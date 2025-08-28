<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\BankAccount;
use App\Models\Finance\BankReconciliation;
use App\Models\Finance\BankStatement;
use App\Models\Finance\BankTransaction;
use App\Models\Finance\ReconciliationAdjustment;
use App\Models\Finance\TransactionMatch;
use App\Models\User;

class BankReconciliationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user
        $user = User::first() ?? User::factory()->create();

        // Get or create bank accounts
        $bankAccounts = BankAccount::where('status', 'active')->take(3)->get();
        
        if ($bankAccounts->isEmpty()) {
            $bankAccounts = BankAccount::factory(3)->create([
                'status' => 'active',
                'balance' => 1000000
            ]);
        }

        foreach ($bankAccounts as $bankAccount) {
            // Create bank statements
            $statement = BankStatement::create([
                'bank_account_id' => $bankAccount->id,
                'statement_date' => now()->subDays(30),
                'opening_balance' => 950000,
                'closing_balance' => 1000000,
                'import_status' => 'completed',
                'total_transactions' => 15,
                'processed_transactions' => 15,
                'created_by' => $user->id
            ]);

            // Create bank transactions
            $transactions = [
                ['description' => 'Salary Deposit', 'amount' => 5000000, 'transaction_type' => 'deposit', 'transaction_date' => now()->subDays(25)],
                ['description' => 'ATM Withdrawal', 'amount' => -500000, 'transaction_type' => 'withdrawal', 'transaction_date' => now()->subDays(20)],
                ['description' => 'Online Transfer', 'amount' => -1000000, 'transaction_type' => 'transfer', 'transaction_date' => now()->subDays(15)],
                ['description' => 'Bank Charge', 'amount' => -25000, 'transaction_type' => 'charge', 'transaction_date' => now()->subDays(10)],
                ['description' => 'Interest Earned', 'amount' => 15000, 'transaction_type' => 'deposit', 'transaction_date' => now()->subDays(5)]
            ];

            foreach ($transactions as $transactionData) {
                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'statement_id' => $statement->id,
                    'transaction_date' => $transactionData['transaction_date'],
                    'description' => $transactionData['description'],
                    'amount' => $transactionData['amount'],
                    'transaction_type' => $transactionData['transaction_type'],
                    'is_reconciled' => false,
                    'created_by' => $user->id
                ]);
            }

            // Create reconciliations
            $reconciliation = BankReconciliation::create([
                'bank_account_id' => $bankAccount->id,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'bank_statement_balance' => 1000000,
                'book_balance' => 975000,
                'difference' => 25000,
                'status' => 'in_progress',
                'created_by' => $user->id
            ]);

            // Create adjustments
            ReconciliationAdjustment::create([
                'reconciliation_id' => $reconciliation->id,
                'type' => 'bank_charge',
                'description' => 'Monthly Service Fee',
                'amount' => -25000,
                'created_by' => $user->id
            ]);

            // Create transaction matches (some transactions) - commented out for now
            // $bankTransactions = BankTransaction::where('bank_account_id', $bankAccount->id)->take(3)->get();
            
            // foreach ($bankTransactions->take(2) as $index => $transaction) {
            //     TransactionMatch::create([
            //         'reconciliation_id' => $reconciliation->id,
            //         'bank_transaction_id' => $transaction->id,
            //         'book_transaction_id' => 1, // Assuming journal entry line exists
            //         'match_score' => 95 - ($index * 5),
            //         'match_type' => $index === 0 ? 'exact' : 'partial',
            //         'created_by' => $user->id
            //     ]);

            //     // Mark as reconciled
            //     $transaction->update(['is_reconciled' => true]);
            // }
        }

        // Create some completed reconciliations
        BankReconciliation::create([
            'bank_account_id' => $bankAccounts->first()->id,
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'bank_statement_balance' => 950000,
            'book_balance' => 950000,
            'difference' => 0,
            'status' => 'completed',
            'reconciliation_date' => now()->subMonth()->endOfMonth(),
            'created_by' => $user->id,
            'approved_by' => $user->id,
            'approved_at' => now()->subMonth()->endOfMonth()
        ]);

        $this->command->info('Bank Reconciliation sample data created successfully!');
    }
}
