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
use Illuminate\Support\Facades\Log;
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
                    ->whereIn('status', ['sent', 'open', 'overdue'])
                    ->sum('total_amount'),
                'overdue_amount' => Invoice::where('company_id', $companyId)
                    ->whereIn('status', ['sent', 'open', 'partial', 'overdue'])
                    ->where('due_date', '<', now())
                    ->sum('total_amount'),
                'total_invoices' => Invoice::where('company_id', $companyId)->count(),
                'open_invoices' => Invoice::where('company_id', $companyId)
                    ->whereIn('status', ['sent', 'open', 'overdue'])
                    ->count(),
                'overdue_invoices' => Invoice::where('company_id', $companyId)
                    ->whereIn('status', ['sent', 'open', 'overdue'])
                    ->where('due_date', '<', now())
                    ->count(),
                'paid_amount' => Payment::where('company_id', $companyId)
                    ->where('status', 'completed')
                    ->sum('amount'),
            ];

            // Get aging analysis
            $aging = $this->getAgingAnalysis($companyId);

            // Get recent activities
            $recentInvoices = Invoice::where('company_id', $companyId)
                ->with(['customer', 'items', 'payments'])
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
     * Display a listing of invoices (index method for API resource)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->getInvoices($request);
    }

    /**
     * Get invoices with filters
     */
    public function getInvoices(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            $query = Invoice::where('company_id', $companyId)
                ->with(['customer', 'items', 'payments']);

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
                      ->orWhere('description', 'ilike', "%{$search}%")
                      ->orWhereHas('customer', function ($customerQuery) use ($search) {
                          $customerQuery->where('name', 'ilike', "%{$search}%");
                      });
                });
            }

            $invoices = $query->orderBy('invoice_date', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($request->get('per_page', 15));

            // Get summary for invoices page
            $summary = [
                'total_invoices' => Invoice::where('company_id', $companyId)->count(),
                'total_amount' => Invoice::where('company_id', $companyId)->sum('total_amount'),
                'overdue_amount' => Invoice::where('company_id', $companyId)
                    ->whereIn('status', ['sent', 'open', 'overdue'])
                    ->where('due_date', '<', now())
                    ->sum('total_amount'),
                'paid_amount' => Payment::where('company_id', $companyId)
                    ->where('status', 'completed')
                    ->sum('amount'),
                'outstanding_amount' => Invoice::where('company_id', $companyId)
                    ->whereNotIn('status', ['paid', 'cancelled'])
                    ->sum(DB::raw('total_amount - paid_amount')),
            ];

            return response()->json([
                'success' => true,
                'data' => $invoices->items(),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ],
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show($id): JsonResponse
    {
        try {
            $invoice = Invoice::where('company_id', Auth::user()->company_id)
                ->with(['customer', 'items.product', 'payments.customer'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Store a newly created invoice (store method for API resource)
     */
    public function store(Request $request): JsonResponse
    {
        return $this->createInvoice($request);
    }

    /**
     * Create new invoice
     */
    public function createInvoice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;

                $subtotal += $lineTotal;
                $discountAmount += $discount;
                $taxAmount += $tax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $invoice = Invoice::create([
                'company_id' => Auth::user()->company_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'description' => $request->description,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount, // Balance = total amount for new invoices
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Create invoice items
            foreach ($request->items as $index => $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;
                $total = $lineTotal - $discount + $tax;

                $invoice->items()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $tax,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'line_number' => $index + 1,
                ]);
            }

            DB::commit();

            $invoice->load(['customer', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified invoice (update method for API resource)
     */
    public function update(Request $request, $id): JsonResponse
    {
        return $this->updateInvoice($request, $id);
    }

    /**
     * Update invoice
     */
    public function updateInvoice(Request $request, $id): JsonResponse
    {
        try {
            $invoice = Invoice::where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            // Only allow updates for draft invoices
            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update non-draft invoice'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'reference_type' => 'nullable|string|max:50',
                'reference_id' => 'nullable|integer',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|exists:products,id',
                'items.*.item_name' => 'required|string|max:255',
                'items.*.description' => 'nullable|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
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
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;

                $subtotal += $lineTotal;
                $discountAmount += $discount;
                $taxAmount += $tax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Create new invoice items
            foreach ($request->items as $index => $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;
                $total = $lineTotal - $discount + $tax;

                $invoice->items()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $tax,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'line_number' => $index + 1,
                ]);
            }

            DB::commit();

            $invoice->load(['customer', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data' => $invoice
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice (destroy method for API resource)
     */
    public function destroy($id): JsonResponse
    {
        return $this->deleteInvoice($id);
    }

    /**
     * Delete invoice
     */
    public function deleteInvoice($id): JsonResponse
    {
        try {
            $invoice = Invoice::where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            // Only allow deletion for draft invoices
            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete non-draft invoice'
                ], 422);
            }

            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post invoice (create journal entry)
     */
    public function postInvoice($id): JsonResponse
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
            if ($invoice->tax_amount > 0) {
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
                        'credit_amount' => $invoice->tax_amount,
                        'line_number' => 3,
                    ]);
                }
            }

            // Update invoice status
            $invoice->update([
                'status' => 'sent',
                'posted_at' => now(),
                'journal_entry_id' => $journalEntry->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice posted successfully',
                'data' => $invoice->load(['customer', 'items'])
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

            if (!in_array($invoice->status, ['sent', 'open'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only sent/open invoices can receive payments'
                ], 422);
            }

            DB::beginTransaction();

            // Generate payment number
            $paymentNumber = $this->generatePaymentNumber();
            Log::info('Generated payment number: ' . $paymentNumber);

            // Create payment record
            $payment = Payment::create([
                'company_id' => Auth::user()->company_id,
                'payment_number' => $paymentNumber,
                'invoice_id' => $request->invoice_id,
                'customer_id' => $invoice->customer_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'description' => $request->description,
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]);

            Log::info('Payment created with ID: ' . $payment->id . ', Number: ' . $payment->payment_number);

            // Update invoice paid amount
            $invoice->increment('paid_amount', $request->amount);
            
            // Update balance amount
            $invoice->update([
                'balance_amount' => $invoice->total_amount - $invoice->paid_amount
            ]);
            
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
            } else {
                // Keep status as 'sent' since 'partial' is not allowed
                // The balance_amount will show the remaining amount
            }

            // Create journal entry for payment
            $journalEntry = $this->createPaymentJournalEntry($payment, $invoice);
            
            // Update payment with journal entry ID
            $payment->update(['journal_entry_id' => $journalEntry->id]);

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
            ->whereIn('status', ['sent', 'open', 'overdue'])
            ->with(['payments'])
            ->get();

        foreach ($invoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date, false);
            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;
            
            if ($daysOverdue <= 0) {
                $aging['current'] += $remainingAmount;
            } elseif ($daysOverdue <= 30) {
                $aging['30_days'] += $remainingAmount;
            } elseif ($daysOverdue <= 60) {
                $aging['60_days'] += $remainingAmount;
            } elseif ($daysOverdue <= 90) {
                $aging['90_days'] += $remainingAmount;
            } else {
                $aging['over_90_days'] += $remainingAmount;
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
            $lastNumber = $lastInvoice->invoice_number;
            
            // Handle different formats that might exist
            if (preg_match('/INV(\d+)-(\d+)/', $lastNumber, $matches)) {
                // Format: INV000-19
                $prefix = (int) $matches[1];
                $sequence = (int) $matches[2];
                $newSequence = $sequence + 1;
                return sprintf('INV%03d-%02d', $prefix, $newSequence);
            } elseif (preg_match('/INV(\d+)/', $lastNumber, $matches)) {
                // Format: INV000001
                $sequence = (int) $matches[1];
                $newSequence = $sequence + 1;
                return sprintf('INV%06d', $newSequence);
            } else {
                // Fallback: start with INV000-01
                return 'INV000-01';
            }
        }

        // Start with INV000-01 for new companies
        return 'INV000-01';
    }

    /**
     * Generate unique payment number
     */
    private function generatePaymentNumber(): string
    {
        $lastPayment = Payment::where('company_id', Auth::user()->company_id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastPayment) {
            return 'PAY000001';
        }

        // Extract number from last payment number
        $lastNumber = $lastPayment->payment_number;
        
        if (preg_match('/PAY(\d+)/', $lastNumber, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            return 'PAY' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }

        // Fallback if pattern doesn't match
        return 'PAY' . str_pad($lastPayment->id + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create payment journal entry
     */
    private function createPaymentJournalEntry($payment, $invoice): JournalEntry
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

        // Return journal entry for linking
        return $journalEntry;
    }
}
