<?php

namespace App\Services;

use App\Models\Finance\Expense;
use App\Models\Finance\AssetPurchase;
use App\Models\Finance\CashTransaction;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\ApprovalRequest;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinanceService
{
    /**
     * Get comprehensive financial dashboard data
     */
    public function getFinancialDashboard(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'current_month' => [
                'expenses' => $this->getMonthlyExpenses($currentMonth),
                'assets' => $this->getMonthlyAssetPurchases($currentMonth),
                'cash_flow' => $this->getMonthlyCashFlow($currentMonth),
                'budget_variance' => $this->getMonthlyBudgetVariance($currentMonth),
            ],
            'previous_month' => [
                'expenses' => $this->getMonthlyExpenses($previousMonth),
                'assets' => $this->getMonthlyAssetPurchases($previousMonth),
                'cash_flow' => $this->getMonthlyCashFlow($previousMonth),
                'budget_variance' => $this->getMonthlyBudgetVariance($previousMonth),
            ],
            'pending_approvals' => $this->getPendingApprovals(),
            'budget_alerts' => $this->getBudgetAlerts(),
            'cash_position' => $this->getCashPosition(),
        ];
    }

    /**
     * Get monthly expenses summary
     */
    private function getMonthlyExpenses(Carbon $month): array
    {
        $expenses = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$month, $month->copy()->endOfMonth()])
            ->get();

        return [
            'total' => $expenses->sum('total_amount'),
            'count' => $expenses->count(),
            'by_category' => $expenses->groupBy('category.name')
                ->map(fn($group) => $group->sum('total_amount')),
            'by_department' => $expenses->groupBy('department.name')
                ->map(fn($group) => $group->sum('total_amount')),
        ];
    }

    /**
     * Get monthly asset purchases summary
     */
    private function getMonthlyAssetPurchases(Carbon $month): array
    {
        $assets = AssetPurchase::where('status', 'approved')
            ->whereBetween('purchase_date', [$month, $month->copy()->endOfMonth()])
            ->get();

        return [
            'total' => $assets->sum('total_cost'),
            'count' => $assets->count(),
            'by_category' => $assets->groupBy('category.name')
                ->map(fn($group) => $group->sum('total_cost')),
            'by_department' => $assets->groupBy('department.name')
                ->map(fn($group) => $group->sum('total_cost')),
        ];
    }

    /**
     * Get monthly cash flow summary
     */
    private function getMonthlyCashFlow(Carbon $month): array
    {
        $transactions = CashTransaction::where('status', 'completed')
            ->whereBetween('date', [$month, $month->copy()->endOfMonth()])
            ->get();

        $inflows = $transactions->whereIn('type', ['deposit', 'transfer'])->sum('amount');
        $outflows = $transactions->whereIn('type', ['withdrawal', 'expense'])->sum('amount');

        return [
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net_flow' => $inflows - $outflows,
            'by_type' => $transactions->groupBy('type')
                ->map(fn($group) => $group->sum('amount')),
        ];
    }

    /**
     * Get monthly budget variance analysis
     */
    private function getMonthlyBudgetVariance(Carbon $month): array
    {
        $budgets = Budget::where('period_start', '<=', $month->endOfMonth())
            ->where('period_end', '>=', $month)
            ->with('category')
            ->get();

        $actualExpenses = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$month, $month->copy()->endOfMonth()])
            ->get();

        $variances = [];
        foreach ($budgets as $budget) {
            $actual = $actualExpenses->where('category_id', $budget->category_id)->sum('total_amount');
            $variance = $budget->amount - $actual;
            $variancePercentage = $budget->amount > 0 ? ($variance / $budget->amount) * 100 : 0;

            $variances[] = [
                'category' => $budget->category->name,
                'budgeted' => $budget->amount,
                'actual' => $actual,
                'variance' => $variance,
                'variance_percentage' => $variancePercentage,
                'status' => $this->getVarianceStatus($variancePercentage),
            ];
        }

        return $variances;
    }

    /**
     * Get variance status based on percentage
     */
    private function getVarianceStatus(float $percentage): string
    {
        if ($percentage >= 10) return 'over_budget';
        if ($percentage <= -10) return 'under_budget';
        return 'within_budget';
    }

    /**
     * Get pending approval requests
     */
    public function getPendingApprovals(): array
    {
        $approvals = ApprovalRequest::where('status', 'pending')
            ->with(['approvable', 'requestor', 'workflow'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $approvals->map(function ($approval) {
            return [
                'id' => $approval->id,
                'type' => class_basename($approval->approvable_type),
                'amount' => $approval->amount,
                'description' => $approval->description,
                'requestor' => $approval->requestor->name,
                'priority' => $approval->priority,
                'due_date' => $approval->due_date,
                'created_at' => $approval->created_at,
            ];
        })->toArray();
    }

    /**
     * Get budget alerts for overruns
     */
    public function getBudgetAlerts(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $budgets = Budget::where('period_start', '<=', $currentMonth->endOfMonth())
            ->where('period_end', '>=', $currentMonth)
            ->with('category')
            ->get();

        $alerts = [];
        foreach ($budgets as $budget) {
            $actual = Expense::where('status', 'approved')
                ->where('category_id', $budget->category_id)
                ->whereBetween('expense_date', [$currentMonth, $currentMonth->copy()->endOfMonth()])
                ->sum('total_amount');

            $percentage = $budget->amount > 0 ? ($actual / $budget->amount) * 100 : 0;

            if ($percentage >= 80) {
                $alerts[] = [
                    'category' => $budget->category->name,
                    'budgeted' => $budget->amount,
                    'actual' => $actual,
                    'percentage_used' => $percentage,
                    'remaining' => $budget->amount - $actual,
                    'alert_level' => $percentage >= 100 ? 'over_budget' : 'near_limit',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Get current cash position
     */
    public function getCashPosition(): array
    {
        $cashAccount = ChartOfAccount::where('account_code', '1110')->first();
        $bankAccount = ChartOfAccount::where('account_code', '1120')->first();

        $cashBalance = 0;
        $bankBalance = 0;

        if ($cashAccount) {
            $cashBalance = $this->getAccountBalance($cashAccount->id);
        }

        if ($bankAccount) {
            $bankBalance = $this->getAccountBalance($bankAccount->id);
        }

        return [
            'cash_on_hand' => $cashBalance,
            'bank_balance' => $bankBalance,
            'total_cash' => $cashBalance + $bankBalance,
            'last_updated' => Carbon::now(),
        ];
    }

    /**
     * Get account balance from journal entries
     */
    private function getAccountBalance(int $accountId): float
    {
        $balance = JournalEntryLine::join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->selectRaw('SUM(debit_amount) - SUM(credit_amount) as balance')
            ->first();

        return $balance ? (float) $balance->balance : 0;
    }

    /**
     * Create budget monitoring alert
     */
    public function createBudgetAlert(int $categoryId, float $threshold, string $message): void
    {
        // This would integrate with notification system
        Log::info('Budget alert created', [
            'category_id' => $categoryId,
            'threshold' => $threshold,
            'message' => $message,
        ]);
    }

    /**
     * Get financial metrics for reporting
     */
    public function getFinancialMetrics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $endDate = Carbon::now();

        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'expenses' => $this->getMonthlyExpenses($startDate),
            'assets' => $this->getMonthlyAssetPurchases($startDate),
            'cash_flow' => $this->getMonthlyCashFlow($startDate),
            'budget_variance' => $this->getMonthlyBudgetVariance($startDate),
        ];
    }
}
