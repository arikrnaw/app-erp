<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\ReconciliationAdjustment;
use App\Models\Finance\BankAccount;
use App\Models\User;

class ReconciliationAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first bank account and user
        $bankAccount = BankAccount::first();
        $user = User::first();

        if (!$bankAccount || !$user) {
            $this->command->info('Bank account or user not found. Skipping adjustments seeding.');
            return;
        }

        $adjustments = [
            // Bank Charges
            [
                'type' => 'bank_charge',
                'description' => 'Biaya Admin Bulanan',
                'amount' => -25000,
                'date' => now()->subDays(5),
                'reference' => 'ADM-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Biaya admin bulanan rekening',
                'approved' => true,
                'created_by' => $user->id
            ],
            [
                'type' => 'bank_charge',
                'description' => 'Biaya Transfer Antar Bank',
                'amount' => -15000,
                'date' => now()->subDays(3),
                'reference' => 'TRF-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Biaya transfer ke bank lain',
                'approved' => true,
                'created_by' => $user->id
            ],

            // Interest Earned
            [
                'type' => 'interest_earned',
                'description' => 'Bunga Tabungan',
                'amount' => 125000,
                'date' => now()->subDays(1),
                'reference' => 'BUN-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Bunga tabungan bulanan',
                'approved' => true,
                'created_by' => $user->id
            ],
            [
                'type' => 'interest_earned',
                'description' => 'Bunga Deposito',
                'amount' => 500000,
                'date' => now()->subDays(10),
                'reference' => 'DEP-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Bunga deposito 3 bulan',
                'approved' => true,
                'created_by' => $user->id
            ],

            // Service Fees
            [
                'type' => 'service_fee',
                'description' => 'Biaya Safe Deposit Box',
                'amount' => -75000,
                'date' => now()->subDays(15),
                'reference' => 'SDB-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Biaya sewa safe deposit box',
                'approved' => true,
                'created_by' => $user->id
            ],
            [
                'type' => 'service_fee',
                'description' => 'Biaya Wealth Management',
                'amount' => -200000,
                'date' => now()->subDays(20),
                'reference' => 'WM-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Biaya layanan wealth management',
                'approved' => false,
                'created_by' => $user->id
            ],

            // Other
            [
                'type' => 'other',
                'description' => 'Biaya Notaris',
                'amount' => -150000,
                'date' => now()->subDays(25),
                'reference' => 'NOT-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Biaya notaris untuk dokumen',
                'approved' => true,
                'created_by' => $user->id
            ],
            [
                'type' => 'other',
                'description' => 'Biaya Audit',
                'amount' => -500000,
                'date' => now()->subDays(30),
                'reference' => 'AUD-001',
                'bank_account_id' => $bankAccount->id,
                'notes' => 'Biaya audit tahunan',
                'approved' => false,
                'created_by' => $user->id
            ]
        ];

        foreach ($adjustments as $adjustment) {
            ReconciliationAdjustment::create($adjustment);
        }

        $this->command->info('Reconciliation adjustments seeded successfully!');
    }
}
