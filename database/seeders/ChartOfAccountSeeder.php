<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\User;

class ChartOfAccountSeeder extends Seeder
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

        $accounts = [
            // Assets
            ['account_code' => '1000', 'name' => 'Current Assets', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '1100', 'name' => 'Cash and Cash Equivalents', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '1110', 'name' => 'Cash on Hand', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '1120', 'name' => 'Bank Account', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '1300', 'name' => 'Inventory', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '1400', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'parent_id' => null],
            
            // Fixed Assets
            ['account_code' => '2000', 'name' => 'Fixed Assets', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '2100', 'name' => 'Equipment', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '2200', 'name' => 'Buildings', 'type' => 'asset', 'parent_id' => null],
            ['account_code' => '2300', 'name' => 'Vehicles', 'type' => 'asset', 'parent_id' => null],
            
            // Liabilities
            ['account_code' => '3000', 'name' => 'Current Liabilities', 'type' => 'liability', 'parent_id' => null],
            ['account_code' => '3100', 'name' => 'Accounts Payable', 'type' => 'liability', 'parent_id' => null],
            ['account_code' => '3200', 'name' => 'Accrued Expenses', 'type' => 'liability', 'parent_id' => null],
            ['account_code' => '3300', 'name' => 'Short-term Loans', 'type' => 'liability', 'parent_id' => null],
            
            // Long-term Liabilities
            ['account_code' => '4000', 'name' => 'Long-term Liabilities', 'type' => 'liability', 'parent_id' => null],
            ['account_code' => '4100', 'name' => 'Long-term Loans', 'type' => 'liability', 'parent_id' => null],
            
            // Equity
            ['account_code' => '5000', 'name' => 'Owner\'s Equity', 'type' => 'equity', 'parent_id' => null],
            ['account_code' => '5100', 'name' => 'Capital', 'type' => 'equity', 'parent_id' => null],
            ['account_code' => '5200', 'name' => 'Retained Earnings', 'type' => 'equity', 'parent_id' => null],
            
            // Revenue
            ['account_code' => '6000', 'name' => 'Revenue', 'type' => 'revenue', 'parent_id' => null],
            ['account_code' => '6100', 'name' => 'Sales Revenue', 'type' => 'revenue', 'parent_id' => null],
            ['account_code' => '6200', 'name' => 'Service Revenue', 'type' => 'revenue', 'parent_id' => null],
            ['account_code' => '6300', 'name' => 'Other Income', 'type' => 'revenue', 'parent_id' => null],
            
            // Expenses
            ['account_code' => '7000', 'name' => 'Expenses', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7100', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7200', 'name' => 'Operating Expenses', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7210', 'name' => 'Salaries and Wages', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7220', 'name' => 'Rent Expense', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7230', 'name' => 'Utilities', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7240', 'name' => 'Office Supplies', 'type' => 'expense', 'parent_id' => null],
            ['account_code' => '7250', 'name' => 'Depreciation', 'type' => 'expense', 'parent_id' => null],
        ];

        foreach ($accounts as $accountData) {
            ChartOfAccount::create([
                'company_id' => $company->id,
                'account_code' => $accountData['account_code'],
                'name' => $accountData['name'],
                'description' => 'Default account for ' . $accountData['name'],
                'type' => $accountData['type'],
                'parent_id' => $accountData['parent_id'],
                'balance' => 0,
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
