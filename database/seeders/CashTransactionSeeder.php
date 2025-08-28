<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\CashTransaction;
use App\Models\Finance\BankAccount;
use Carbon\Carbon;

class CashTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a sample bank account
        $bankAccount = BankAccount::first();
        
        if (!$bankAccount) {
            $bankAccount = BankAccount::create([
                'name' => 'Main Bank Account',
                'account_number' => '1234567890',
                'description' => 'Main operating account',
                'bank_name' => 'Sample Bank',
                'bank_branch' => 'Jakarta Central',
                'currency' => 'IDR',
                'account_type' => 'checking',
                'opening_balance' => 10000000,
                'balance' => 10000000,
                'status' => 'active',
                'reconcile_automatically' => false,
                'allow_overdraft' => false,
                'include_in_cash_flow' => true
            ]);
        }

        // Sample transactions
        $transactions = [
            [
                'transaction_number' => 'CTX-001',
                'type' => 'deposit',
                'bank_account_id' => $bankAccount->id,
                'description' => 'Customer Payment - Invoice #001',
                'amount' => 5000000,
                'currency' => 'IDR',
                'transaction_date' => Carbon::now()->subDays(2),
                'status' => 'completed'
            ],
            [
                'transaction_number' => 'CTX-002',
                'type' => 'withdrawal',
                'bank_account_id' => $bankAccount->id,
                'description' => 'Office Supplies Purchase',
                'amount' => -1500000,
                'currency' => 'IDR',
                'transaction_date' => Carbon::now()->subDays(1),
                'status' => 'completed'
            ],
            [
                'transaction_number' => 'CTX-003',
                'type' => 'transfer',
                'bank_account_id' => $bankAccount->id,
                'description' => 'Inter-bank Transfer',
                'amount' => -3000000,
                'currency' => 'IDR',
                'transaction_date' => Carbon::now(),
                'status' => 'completed'
            ],
            [
                'transaction_number' => 'CTX-004',
                'type' => 'income',
                'bank_account_id' => $bankAccount->id,
                'description' => 'Interest Income',
                'amount' => 250000,
                'currency' => 'IDR',
                'transaction_date' => Carbon::now()->subDays(3),
                'status' => 'completed'
            ],
            [
                'transaction_number' => 'CTX-005',
                'type' => 'expense',
                'bank_account_id' => $bankAccount->id,
                'description' => 'Utility Bills',
                'amount' => -800000,
                'currency' => 'IDR',
                'transaction_date' => Carbon::now()->subDays(4),
                'status' => 'completed'
            ]
        ];

        foreach ($transactions as $transaction) {
            CashTransaction::create($transaction);
        }
    }
}
