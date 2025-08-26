<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\Finance\CustomerBalance;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountsReceivableController extends Controller
{
    /**
     * Get AR dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            
            // Get AR summary
            $summary = [
                'total_receivables' => Invoice::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->sum('total_amount'),
                'overdue_amount' => Invoice::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->where('due_date', '<', now())
                    ->sum('total_amount'),
                'total_invoices' => Invoice::where('company_id', $companyId)->count(),
                'open_invoices' => Invoice::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->count(),
                'overdue_invoices' => Invoice::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->where('due_date', '<', now())
                    ->count(),
            ];

            // Get aging analysis
            $aging = $this->getAgingAnalysis($companyId);

            // Get recent activities
            $recentInvoices = Invoice::where('company_id', $companyId)
                ->with(['customer', 'lines'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $recentPayments = Payment::where('company_id', $companyId)
                ->with(['customer', 'invoice'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'aging' => $aging,
                    'recent_invoices' => $recentInvoices,
                    'recent_payments' => $recentPayments,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading AR dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoices with filters
     */
    public function getInvoices(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            $query = Invoice::where('company_id', $companyId)
                ->with(['customer', 'lines']);

            // Apply filters
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('customer_id') && $request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->where('invoice_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->where('invoice_date', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            $invoices = $query->orderBy('invoice_date', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new invoice
     */
    public function createInvoice(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after:invoice_date',
                'description' => 'nullable|string',
                'lines' => 'required|array|min:1',
                'lines.*.description' => 'required|string',
                'lines.*.quantity' => 'required|numeric|min:0.01',
                'lines.*.unit_price' => 'required|numeric|min:0.01',
                'lines.*.tax_rate' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalAmount = 0;

            foreach ($request->lines as $line) {
                $lineTotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineTotal * ($line['tax_rate'] ?? 0) / 100;
                
                $subtotal += $lineTotal;
                $totalTax += $lineTax;
            }

            $totalAmount = $subtotal + $totalTax;

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => Auth::user()->company_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Create invoice lines
            foreach ($request->lines as $line) {
                $lineTotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineTotal * ($line['tax_rate'] ?? 0) / 100;

                $invoice->lines()->create([
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_rate' => $line['tax_rate'] ?? 0,
                    'line_total' => $lineTotal,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal + $lineTax,
                ]);
            }

            // Update customer balance
            $this->updateCustomerBalance($request->customer_id, $totalAmount, 'invoice');

            DB::commit();

            $invoice->load(['customer', 'lines']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post invoice (create journal entry)
     */
    public function postInvoice(Request $request, $id): JsonResponse
    {
        try {
            $invoice = Invoice::where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft invoices can be posted'
                ], 422);
            }

            DB::beginTransaction();

            // Get AR account
            $arAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'asset')
                ->where('name', 'like', '%Accounts Receivable%')
                ->first();

            if (!$arAccount) {
                throw new \Exception('Accounts Receivable account not found');
            }

            // Get Revenue account
            $revenueAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'revenue')
                ->where('name', 'like', '%Sales Revenue%')
                ->first();

            if (!$revenueAccount) {
                throw new \Exception('Sales Revenue account not found');
            }

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => Auth::user()->company_id,
                'entry_number' => JournalEntry::generateEntryNumber(Auth::user()->company_id),
                'entry_date' => $invoice->invoice_date,
                'reference_type' => 'Invoice',
                'reference_id' => $invoice->id,
                'description' => 'Invoice #' . $invoice->invoice_number . ' - ' . $invoice->description,
                'total_debit' => $invoice->total_amount,
                'total_credit' => $invoice->total_amount,
                'status' => 'posted',
                'posted_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // Create journal entry lines
            // Debit AR account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $arAccount->id,
                'description' => 'AR - Invoice #' . $invoice->invoice_number,
                'debit_amount' => $invoice->total_amount,
                'credit_amount' => 0,
                'line_number' => 1,
            ]);

            // Credit Revenue account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $revenueAccount->id,
                'description' => 'Revenue - Invoice #' . $invoice->invoice_number,
                'debit_amount' => 0,
                'credit_amount' => $invoice->subtotal,
                'line_number' => 2,
            ]);

            // Credit Tax account if applicable
            if ($invoice->total_tax > 0) {
                $taxAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                    ->where('type', 'liability')
                    ->where('name', 'like', '%Sales Tax%')
                    ->first();

                if ($taxAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $taxAccount->id,
                        'description' => 'Sales Tax - Invoice #' . $invoice->invoice_number,
                        'debit_amount' => 0,
                        'credit_amount' => $invoice->total_tax,
                        'line_number' => 3,
                    ]);
                }
            }

            // Update invoice status
            $invoice->update([
                'status' => 'posted',
                'posted_at' => now(),
                'journal_entry_id' => $journalEntry->id,
            ]);

            // Update account balances
            $arAccount->increment('balance', $invoice->total_amount);
            $revenueAccount->decrement('balance', $invoice->subtotal);
            
            if ($invoice->total_tax > 0 && isset($taxAccount)) {
                $taxAccount->decrement('balance', $invoice->total_tax);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice posted successfully',
                'data' => $invoice->load(['customer', 'lines'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error posting invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record payment
     */
    public function recordPayment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|exists:invoices,id',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string',
                'reference_number' => 'nullable|string',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $invoice = Invoice::where('company_id', Auth::user()->company_id)
                ->findOrFail($request->invoice_id);

            if ($invoice->status !== 'posted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only posted invoices can receive payments'
                ], 422);
            }

            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'company_id' => Auth::user()->company_id,
                'invoice_id' => $request->invoice_id,
                'customer_id' => $invoice->customer_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'description' => $request->description,
                'created_by' => Auth::id(),
            ]);

            // Update invoice paid amount
            $invoice->increment('paid_amount', $request->amount);
            
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
            }

            // Create journal entry for payment
            $this->createPaymentJournalEntry($payment, $invoice);

            // Update customer balance
            $this->updateCustomerBalance($invoice->customer_id, -$request->amount, 'payment');

            DB::commit();

            $payment->load(['customer', 'invoice']);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get aging analysis
     */
    private function getAgingAnalysis($companyId): array
    {
        $aging = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
        ];

        $invoices = Invoice::where('company_id', $companyId)
            ->where('status', 'open')
            ->get();

        foreach ($invoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date, false);
            
            if ($daysOverdue <= 0) {
                $aging['current'] += $invoice->total_amount - $invoice->paid_amount;
            } elseif ($daysOverdue <= 30) {
                $aging['30_days'] += $invoice->total_amount - $invoice->paid_amount;
            } elseif ($daysOverdue <= 60) {
                $aging['60_days'] += $invoice->total_amount - $invoice->paid_amount;
            } elseif ($daysOverdue <= 90) {
                $aging['90_days'] += $invoice->total_amount - $invoice->paid_amount;
            } else {
                $aging['over_90_days'] += $invoice->total_amount - $invoice->paid_amount;
            }
        }

        return $aging;
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $lastInvoice = Invoice::where('company_id', Auth::user()->company_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, 3);
            return 'INV' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return 'INV000001';
    }

    /**
     * Update customer balance
     */
    private function updateCustomerBalance($customerId, $amount, $type): void
    {
        $balance = CustomerBalance::where('company_id', Auth::user()->company_id)
            ->where('customer_id', $customerId)
            ->first();

        if ($balance) {
            $balance->increment('balance', $amount);
        } else {
            CustomerBalance::create([
                'company_id' => Auth::user()->company_id,
                'customer_id' => $customerId,
                'balance' => $amount,
            ]);
        }
    }

    /**
     * Create payment journal entry
     */
    private function createPaymentJournalEntry($payment, $invoice): void
    {
        // Get Cash/Bank account
        $cashAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
            ->where('type', 'asset')
            ->where('name', 'like', '%Cash%')
            ->first();

        if (!$cashAccount) {
            $cashAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'asset')
                ->where('name', 'like', '%Bank%')
                ->first();
        }

        if (!$cashAccount) {
            throw new \Exception('Cash or Bank account not found');
        }

        // Get AR account
        $arAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
            ->where('type', 'asset')
            ->where('name', 'like', '%Accounts Receivable%')
            ->first();

        if (!$arAccount) {
            throw new \Exception('Accounts Receivable account not found');
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => Auth::user()->company_id,
            'entry_number' => JournalEntry::generateEntryNumber(Auth::user()->company_id),
            'entry_date' => $payment->payment_date,
            'reference_type' => 'Payment',
            'reference_id' => $payment->id,
            'description' => 'Payment for Invoice #' . $invoice->invoice_number,
            'total_debit' => $payment->amount,
            'total_credit' => $payment->amount,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Create journal entry lines
        // Debit Cash/Bank account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cashAccount->id,
            'description' => 'Cash - Payment for Invoice #' . $invoice->invoice_number,
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'line_number' => 1,
        ]);

        // Credit AR account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'AR - Payment for Invoice #' . $invoice->invoice_number,
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'line_number' => 2,
        ]);

        // Update account balances
        $cashAccount->increment('balance', $payment->amount);
        $arAccount->decrement('balance', $payment->amount);

        // Link payment to journal entry
        $payment->update(['journal_entry_id' => $journalEntry->id]);
    }
}
