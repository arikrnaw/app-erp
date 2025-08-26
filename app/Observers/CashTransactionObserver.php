<?php

namespace App\Observers;

use App\Models\Finance\CashTransaction;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;

class CashTransactionObserver
{
    /**
     * Handle the CashTransaction "created" event.
     */
    public function created(CashTransaction $cashTransaction): void
    {
        // Create journal entry immediately for cash transactions
        if ($cashTransaction->status === 'completed') {
            $this->createJournalEntry($cashTransaction);
        }
    }

    /**
     * Handle the CashTransaction "updated" event.
     */
    public function updated(CashTransaction $cashTransaction): void
    {
        // Check if status changed to completed
        if ($cashTransaction->wasChanged('status') && $cashTransaction->status === 'completed') {
            $this->createJournalEntry($cashTransaction);
        }
    }

    /**
     * Handle the CashTransaction "deleted" event.
     */
    public function deleted(CashTransaction $cashTransaction): void
    {
        // Reverse journal entry if cash transaction is deleted
        if ($cashTransaction->status === 'completed') {
            $this->reverseJournalEntry($cashTransaction);
        }
    }

    /**
     * Handle the CashTransaction "restored" event.
     */
    public function restored(CashTransaction $cashTransaction): void
    {
        //
    }

    /**
     * Handle the CashTransaction "force deleted" event.
     */
    public function forceDeleted(CashTransaction $cashTransaction): void
    {
        //
    }

    /**
     * Create journal entry for completed cash transaction
     */
    private function createJournalEntry(CashTransaction $cashTransaction): void
    {
        // Get accounts based on transaction type
        $cashAccount = ChartOfAccount::where('account_code', '1110')->first(); // Cash on Hand
        $bankAccount = ChartOfAccount::where('account_code', '1120')->first(); // Bank Account
        
        if (!$cashAccount || !$bankAccount) {
            Log::error('Required accounts not found for cash transaction journal entry', [
                'cash_transaction_id' => $cashTransaction->id,
                'cash_account' => $cashAccount?->id,
                'bank_account' => $bankAccount?->id
            ]);
            return;
        }

        // Determine debit and credit accounts based on transaction type
        $debitAccount = null;
        $creditAccount = null;
        $description = '';

        switch ($cashTransaction->type) {
            case 'deposit':
                $debitAccount = $bankAccount;
                $creditAccount = $cashAccount;
                $description = 'Cash deposit to bank';
                break;
            case 'withdrawal':
                $debitAccount = $cashAccount;
                $creditAccount = $bankAccount;
                $description = 'Cash withdrawal from bank';
                break;
            case 'transfer':
                if ($cashTransaction->bank_account_id) {
                    $debitAccount = $bankAccount;
                    $creditAccount = $cashAccount;
                    $description = 'Transfer to bank account';
                } else {
                    $debitAccount = $cashAccount;
                    $creditAccount = $bankAccount;
                    $description = 'Transfer from bank account';
                }
                break;
            case 'expense':
                $debitAccount = ChartOfAccount::where('account_code', '7200')->first(); // Operating Expenses
                $creditAccount = $cashAccount;
                $description = 'Cash expense: ' . $cashTransaction->description;
                break;
            default:
                Log::warning('Unknown cash transaction type', [
                    'cash_transaction_id' => $cashTransaction->id,
                    'type' => $cashTransaction->type
                ]);
                return;
        }

        if (!$debitAccount || !$creditAccount) {
            Log::error('Required accounts not found for cash transaction type', [
                'cash_transaction_id' => $cashTransaction->id,
                'type' => $cashTransaction->type,
                'debit_account' => $debitAccount?->id,
                'credit_account' => $creditAccount?->id
            ]);
            return;
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => $cashTransaction->bankAccount?->company_id ?? 1, // Default company ID
            'entry_number' => JournalEntry::generateEntryNumber($cashTransaction->bankAccount?->company_id ?? 1),
            'entry_date' => $cashTransaction->date,
            'description' => $description,
            'status' => 'posted',
            'total_debit' => $cashTransaction->amount,
            'total_credit' => $cashTransaction->amount,
            'created_by' => $cashTransaction->approved_by ?? 1, // Default user ID
        ]);

        // Create journal entry lines
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $debitAccount->id,
            'debit_amount' => $cashTransaction->amount,
            'credit_amount' => 0,
            'description' => $description,
            'line_number' => 1,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $creditAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $cashTransaction->amount,
            'description' => $description,
            'line_number' => 2,
        ]);

        Log::info('Journal entry created for cash transaction', [
            'cash_transaction_id' => $cashTransaction->id,
            'journal_entry_id' => $journalEntry->id,
            'type' => $cashTransaction->type,
            'amount' => $cashTransaction->amount
        ]);
    }

    /**
     * Reverse journal entry for deleted cash transaction
     */
    private function reverseJournalEntry(CashTransaction $cashTransaction): void
    {
        // Find and reverse the journal entry
        $journalEntry = JournalEntry::where('description', 'LIKE', '%' . $cashTransaction->description . '%')
            ->where('status', 'posted')
            ->first();

        if ($journalEntry) {
            $journalEntry->update(['status' => 'reversed']);
            Log::info('Journal entry reversed for deleted cash transaction', [
                'cash_transaction_id' => $cashTransaction->id,
                'journal_entry_id' => $journalEntry->id
            ]);
        }
    }
}
