<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\PettyCashFund;

class PettyCashFundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample petty cash funds
        $funds = [
            [
                'name' => 'Office Petty Cash',
                'custodian' => 'John Doe',
                'initial_amount' => 1000000,
                'current_balance' => 750000,
                'currency' => 'IDR',
                'description' => 'Main office petty cash fund',
                'location' => 'Main Office',
                'status' => 'active',
                'replenishment_threshold' => 200000
            ],
            [
                'name' => 'Branch Petty Cash',
                'custodian' => 'Jane Smith',
                'initial_amount' => 500000,
                'current_balance' => 300000,
                'currency' => 'IDR',
                'description' => 'Branch office petty cash fund',
                'location' => 'Branch Office',
                'status' => 'active',
                'replenishment_threshold' => 100000
            ]
        ];

        foreach ($funds as $fund) {
            PettyCashFund::create($fund);
        }
    }
}
