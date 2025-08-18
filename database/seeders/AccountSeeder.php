<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        // Asset Accounts
        Account::create([
            'company_id' => $company->id,
            'account_number' => '1000',
            'name' => 'Cash',
            'type' => 'asset',
            'category' => 'cash',
            'opening_balance' => 50000.00,
            'current_balance' => 50000.00,
            'description' => 'Cash on hand and in bank',
            'status' => 'active',
        ]);

        Account::create([
            'company_id' => $company->id,
            'account_number' => '1100',
            'name' => 'Accounts Receivable',
            'type' => 'asset',
            'category' => 'accounts_receivable',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'Amounts owed by customers',
            'status' => 'active',
        ]);

        Account::create([
            'company_id' => $company->id,
            'account_number' => '1200',
            'name' => 'Inventory',
            'type' => 'asset',
            'category' => 'inventory',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'Product inventory',
            'status' => 'active',
        ]);

        Account::create([
            'company_id' => $company->id,
            'account_number' => '1500',
            'name' => 'Equipment',
            'type' => 'asset',
            'category' => 'fixed_assets',
            'opening_balance' => 25000.00,
            'current_balance' => 25000.00,
            'description' => 'Office equipment and furniture',
            'status' => 'active',
        ]);

        // Liability Accounts
        Account::create([
            'company_id' => $company->id,
            'account_number' => '2000',
            'name' => 'Accounts Payable',
            'type' => 'liability',
            'category' => 'accounts_payable',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'Amounts owed to suppliers',
            'status' => 'active',
        ]);

        Account::create([
            'company_id' => $company->id,
            'account_number' => '2500',
            'name' => 'Bank Loan',
            'type' => 'liability',
            'category' => 'loans',
            'opening_balance' => 100000.00,
            'current_balance' => 100000.00,
            'description' => 'Long-term bank loan',
            'status' => 'active',
        ]);

        // Equity Accounts
        Account::create([
            'company_id' => $company->id,
            'account_number' => '3000',
            'name' => 'Capital',
            'type' => 'equity',
            'category' => 'capital',
            'opening_balance' => 100000.00,
            'current_balance' => 100000.00,
            'description' => 'Owner\'s capital investment',
            'status' => 'active',
        ]);

        Account::create([
            'company_id' => $company->id,
            'account_number' => '3500',
            'name' => 'Retained Earnings',
            'type' => 'equity',
            'category' => 'retained_earnings',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'Accumulated profits',
            'status' => 'active',
        ]);

        // Revenue Accounts
        Account::create([
            'company_id' => $company->id,
            'account_number' => '4000',
            'name' => 'Sales Revenue',
            'type' => 'revenue',
            'category' => 'sales',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'Revenue from product sales',
            'status' => 'active',
        ]);

        // Expense Accounts
        Account::create([
            'company_id' => $company->id,
            'account_number' => '5000',
            'name' => 'Cost of Goods Sold',
            'type' => 'expense',
            'category' => 'cost_of_goods_sold',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'Cost of products sold',
            'status' => 'active',
        ]);

        Account::create([
            'company_id' => $company->id,
            'account_number' => '6000',
            'name' => 'Operating Expenses',
            'type' => 'expense',
            'category' => 'operating_expenses',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'description' => 'General operating expenses',
            'status' => 'active',
        ]);
    }
}
