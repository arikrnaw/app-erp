<?php

namespace App\Observers;

use App\Models\Finance\AssetPurchase;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;

class AssetPurchaseObserver
{
    /**
     * Handle the AssetPurchase "created" event.
     */
    public function created(AssetPurchase $assetPurchase): void
    {
        // Do nothing on creation - wait for approval
    }

    /**
     * Handle the AssetPurchase "updated" event.
     */
    public function updated(AssetPurchase $assetPurchase): void
    {
        // Check if status changed to approved
        if ($assetPurchase->wasChanged('status') && $assetPurchase->status === 'approved') {
            $this->createJournalEntry($assetPurchase);
        }
    }

    /**
     * Handle the AssetPurchase "deleted" event.
     */
    public function deleted(AssetPurchase $assetPurchase): void
    {
        // Reverse journal entry if asset purchase is deleted
        if ($assetPurchase->status === 'approved') {
            $this->reverseJournalEntry($assetPurchase);
        }
    }

    /**
     * Handle the AssetPurchase "restored" event.
     */
    public function restored(AssetPurchase $assetPurchase): void
    {
        //
    }

    /**
     * Handle the AssetPurchase "force deleted" event.
     */
    public function forceDeleted(AssetPurchase $assetPurchase): void
    {
        //
    }

    /**
     * Create journal entry for approved asset purchase
     */
    private function createJournalEntry(AssetPurchase $assetPurchase): void
    {
        // Get accounts
        $fixedAssetAccount = ChartOfAccount::where('account_code', '2000')->first(); // Fixed Assets
        $cashAccount = ChartOfAccount::where('account_code', '1110')->first(); // Cash on Hand
        $bankAccount = ChartOfAccount::where('account_code', '1120')->first(); // Bank Account
        
        if (!$fixedAssetAccount || (!$cashAccount && !$bankAccount)) {
            Log::error('Required accounts not found for asset purchase journal entry', [
                'asset_purchase_id' => $assetPurchase->id,
                'fixed_asset_account' => $fixedAssetAccount?->id,
                'cash_account' => $cashAccount?->id,
                'bank_account' => $bankAccount?->id
            ]);
            return;
        }

        // Determine payment account (prefer bank account if available)
        $paymentAccount = $bankAccount ?: $cashAccount;

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => $assetPurchase->department->company_id,
            'entry_number' => JournalEntry::generateEntryNumber($assetPurchase->department->company_id),
            'entry_date' => $assetPurchase->purchase_date,
            'description' => 'Asset Purchase: ' . $assetPurchase->asset_name,
            'status' => 'posted',
            'total_debit' => $assetPurchase->total_cost,
            'total_credit' => $assetPurchase->total_cost,
            'created_by' => $assetPurchase->created_by,
        ]);

        // Create journal entry lines
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $fixedAssetAccount->id,
            'debit_amount' => $assetPurchase->total_cost,
            'credit_amount' => 0,
            'description' => 'Asset: ' . $assetPurchase->asset_name,
            'line_number' => 1,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $paymentAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $assetPurchase->total_cost,
            'description' => 'Payment for: ' . $assetPurchase->asset_name,
            'line_number' => 2,
        ]);

        Log::info('Journal entry created for asset purchase', [
            'asset_purchase_id' => $assetPurchase->id,
            'journal_entry_id' => $journalEntry->id,
            'amount' => $assetPurchase->total_cost
        ]);
    }

    /**
     * Reverse journal entry for deleted asset purchase
     */
    private function reverseJournalEntry(AssetPurchase $assetPurchase): void
    {
        // Find and reverse the journal entry
        $journalEntry = JournalEntry::where('description', 'LIKE', '%Asset Purchase: ' . $assetPurchase->asset_name . '%')
            ->where('status', 'posted')
            ->first();

        if ($journalEntry) {
            $journalEntry->update(['status' => 'reversed']);
            Log::info('Journal entry reversed for deleted asset purchase', [
                'asset_purchase_id' => $assetPurchase->id,
                'journal_entry_id' => $journalEntry->id
            ]);
        }
    }
}
