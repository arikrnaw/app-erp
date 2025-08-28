<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\BankAccount;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample bank accounts
        $accounts = [
            [
                'name' => 'BCA Main Account',
                'account_number' => '1234567890',
                'description' => 'Main business account',
                'bank_name' => 'Bank Central Asia',
                'bank_branch' => 'Jakarta Pusat',
                'swift_code' => 'CENAIDJA',
                'iban' => 'ID12345678901234567890',
                'currency' => 'IDR',
                'opening_balance' => 10000000,
                'opening_date' => '2024-01-01',
                'account_type' => 'checking',
                'status' => 'active',
                'reconcile_automatically' => true,
                'allow_overdraft' => false,
                'include_in_cash_flow' => true,
                'balance' => 15000000,
                'notes' => 'Primary operating account'
            ],
            [
                'name' => 'Mandiri Savings',
                'account_number' => '0987654321',
                'description' => 'Savings for emergency fund',
                'bank_name' => 'Bank Mandiri',
                'bank_branch' => 'Jakarta Selatan',
                'swift_code' => 'BMRIIDJA',
                'iban' => 'ID09876543210987654321',
                'currency' => 'IDR',
                'opening_balance' => 5000000,
                'opening_date' => '2024-01-01',
                'account_type' => 'savings',
                'status' => 'active',
                'reconcile_automatically' => false,
                'allow_overdraft' => false,
                'include_in_cash_flow' => true,
                'balance' => 7500000,
                'notes' => 'Emergency fund account'
            ]
        ];

        foreach ($accounts as $account) {
            BankAccount::create($account);
        }
    }
}
