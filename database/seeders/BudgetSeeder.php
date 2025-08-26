<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Finance\BudgetPeriod;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetVariance;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $user = User::first();

        if (!$company || !$user) {
            $this->command->error('Company or User not found. Please run CompanySeeder and UserSeeder first.');
            return;
        }

        // Create Budget Periods
        $periods = [
            [
                'name' => 'Q1 2024',
                'description' => 'First Quarter 2024',
                'start_date' => '2024-01-01',
                'end_date' => '2024-03-31',
                'fiscal_year' => '2024',
                'status' => 'active',
                'notes' => 'Q1 Budget Period'
            ],
            [
                'name' => 'Q2 2024',
                'description' => 'Second Quarter 2024',
                'start_date' => '2024-04-01',
                'end_date' => '2024-06-30',
                'fiscal_year' => '2024',
                'status' => 'active',
                'notes' => 'Q2 Budget Period'
            ],
            [
                'name' => 'Q3 2024',
                'description' => 'Third Quarter 2024',
                'start_date' => '2024-07-01',
                'end_date' => '2024-09-30',
                'fiscal_year' => '2024',
                'status' => 'planning',
                'notes' => 'Q3 Budget Period'
            ]
        ];

        foreach ($periods as $periodData) {
            BudgetPeriod::create(array_merge($periodData, [
                'company_id' => $company->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));
        }

        // Create Budget Categories
        $categories = [
            [
                'name' => 'Operating Expenses',
                'description' => 'Day-to-day operational expenses',
                'type' => 'expense',
                'status' => 'active'
            ],
            [
                'name' => 'Marketing & Sales',
                'description' => 'Marketing and sales related expenses',
                'type' => 'expense',
                'status' => 'active'
            ],
            [
                'name' => 'Research & Development',
                'description' => 'R&D and innovation expenses',
                'type' => 'expense',
                'status' => 'active'
            ],
            [
                'name' => 'Capital Expenditure',
                'description' => 'Long-term asset investments',
                'type' => 'asset',
                'status' => 'active'
            ],
            [
                'name' => 'Revenue',
                'description' => 'Expected revenue streams',
                'type' => 'revenue',
                'status' => 'active'
            ]
        ];

        foreach ($categories as $categoryData) {
            BudgetCategory::create(array_merge($categoryData, [
                'company_id' => $company->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));
        }

        // Create Budgets
        $budgets = [
            [
                'category_id' => 1, // Operating Expenses
                'period_id' => 1, // Q1 2024
                'period_start' => '2024-01-01',
                'period_end' => '2024-03-31',
                'amount' => 50000000,
                'description' => 'Q1 Operating Expenses Budget',
                'status' => 'active'
            ],
            [
                'category_id' => 2, // Marketing & Sales
                'period_id' => 1, // Q1 2024
                'period_start' => '2024-01-01',
                'period_end' => '2024-03-31',
                'amount' => 30000000,
                'description' => 'Q1 Marketing Budget',
                'status' => 'active'
            ],
            [
                'category_id' => 3, // Research & Development
                'period_id' => 1, // Q1 2024
                'period_start' => '2024-01-01',
                'period_end' => '2024-03-31',
                'amount' => 20000000,
                'description' => 'Q1 R&D Budget',
                'status' => 'active'
            ],
            [
                'category_id' => 4, // Capital Expenditure
                'period_id' => 1, // Q1 2024
                'period_start' => '2024-01-01',
                'period_end' => '2024-03-31',
                'amount' => 100000000,
                'description' => 'Q1 Capital Budget',
                'status' => 'active'
            ],
            [
                'category_id' => 5, // Revenue
                'period_id' => 1, // Q1 2024
                'period_start' => '2024-01-01',
                'period_end' => '2024-03-31',
                'amount' => 250000000,
                'description' => 'Q1 Revenue Target',
                'status' => 'active'
            ]
        ];

        foreach ($budgets as $budgetData) {
            Budget::create(array_merge($budgetData, [
                'company_id' => $company->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));
        }

        // Create Budget Variances (Actual vs Budget)
        $variances = [
            [
                'budget_id' => 1, // Operating Expenses
                'actual_amount' => 52000000,
                'variance' => 2000000,
                'variance_percentage' => 4.0,
                'variance_date' => '2024-03-31',
                'variance_reason' => 'Higher utility costs than expected',
                'corrective_action' => 'Review energy efficiency measures',
                'notes' => 'Q1 variance analysis'
            ],
            [
                'budget_id' => 2, // Marketing & Sales
                'actual_amount' => 28000000,
                'variance' => -2000000,
                'variance_percentage' => -6.67,
                'variance_date' => '2024-03-31',
                'variance_reason' => 'Some marketing campaigns delayed to Q2',
                'corrective_action' => 'Accelerate Q2 marketing activities',
                'notes' => 'Q1 variance analysis'
            ],
            [
                'budget_id' => 3, // Research & Development
                'actual_amount' => 21000000,
                'variance' => 1000000,
                'variance_percentage' => 5.0,
                'variance_date' => '2024-03-31',
                'variance_reason' => 'Additional research materials purchased',
                'corrective_action' => 'Optimize R&D procurement process',
                'notes' => 'Q1 variance analysis'
            ]
        ];

        foreach ($variances as $varianceData) {
            BudgetVariance::create(array_merge($varianceData, [
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));
        }

        $this->command->info('Budget data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($periods) . ' Budget Periods');
        $this->command->info('- ' . count($categories) . ' Budget Categories');
        $this->command->info('- ' . count($budgets) . ' Budgets');
        $this->command->info('- ' . count($variances) . ' Budget Variances');
    }
}
