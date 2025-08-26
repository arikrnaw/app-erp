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

class TrialBalanceTestSeeder extends Seeder
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

        // Get accounts for transactions
        $cashAccount = ChartOfAccount::where('account_code', '1110')->first(); // Cash on Hand
        $bankAccount = ChartOfAccount::where('account_code', '1120')->first(); // Bank Account
        $arAccount = ChartOfAccount::where('account_code', '1200')->first(); // Accounts Receivable
        $inventoryAccount = ChartOfAccount::where('account_code', '1300')->first(); // Inventory
        $equipmentAccount = ChartOfAccount::where('account_code', '2100')->first(); // Equipment
        $apAccount = ChartOfAccount::where('account_code', '3100')->first(); // Accounts Payable
        $loanAccount = ChartOfAccount::where('account_code', '3300')->first(); // Short-term Loans
        $capitalAccount = ChartOfAccount::where('account_code', '5100')->first(); // Capital
        $salesAccount = ChartOfAccount::where('account_code', '6100')->first(); // Sales Revenue
        $cogsAccount = ChartOfAccount::where('account_code', '7100')->first(); // Cost of Goods Sold
        $salaryAccount = ChartOfAccount::where('account_code', '7210')->first(); // Salaries and Wages
        $rentAccount = ChartOfAccount::where('account_code', '7220')->first(); // Rent Expense

        if (!$cashAccount || !$bankAccount || !$arAccount || !$inventoryAccount || !$equipmentAccount || !$apAccount || !$loanAccount || !$capitalAccount || !$salesAccount || !$cogsAccount || !$salaryAccount || !$rentAccount) {
            $this->command->error('Required accounts not found. Please run ChartOfAccountSeeder first.');
            return;
        }

        // Clear existing journal entries for this company
        JournalEntry::where('company_id', $company->id)->delete();

        // Sample realistic journal entries for current month
        $entries = [
            [
                'entry_date' => Carbon::now()->startOfMonth(),
                'description' => 'Initial capital investment',
                'lines' => [
                    ['account_id' => $cashAccount->id, 'debit_amount' => 10000000, 'credit_amount' => 0, 'description' => 'Cash received'],
                    ['account_id' => $capitalAccount->id, 'debit_amount' => 0, 'credit_amount' => 10000000, 'description' => 'Capital investment']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(2),
                'description' => 'Purchase equipment on credit',
                'lines' => [
                    ['account_id' => $equipmentAccount->id, 'debit_amount' => 5000000, 'credit_amount' => 0, 'description' => 'Equipment purchase'],
                    ['account_id' => $apAccount->id, 'debit_amount' => 0, 'credit_amount' => 5000000, 'description' => 'Equipment payable']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(5),
                'description' => 'Purchase inventory on credit',
                'lines' => [
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 3000000, 'credit_amount' => 0, 'description' => 'Inventory purchase'],
                    ['account_id' => $apAccount->id, 'debit_amount' => 0, 'credit_amount' => 3000000, 'description' => 'Inventory payable']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(10),
                'description' => 'Cash sale of inventory',
                'lines' => [
                    ['account_id' => $cashAccount->id, 'debit_amount' => 4000000, 'credit_amount' => 0, 'description' => 'Cash received'],
                    ['account_id' => $salesAccount->id, 'debit_amount' => 0, 'credit_amount' => 4000000, 'description' => 'Sales revenue'],
                    ['account_id' => $cogsAccount->id, 'debit_amount' => 2400000, 'credit_amount' => 0, 'description' => 'Cost of goods sold'],
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 0, 'credit_amount' => 2400000, 'description' => 'Inventory reduction']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(15),
                'description' => 'Credit sale',
                'lines' => [
                    ['account_id' => $arAccount->id, 'debit_amount' => 2000000, 'credit_amount' => 0, 'description' => 'Accounts receivable'],
                    ['account_id' => $salesAccount->id, 'debit_amount' => 0, 'credit_amount' => 2000000, 'description' => 'Sales revenue'],
                    ['account_id' => $cogsAccount->id, 'debit_amount' => 1200000, 'credit_amount' => 0, 'description' => 'Cost of goods sold'],
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 0, 'credit_amount' => 1200000, 'description' => 'Inventory reduction']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(20),
                'description' => 'Payment of accounts payable',
                'lines' => [
                    ['account_id' => $apAccount->id, 'debit_amount' => 2000000, 'credit_amount' => 0, 'description' => 'Payment to suppliers'],
                    ['account_id' => $cashAccount->id, 'debit_amount' => 0, 'credit_amount' => 2000000, 'description' => 'Cash payment']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(25),
                'description' => 'Operating expenses',
                'lines' => [
                    ['account_id' => $salaryAccount->id, 'debit_amount' => 1500000, 'credit_amount' => 0, 'description' => 'Salary expense'],
                    ['account_id' => $rentAccount->id, 'debit_amount' => 800000, 'credit_amount' => 0, 'description' => 'Rent expense'],
                    ['account_id' => $cashAccount->id, 'debit_amount' => 0, 'credit_amount' => 2300000, 'description' => 'Cash payment']
                ]
            ],
            [
                'entry_date' => Carbon::now()->startOfMonth()->addDays(28),
                'description' => 'Collection of accounts receivable',
                'lines' => [
                    ['account_id' => $cashAccount->id, 'debit_amount' => 1500000, 'credit_amount' => 0, 'description' => 'Cash received'],
                    ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => 1500000, 'description' => 'Accounts receivable']
                ]
            ]
        ];

        foreach ($entries as $entryData) {
            $totalDebit = collect($entryData['lines'])->sum('debit_amount');
            $totalCredit = collect($entryData['lines'])->sum('credit_amount');

            $entry = JournalEntry::create([
                'company_id' => $company->id,
                'entry_number' => JournalEntry::generateEntryNumber($company->id),
                'entry_date' => $entryData['entry_date'],
                'description' => $entryData['description'],
                'status' => 'posted',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'posted_at' => now(),
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

        $this->command->info('Trial Balance Test Data seeded successfully!');
        $this->command->info('Created ' . count($entries) . ' journal entries with realistic financial data.');
    }
}
