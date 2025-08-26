<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\TaxRate;
use App\Models\Finance\TaxTransaction;
use App\Models\Finance\TaxCategory;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaxManagementController extends Controller
{
    /**
     * Get tax management dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            
            // Get tax summary
            $summary = [
                'total_tax_rates' => TaxRate::where('company_id', $companyId)->count(),
                'active_tax_rates' => TaxRate::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->count(),
                'total_tax_transactions' => TaxTransaction::where('company_id', $companyId)->count(),
                'current_month_tax' => TaxTransaction::where('company_id', $companyId)
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum('tax_amount'),
                'pending_tax_liability' => $this->getPendingTaxLiability($companyId),
                'input_tax_credit' => $this->getInputTaxCredit($companyId),
            ];

            // Get tax categories
            $taxCategories = TaxCategory::where('company_id', $companyId)
                ->with(['taxRates'])
                ->get();

            // Get recent tax transactions
            $recentTransactions = TaxTransaction::where('company_id', $companyId)
                ->with(['taxRate', 'transactionable'])
                ->orderBy('transaction_date', 'desc')
                ->limit(10)
                ->get();

            // Get tax liability by period
            $taxLiabilityByPeriod = $this->getTaxLiabilityByPeriod($companyId);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'tax_categories' => $taxCategories,
                    'recent_transactions' => $recentTransactions,
                    'tax_liability_by_period' => $taxLiabilityByPeriod,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading tax dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax rates with filters
     */
    public function getTaxRates(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            $query = TaxRate::where('company_id', $companyId)
                ->with(['category']);

            // Apply filters
            if ($request->has('category_id') && $request->category_id) {
                $query->where('tax_category_id', $request->category_id);
            }

            if ($request->has('is_active') && $request->is_active !== 'all') {
                $query->where('is_active', $request->is_active === 'true');
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            $taxRates = $query->orderBy('name')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $taxRates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading tax rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new tax rate
     */
    public function createTaxRate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'tax_category_id' => 'required|exists:tax_categories,id',
                'rate' => 'required|numeric|min:0|max:100',
                'is_compound' => 'boolean',
                'is_recoverable' => 'boolean',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $taxRate = TaxRate::create([
                'company_id' => Auth::user()->company_id,
                'name' => $request->name,
                'description' => $request->description,
                'tax_category_id' => $request->tax_category_id,
                'rate' => $request->rate,
                'is_compound' => $request->is_compound ?? false,
                'is_recoverable' => $request->is_recoverable ?? true,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'is_active' => $request->is_active ?? true,
                'created_by' => Auth::id(),
            ]);

            $taxRate->load(['category']);

            return response()->json([
                'success' => true,
                'message' => 'Tax rate created successfully',
                'data' => $taxRate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating tax rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tax rate
     */
    public function updateTaxRate(Request $request, $id): JsonResponse
    {
        try {
            $taxRate = TaxRate::where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string|max:500',
                'tax_category_id' => 'sometimes|required|exists:tax_categories,id',
                'rate' => 'sometimes|required|numeric|min:0|max:100',
                'is_compound' => 'boolean',
                'is_recoverable' => 'boolean',
                'effective_from' => 'sometimes|required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $taxRate->update($request->only([
                'name', 'description', 'tax_category_id', 'rate',
                'is_compound', 'is_recoverable', 'effective_from',
                'effective_to', 'is_active'
            ]));

            $taxRate->load(['category']);

            return response()->json([
                'success' => true,
                'message' => 'Tax rate updated successfully',
                'data' => $taxRate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating tax rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax transactions with filters
     */
    public function getTaxTransactions(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::user()->company_id;
            $query = TaxTransaction::where('company_id', $companyId)
                ->with(['taxRate', 'transactionable']);

            // Apply filters
            if ($request->has('tax_rate_id') && $request->tax_rate_id) {
                $query->where('tax_rate_id', $request->tax_rate_id);
            }

            if ($request->has('transaction_type') && $request->transaction_type) {
                $query->where('transaction_type', $request->transaction_type);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->where('transaction_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->where('transaction_date', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'ilike', "%{$search}%")
                      ->orWhere('reference_number', 'ilike', "%{$search}%");
                });
            }

            $transactions = $query->orderBy('transaction_date', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading tax transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create tax transaction
     */
    public function createTaxTransaction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tax_rate_id' => 'required|exists:tax_rates,id',
                'transaction_type' => 'required|in:sales_tax,purchase_tax,adjustment',
                'transactionable_type' => 'required|string',
                'transactionable_id' => 'required|integer',
                'transaction_date' => 'required|date',
                'taxable_amount' => 'required|numeric|min:0',
                'tax_amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'reference_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $taxRate = TaxRate::where('company_id', Auth::user()->company_id)
                ->findOrFail($request->tax_rate_id);

            // Validate tax amount calculation
            $calculatedTax = $request->taxable_amount * ($taxRate->rate / 100);
            if (abs($calculatedTax - $request->tax_amount) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tax amount does not match calculated amount',
                    'errors' => ['tax_amount' => ['Expected: ' . $calculatedTax . ', Provided: ' . $request->tax_amount]]
                ], 422);
            }

            $transaction = TaxTransaction::create([
                'company_id' => Auth::user()->company_id,
                'tax_rate_id' => $request->tax_rate_id,
                'transaction_type' => $request->transaction_type,
                'transactionable_type' => $request->transactionable_type,
                'transactionable_id' => $request->transactionable_id,
                'transaction_date' => $request->transaction_date,
                'taxable_amount' => $request->taxable_amount,
                'tax_amount' => $request->tax_amount,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'created_by' => Auth::id(),
            ]);

            // Create journal entry for tax transaction
            $this->createTaxJournalEntry($transaction, $taxRate);

            $transaction->load(['taxRate', 'transactionable']);

            return response()->json([
                'success' => true,
                'message' => 'Tax transaction created successfully',
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating tax transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate tax for given amount and rate
     */
    public function calculateTax(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'taxable_amount' => 'required|numeric|min:0',
                'tax_rate_id' => 'required|exists:tax_rates,id',
                'is_compound' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $taxRate = TaxRate::where('company_id', Auth::user()->company_id)
                ->findOrFail($request->tax_rate_id);

            $taxableAmount = $request->taxable_amount;
            $taxAmount = $taxableAmount * ($taxRate->rate / 100);

            if ($request->is_compound) {
                $totalAmount = $taxableAmount + $taxAmount;
            } else {
                $totalAmount = $taxableAmount;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'taxable_amount' => $taxableAmount,
                    'tax_rate' => $taxRate->rate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'is_compound' => $request->is_compound ?? false,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating tax: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax report by period
     */
    public function getTaxReport(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'required|in:month,quarter,year',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $companyId = Auth::user()->company_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Get tax transactions for the period
            $transactions = TaxTransaction::where('company_id', $companyId)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->with(['taxRate', 'taxRate.category'])
                ->get();

            // Group by tax category and type
            $report = [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_sales_tax' => $transactions->where('transaction_type', 'sales_tax')->sum('tax_amount'),
                    'total_purchase_tax' => $transactions->where('transaction_type', 'purchase_tax')->sum('tax_amount'),
                    'total_adjustments' => $transactions->where('transaction_type', 'adjustment')->sum('tax_amount'),
                    'net_tax_liability' => 0,
                ],
                'by_category' => [],
                'by_period' => $this->getTaxByPeriod($companyId, $startDate, $endDate, $request->period),
            ];

            // Calculate net tax liability
            $report['summary']['net_tax_liability'] = 
                $report['summary']['total_sales_tax'] - 
                $report['summary']['total_purchase_tax'] + 
                $report['summary']['total_adjustments'];

            // Group by tax category
            $transactions->groupBy('taxRate.tax_category_id')->each(function ($categoryTransactions, $categoryId) use (&$report) {
                $category = $categoryTransactions->first()->taxRate->category;
                $report['by_category'][$category->name] = [
                    'sales_tax' => $categoryTransactions->where('transaction_type', 'sales_tax')->sum('tax_amount'),
                    'purchase_tax' => $categoryTransactions->where('transaction_type', 'purchase_tax')->sum('tax_amount'),
                    'adjustments' => $categoryTransactions->where('transaction_type', 'adjustment')->sum('tax_amount'),
                    'net_amount' => 0,
                ];

                $report['by_category'][$category->name]['net_amount'] = 
                    $report['by_category'][$category->name]['sales_tax'] - 
                    $report['by_category'][$category->name]['purchase_tax'] + 
                    $report['by_category'][$category->name]['adjustments'];
            });

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating tax report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending tax liability
     */
    private function getPendingTaxLiability($companyId): float
    {
        return TaxTransaction::where('company_id', $companyId)
            ->where('transaction_type', 'sales_tax')
            ->where('is_posted', false)
            ->sum('tax_amount');
    }

    /**
     * Get input tax credit
     */
    private function getInputTaxCredit($companyId): float
    {
        return TaxTransaction::where('company_id', $companyId)
            ->where('transaction_type', 'purchase_tax')
            ->where('is_posted', false)
            ->sum('tax_amount');
    }

    /**
     * Get tax liability by period
     */
    private function getTaxLiabilityByPeriod($companyId): array
    {
        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();

            $salesTax = TaxTransaction::where('company_id', $companyId)
                ->where('transaction_type', 'sales_tax')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('tax_amount');

            $purchaseTax = TaxTransaction::where('company_id', $companyId)
                ->where('transaction_type', 'purchase_tax')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('tax_amount');

            $periods[] = [
                'period' => $date->format('M Y'),
                'sales_tax' => $salesTax,
                'purchase_tax' => $purchaseTax,
                'net_liability' => $salesTax - $purchaseTax,
            ];
        }

        return array_reverse($periods);
    }

    /**
     * Get tax by period
     */
    private function getTaxByPeriod($companyId, $startDate, $endDate, $period): array
    {
        $result = [];
        $currentDate = \Carbon\Carbon::parse($startDate);

        while ($currentDate <= \Carbon\Carbon::parse($endDate)) {
            $periodStart = $currentDate->copy();
            $periodEnd = $currentDate->copy();

            switch ($period) {
                case 'month':
                    $periodEnd->endOfMonth();
                    $periodLabel = $currentDate->format('M Y');
                    $currentDate->addMonth();
                    break;
                case 'quarter':
                    $periodEnd->endOfQuarter();
                    $periodLabel = 'Q' . $currentDate->quarter . ' ' . $currentDate->year;
                    $currentDate->addQuarter();
                    break;
                case 'year':
                    $periodEnd->endOfYear();
                    $periodLabel = $currentDate->year;
                    $currentDate->addYear();
                    break;
            }

            if ($periodStart <= \Carbon\Carbon::parse($endDate)) {
                $salesTax = TaxTransaction::where('company_id', $companyId)
                    ->where('transaction_type', 'sales_tax')
                    ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                    ->sum('tax_amount');

                $purchaseTax = TaxTransaction::where('company_id', $companyId)
                    ->where('transaction_type', 'purchase_tax')
                    ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                    ->sum('tax_amount');

                $result[] = [
                    'period' => $periodLabel,
                    'sales_tax' => $salesTax,
                    'purchase_tax' => $purchaseTax,
                    'net_liability' => $salesTax - $purchaseTax,
                ];
            }
        }

        return $result;
    }

    /**
     * Create tax journal entry
     */
    private function createTaxJournalEntry($transaction, $taxRate): void
    {
        // Get tax account based on transaction type
        $taxAccount = null;
        
        if ($transaction->transaction_type === 'sales_tax') {
            $taxAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'liability')
                ->where('name', 'like', '%Sales Tax%')
                ->first();
        } elseif ($transaction->transaction_type === 'purchase_tax') {
            $taxAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'asset')
                ->where('name', 'like', '%Input Tax%')
                ->first();
        }

        if (!$taxAccount) {
            throw new \Exception('Tax account not found for ' . $transaction->transaction_type);
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => Auth::user()->company_id,
            'entry_number' => JournalEntry::generateEntryNumber(Auth::user()->company_id),
            'entry_date' => $transaction->transaction_date,
            'reference_type' => 'Tax Transaction',
            'reference_id' => $transaction->id,
            'description' => $transaction->description ?? 'Tax transaction',
            'total_debit' => $transaction->tax_amount,
            'total_credit' => $transaction->tax_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Create journal entry lines based on transaction type
        if ($transaction->transaction_type === 'sales_tax') {
            // Debit tax account (liability increases)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Sales Tax - ' . $transaction->description,
                'debit_amount' => $transaction->tax_amount,
                'credit_amount' => 0,
                'line_number' => 1,
            ]);

            // Credit cash/AR account
            $cashAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'asset')
                ->where('name', 'like', '%Cash%')
                ->first();

            if (!$cashAccount) {
                $cashAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                    ->where('type', 'asset')
                    ->where('name', 'like', '%Accounts Receivable%')
                    ->first();
            }

            if ($cashAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cashAccount->id,
                    'description' => 'Sales Tax - ' . $transaction->description,
                    'debit_amount' => 0,
                    'credit_amount' => $transaction->tax_amount,
                    'line_number' => 2,
                ]);
            }
        } elseif ($transaction->transaction_type === 'purchase_tax') {
            // Debit cash/AP account
            $cashAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                ->where('type', 'asset')
                ->where('name', 'like', '%Cash%')
                ->first();

            if (!$cashAccount) {
                $cashAccount = ChartOfAccount::where('company_id', Auth::user()->company_id)
                    ->where('type', 'liability')
                    ->where('name', 'like', '%Accounts Payable%')
                    ->first();
            }

            if ($cashAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cashAccount->id,
                    'description' => 'Input Tax - ' . $transaction->description,
                    'debit_amount' => $transaction->tax_amount,
                    'credit_amount' => 0,
                    'line_number' => 1,
                ]);
            }

            // Credit tax account (asset increases)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Input Tax - ' . $transaction->description,
                'debit_amount' => 0,
                'credit_amount' => $transaction->tax_amount,
                'line_number' => 2,
            ]);
        }

        // Update account balances
        if ($transaction->transaction_type === 'sales_tax') {
            $taxAccount->increment('balance', $transaction->tax_amount);
            if (isset($cashAccount)) {
                $cashAccount->decrement('balance', $transaction->tax_amount);
            }
        } elseif ($transaction->transaction_type === 'purchase_tax') {
            $taxAccount->decrement('balance', $transaction->tax_amount);
            if (isset($cashAccount)) {
                $cashAccount->increment('balance', $transaction->tax_amount);
            }
        }

        // Link transaction to journal entry
        $transaction->update([
            'journal_entry_id' => $journalEntry->id,
            'is_posted' => true,
        ]);
    }
}
