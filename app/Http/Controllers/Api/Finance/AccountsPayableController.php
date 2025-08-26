<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Bill;
use App\Models\Finance\BillPayment;
use App\Models\Finance\SupplierBalance;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountsPayableController extends Controller
{
    /**
     * Get AP dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            
            // Get AP summary
            $summary = [
                'total_payables' => Bill::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->sum('total_amount'),
                'overdue_amount' => Bill::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->where('due_date', '<', now())
                    ->sum('total_amount'),
                'total_bills' => Bill::where('company_id', $companyId)->count(),
                'open_bills' => Bill::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->count(),
                'overdue_bills' => Bill::where('company_id', $companyId)
                    ->where('status', 'open')
                    ->where('due_date', '<', now())
                    ->count(),
            ];

            // Get aging analysis
            $aging = $this->getAgingAnalysis($companyId);

            // Get recent activities
            $recentBills = Bill::where('company_id', $companyId)
                ->with(['supplier', 'lines'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $recentPayments = BillPayment::where('company_id', $companyId)
                ->with(['supplier', 'bill'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'aging' => $aging,
                    'recent_bills' => $recentBills,
                    'recent_payments' => $recentPayments,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading AP dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bills with filters
     */
    public function getBills(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            $query = Bill::where('company_id', $companyId)
                ->with(['supplier', 'lines']);

            // Apply filters
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->where('bill_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->where('bill_date', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('bill_number', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            $bills = $query->orderBy('bill_date', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $bills
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading bills: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new bill
     */
    public function createBill(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'bill_date' => 'required|date',
                'due_date' => 'required|date|after:bill_date',
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

            // Create bill
            $bill = Bill::create([
                'company_id' => Auth::user()->company_id,
                'bill_number' => $this->generateBillNumber(),
                'supplier_id' => $request->supplier_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Create bill lines
            foreach ($request->lines as $line) {
                $lineTotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineTotal * ($line['tax_rate'] ?? 0) / 100;

                $bill->lines()->create([
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_rate' => $line['tax_rate'] ?? 0,
                    'line_total' => $lineTotal,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal + $lineTax,
                ]);
            }

            // Update supplier balance
            $this->updateSupplierBalance($request->supplier_id, $totalAmount, 'bill');

            DB::commit();

            $bill->load(['supplier', 'lines']);

            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully',
                'data' => $bill
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post bill (create journal entry)
     */
    public function postBill(Request $request, $id): JsonResponse
    {
        try {
            $bill = Bill::where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            if ($bill->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft bills can be posted'
                ], 422);
            }

            DB::beginTransaction();

            // Get AP account
            $apAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'liability')
                ->where('name', 'like', '%Accounts Payable%')
                ->first();

            if (!$apAccount) {
                throw new \Exception('Accounts Payable account not found');
            }

            // Get Expense account
            $expenseAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'expense')
                ->where('name', 'like', '%Operating Expenses%')
                ->first();

            if (!$expenseAccount) {
                throw new \Exception('Operating Expenses account not found');
            }

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => Auth::user()->company_id,
                'entry_number' => JournalEntry::generateEntryNumber(Auth::user()->company_id),
                'entry_date' => $bill->bill_date,
                'reference_type' => 'Bill',
                'reference_id' => $bill->id,
                'description' => 'Bill #' . $bill->bill_number . ' - ' . $bill->description,
                'total_debit' => $bill->total_amount,
                'total_credit' => $bill->total_amount,
                'status' => 'posted',
                'posted_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // Create journal entry lines
            // Debit Expense account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $expenseAccount->id,
                'description' => 'Expense - Bill #' . $bill->bill_number,
                'debit_amount' => $bill->subtotal,
                'credit_amount' => 0,
                'line_number' => 1,
            ]);

            // Credit AP account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $apAccount->id,
                'description' => 'AP - Bill #' . $bill->bill_number,
                'debit_amount' => 0,
                'credit_amount' => $bill->total_amount,
                'line_number' => 2,
            ]);

            // Debit Tax account if applicable
            if ($bill->total_tax > 0) {
                $taxAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                    ->where('type', 'asset')
                    ->where('name', 'like', '%Input Tax%')
                    ->first();

                if ($taxAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $taxAccount->id,
                        'description' => 'Input Tax - Bill #' . $bill->bill_number,
                        'debit_amount' => $bill->total_tax,
                        'credit_amount' => 0,
                        'line_number' => 3,
                    ]);
                }
            }

            // Update bill status
            $bill->update([
                'status' => 'posted',
                'posted_at' => now(),
                'journal_entry_id' => $journalEntry->id,
            ]);

            // Update account balances
            $expenseAccount->increment('balance', $bill->subtotal);
            $apAccount->decrement('balance', $bill->total_amount);
            
            if ($bill->total_tax > 0 && isset($taxAccount)) {
                $taxAccount->increment('balance', $bill->total_tax);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bill posted successfully',
                'data' => $bill->load(['supplier', 'lines'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error posting bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record bill payment
     */
    public function recordPayment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'bill_id' => 'required|exists:bills,id',
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

            $bill = Bill::where('company_id', Auth::user()->company_id)
                ->findOrFail($request->bill_id);

            if ($bill->status !== 'posted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only posted bills can receive payments'
                ], 422);
            }

            DB::beginTransaction();

            // Create payment record
            $payment = BillPayment::create([
                'company_id' => Auth::user()->company_id,
                'bill_id' => $request->bill_id,
                'supplier_id' => $bill->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'description' => $request->description,
                'created_by' => Auth::id(),
            ]);

            // Update bill paid amount
            $bill->increment('paid_amount', $request->amount);
            
            if ($bill->paid_amount >= $bill->total_amount) {
                $bill->update(['status' => 'paid']);
            } else {
                $bill->update(['status' => 'partial']);
            }

            // Create journal entry for payment
            $this->createPaymentJournalEntry($payment, $bill);

            // Update supplier balance
            $this->updateSupplierBalance($bill->supplier_id, -$request->amount, 'payment');

            DB::commit();

            $payment->load(['supplier', 'bill']);

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

        $bills = Bill::where('company_id', $companyId)
            ->where('status', 'open')
            ->get();

        foreach ($bills as $bill) {
            $daysOverdue = now()->diffInDays($bill->due_date, false);
            
            if ($daysOverdue <= 0) {
                $aging['current'] += $bill->total_amount - $bill->paid_amount;
            } elseif ($daysOverdue <= 30) {
                $aging['30_days'] += $bill->total_amount - $bill->paid_amount;
            } elseif ($daysOverdue <= 60) {
                $aging['60_days'] += $bill->total_amount - $bill->paid_amount;
            } elseif ($daysOverdue <= 90) {
                $aging['90_days'] += $bill->total_amount - $bill->paid_amount;
            } else {
                $aging['over_90_days'] += $bill->total_amount - $bill->paid_amount;
            }
        }

        return $aging;
    }

    /**
     * Generate bill number
     */
    private function generateBillNumber(): string
    {
        $lastBill = Bill::where('company_id', Auth::user()->company_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastBill) {
            $lastNumber = (int) substr($lastBill->bill_number, 3);
            return 'BIL' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return 'BIL000001';
    }

    /**
     * Update supplier balance
     */
    private function updateSupplierBalance($supplierId, $amount, $type): void
    {
        $balance = SupplierBalance::where('company_id', Auth::user()->company_id)
            ->where('supplier_id', $supplierId)
            ->first();

        if ($balance) {
            $balance->increment('balance', $amount);
        } else {
            SupplierBalance::create([
                'company_id' => Auth::user()->company_id,
                'supplier_id' => $supplierId,
                'balance' => $amount,
            ]);
        }
    }

    /**
     * Create payment journal entry
     */
    private function createPaymentJournalEntry($payment, $bill): void
    {
        // Get AP account
        $apAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
            ->where('type', 'liability')
            ->where('name', 'like', '%Accounts Payable%')
            ->first();

        if (!$apAccount) {
            throw new \Exception('Accounts Payable account not found');
        }

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

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => Auth::user()->company_id,
            'entry_number' => JournalEntry::generateEntryNumber(Auth::user()->company_id),
            'entry_date' => $payment->payment_date,
            'reference_type' => 'Bill Payment',
            'reference_id' => $payment->id,
            'description' => 'Payment for Bill #' . $bill->bill_number,
            'total_debit' => $payment->amount,
            'total_credit' => $payment->amount,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Create journal entry lines
        // Debit AP account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $apAccount->id,
            'description' => 'AP - Payment for Bill #' . $bill->bill_number,
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'line_number' => 1,
        ]);

        // Credit Cash/Bank account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cashAccount->id,
            'description' => 'Cash - Payment for Bill #' . $bill->bill_number,
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'line_number' => 2,
        ]);

        // Update account balances
        $apAccount->increment('balance', $payment->amount);
        $cashAccount->decrement('balance', $payment->amount);

        // Link payment to journal entry
        $payment->update(['journal_entry_id' => $journalEntry->id]);
    }
}
