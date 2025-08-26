<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;

class JournalEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company and user
        $company = Company::first();
        $user = User::first();

        if (!$company || !$user) {
            $this->command->error('Company or User not found. Please run CompanySeeder and UserSeeder first.');
            return;
        }

        // Get some accounts for transactions
        $cashAccount = ChartOfAccount::where('account_code', '1110')->first(); // Cash on Hand
        $bankAccount = ChartOfAccount::where('account_code', '1120')->first(); // Bank Account
        $arAccount = ChartOfAccount::where('account_code', '1200')->first(); // Accounts Receivable
        $inventoryAccount = ChartOfAccount::where('account_code', '1300')->first(); // Inventory
        $apAccount = ChartOfAccount::where('account_code', '3100')->first(); // Accounts Payable
        $salesAccount = ChartOfAccount::where('account_code', '6100')->first(); // Sales Revenue
        $cogsAccount = ChartOfAccount::where('account_code', '7100')->first(); // Cost of Goods Sold
        $expenseAccount = ChartOfAccount::where('account_code', '7200')->first(); // Operating Expenses

        if (!$cashAccount || !$bankAccount || !$arAccount || !$inventoryAccount || !$apAccount || !$salesAccount || !$cogsAccount || !$expenseAccount) {
            $this->command->error('Required accounts not found. Please run ChartOfAccountSeeder first.');
            return;
        }

        // Sample journal entries
        $entries = [
            [
                'entry_date' => Carbon::now()->subDays(30),
                'description' => 'Initial capital investment',
                'lines' => [
                    ['account_id' => $cashAccount->id, 'debit_amount' => 10000000, 'credit_amount' => 0, 'description' => 'Initial capital'],
                    ['account_id' => $bankAccount->id, 'debit_amount' => 5000000, 'credit_amount' => 0, 'description' => 'Initial capital'],
                    ['account_id' => $salesAccount->id, 'debit_amount' => 0, 'credit_amount' => 15000000, 'description' => 'Initial capital']
                ]
            ],
            [
                'entry_date' => Carbon::now()->subDays(25),
                'description' => 'Purchase inventory on credit',
                'lines' => [
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 5000000, 'credit_amount' => 0, 'description' => 'Purchase inventory'],
                    ['account_id' => $apAccount->id, 'debit_amount' => 0, 'credit_amount' => 5000000, 'description' => 'Purchase inventory']
                ]
            ],
            [
                'entry_date' => Carbon::now()->subDays(20),
                'description' => 'Cash sale of inventory',
                'lines' => [
                    ['account_id' => $cashAccount->id, 'debit_amount' => 3000000, 'credit_amount' => 0, 'description' => 'Cash sale'],
                    ['account_id' => $salesAccount->id, 'debit_amount' => 0, 'credit_amount' => 3000000, 'description' => 'Cash sale'],
                    ['account_id' => $cogsAccount->id, 'debit_amount' => 2000000, 'credit_amount' => 0, 'description' => 'Cost of goods sold'],
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 0, 'credit_amount' => 2000000, 'description' => 'Cost of goods sold']
                ]
            ],
            [
                'entry_date' => Carbon::now()->subDays(15),
                'description' => 'Credit sale to customer',
                'lines' => [
                    ['account_id' => $arAccount->id, 'debit_amount' => 4000000, 'credit_amount' => 0, 'description' => 'Credit sale'],
                    ['account_id' => $salesAccount->id, 'debit_amount' => 0, 'credit_amount' => 4000000, 'description' => 'Credit sale'],
                    ['account_id' => $cogsAccount->id, 'debit_amount' => 2500000, 'credit_amount' => 0, 'description' => 'Cost of goods sold'],
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 0, 'credit_amount' => 2500000, 'description' => 'Cost of goods sold']
                ]
            ],
            [
                'entry_date' => Carbon::now()->subDays(10),
                'description' => 'Payment received from customer',
                'lines' => [
                    ['account_id' => $cashAccount->id, 'debit_amount' => 4000000, 'credit_amount' => 0, 'description' => 'Payment received'],
                    ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => 4000000, 'description' => 'Payment received']
                ]
            ],
            [
                'entry_date' => Carbon::now()->subDays(5),
                'description' => 'Payment to supplier',
                'lines' => [
                    ['account_id' => $apAccount->id, 'debit_amount' => 3000000, 'credit_amount' => 0, 'description' => 'Payment to supplier'],
                    ['account_id' => $bankAccount->id, 'debit_amount' => 0, 'credit_amount' => 3000000, 'description' => 'Payment to supplier']
                ]
            ],
            [
                'entry_date' => Carbon::now()->subDays(2),
                'description' => 'Operating expenses',
                'lines' => [
                    ['account_id' => $expenseAccount->id, 'debit_amount' => 500000, 'credit_amount' => 0, 'description' => 'Operating expenses'],
                    ['account_id' => $cashAccount->id, 'debit_amount' => 0, 'credit_amount' => 500000, 'description' => 'Operating expenses']
                ]
            ]
        ];

        foreach ($entries as $entryData) {
            $entry = JournalEntry::create([
                'company_id' => $company->id,
                'entry_number' => JournalEntry::generateEntryNumber($company->id),
                'entry_date' => $entryData['entry_date'],
                'description' => $entryData['description'],
                'status' => 'posted',
                'total_debit' => collect($entryData['lines'])->sum('debit_amount'),
                'total_credit' => collect($entryData['lines'])->sum('credit_amount'),
                'created_by' => $user->id,
            ]);

            foreach ($entryData['lines'] as $index => $lineData) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $lineData['account_id'],
                    'debit_amount' => $lineData['debit_amount'],
                    'credit_amount' => $lineData['credit_amount'],
                    'description' => $lineData['description'],
                    'line_number' => $index + 1,
                ]);
            }
        }

        $this->command->info('Journal Entries seeded successfully!');
    }
}
