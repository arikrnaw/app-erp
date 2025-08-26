<?php

namespace App\Observers;

use App\Models\Finance\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        // Do nothing on creation - wait for approval
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        // Check if status changed to approved
        if ($expense->wasChanged('status') && $expense->status === 'approved') {
            $this->createJournalEntry($expense);
        }
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        // Reverse journal entry if expense is deleted
        if ($expense->status === 'approved') {
            $this->reverseJournalEntry($expense);
        }
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void
    {
        //
    }

    /**
     * Create journal entry for approved expense
     */
    private function createJournalEntry(Expense $expense): void
    {
        // Get expense category account
        $expenseAccount = ChartOfAccount::where('account_code', '7200')->first(); // Operating Expenses
        $cashAccount = ChartOfAccount::where('account_code', '1110')->first(); // Cash on Hand
        
        if (!$expenseAccount || !$cashAccount) {
            Log::error('Required accounts not found for expense journal entry', [
                'expense_id' => $expense->id,
                'expense_account' => $expenseAccount?->id,
                'cash_account' => $cashAccount?->id
            ]);
            return;
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => $expense->department->company_id,
            'entry_number' => JournalEntry::generateEntryNumber($expense->department->company_id),
            'entry_date' => $expense->expense_date,
            'description' => 'Expense: ' . $expense->description,
            'status' => 'posted',
            'total_debit' => $expense->total_amount,
            'total_credit' => $expense->total_amount,
            'created_by' => $expense->created_by,
        ]);

        // Create journal entry lines
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $expenseAccount->id,
            'debit_amount' => $expense->total_amount,
            'credit_amount' => 0,
            'description' => $expense->description,
            'line_number' => 1,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cashAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $expense->total_amount,
            'description' => 'Payment for: ' . $expense->description,
            'line_number' => 2,
        ]);

        Log::info('Journal entry created for expense', [
            'expense_id' => $expense->id,
            'journal_entry_id' => $journalEntry->id,
            'amount' => $expense->total_amount
        ]);
    }

    /**
     * Reverse journal entry for deleted expense
     */
    private function reverseJournalEntry(Expense $expense): void
    {
        // Find and reverse the journal entry
        $journalEntry = JournalEntry::where('description', 'LIKE', '%Expense: ' . $expense->description . '%')
            ->where('status', 'posted')
            ->first();

        if ($journalEntry) {
            $journalEntry->update(['status' => 'reversed']);
            Log::info('Journal entry reversed for deleted expense', [
                'expense_id' => $expense->id,
                'journal_entry_id' => $journalEntry->id
            ]);
        }
    }
}
