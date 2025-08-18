<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TrialBalanceController extends Controller
{
    /**
     * Get trial balance data
     */
    public function index(Request $request): JsonResponse
    {
        $query = ChartOfAccount::where('company_id', Auth::user()->company_id);

        // Filter by account type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $accounts = $query->get();

        $trialBalanceData = [];
        $summary = [
            'total_debit' => 0,
            'total_credit' => 0
        ];

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account->id, $request->date_from, $request->date_to);
            
            $debitBalance = 0;
            $creditBalance = 0;

            // Determine debit or credit balance based on account type
            if ($balance > 0) {
                if (in_array($account->type, ['asset', 'expense'])) {
                    $debitBalance = $balance;
                } else {
                    $creditBalance = $balance;
                }
            } else {
                if (in_array($account->type, ['asset', 'expense'])) {
                    $creditBalance = abs($balance);
                } else {
                    $debitBalance = abs($balance);
                }
            }

            $trialBalanceData[] = [
                'id' => $account->id,
                'account_code' => $account->account_code,
                'name' => $account->name,
                'type' => $account->type,
                'debit_balance' => $debitBalance,
                'credit_balance' => $creditBalance
            ];

            $summary['total_debit'] += $debitBalance;
            $summary['total_credit'] += $creditBalance;
        }

        return response()->json([
            'accounts' => $trialBalanceData,
            'summary' => $summary
        ]);
    }

    /**
     * Export trial balance
     */
    public function export(Request $request): JsonResponse
    {
        // This would generate and return a file download
        // For now, return success message
        return response()->json([
            'message' => 'Export functionality will be implemented'
        ]);
    }

    /**
     * Get account balance for a specific period
     */
    private function getAccountBalance(int $accountId, ?string $dateFrom, ?string $dateTo): float
    {
        $query = JournalEntryLine::join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.company_id', Auth::user()->company_id)
            ->where('journal_entries.status', 'posted');

        if ($dateFrom) {
            $query->where('journal_entries.entry_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('journal_entries.entry_date', '<=', $dateTo);
        }

        $balance = $query->selectRaw('SUM(debit_amount) - SUM(credit_amount) as balance')
            ->first();

        return $balance ? (float) $balance->balance : 0;
    }
}
