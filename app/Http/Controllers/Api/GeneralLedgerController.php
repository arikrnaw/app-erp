<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneralLedgerController extends Controller
{
    /**
     * Get general ledger data
     */
    public function index(Request $request): JsonResponse
    {
        $query = ChartOfAccount::where('company_id', Auth::user()->company_id);

        // Filter by account type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by specific account
        if ($request->has('account_id') && $request->account_id) {
            $query->where('id', $request->account_id);
        }

        $accounts = $query->get();

        $ledgerData = [];
        $summary = [
            'total_assets' => 0,
            'total_liabilities' => 0,
            'total_equity' => 0,
            'net_income' => 0
        ];

        foreach ($accounts as $account) {
            // Get opening balance (balance before date range)
            $openingBalance = $this->getOpeningBalance($account->id, $request->date_from);
            
            // Get transactions within date range
            $transactions = $this->getTransactions($account->id, $request->date_from, $request->date_to);
            
            $totalDebit = $transactions->sum('debit_amount');
            $totalCredit = $transactions->sum('credit_amount');
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $ledgerData[] = [
                'id' => $account->id,
                'date' => now()->toDateString(),
                'account_code' => $account->account_code,
                'account_name' => $account->name,
                'type' => $account->type,
                'opening_balance' => $openingBalance,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'closing_balance' => $openingBalance + $totalDebit - $totalCredit,
                'balance' => $openingBalance + $totalDebit - $totalCredit,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'description' => 'Account balance as of ' . now()->format('M d, Y'),
                'reference_type' => 'Account',
                'reference_id' => $account->id
            ];

            // Update summary
            switch ($account->type) {
                case 'asset':
                    $summary['total_assets'] += $closingBalance;
                    break;
                case 'liability':
                    $summary['total_liabilities'] += $closingBalance;
                    break;
                case 'equity':
                    $summary['total_equity'] += $closingBalance;
                    break;
                case 'revenue':
                    $summary['net_income'] += $closingBalance;
                    break;
                case 'expense':
                    $summary['net_income'] -= $closingBalance;
                    break;
            }
        }

        return response()->json([
            'data' => $ledgerData,
            'summary' => $summary,
            'pagination' => null,
            'accounts' => $accounts->map(function($account) {
                return [
                    'id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->name
                ];
            })
        ]);
    }

    /**
     * Export general ledger
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
     * Get opening balance for an account
     */
    private function getOpeningBalance(int $accountId, ?string $dateFrom): float
    {
        if (!$dateFrom) {
            return 0;
        }

        $balance = JournalEntryLine::join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.company_id', Auth::user()->company_id)
            ->where('journal_entries.entry_date', '<', $dateFrom)
            ->where('journal_entries.status', 'posted')
            ->selectRaw('SUM(debit_amount) - SUM(credit_amount) as balance')
            ->first();

        return $balance ? (float) $balance->balance : 0;
    }

    /**
     * Get transactions for an account within date range
     */
    private function getTransactions(int $accountId, ?string $dateFrom, ?string $dateTo)
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

        return $query->get(['debit_amount', 'credit_amount']);
    }
}
