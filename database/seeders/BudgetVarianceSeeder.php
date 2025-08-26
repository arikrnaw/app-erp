<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetVariance;
use App\Models\User;
use App\Models\Company;

class BudgetVarianceSeeder extends Seeder
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

        // Get existing budgets
        $budgets = Budget::all();

        if ($budgets->isEmpty()) {
            $this->command->error('No budgets found. Please run BudgetSeeder first.');
            return;
        }

        // Create sample variance data for each budget
        foreach ($budgets as $budget) {
            // Create 2-3 variance records per budget to simulate actual spending
            $numVariances = rand(2, 3);
            
            for ($i = 0; $i < $numVariances; $i++) {
                $actualAmount = rand(1000000, (int)($budget->amount * 0.8)); // 80% of budget max
                $variance = $actualAmount - $budget->amount;
                $variancePercentage = ($variance / $budget->amount) * 100;
                
                BudgetVariance::create([
                    'budget_id' => $budget->id,
                    'actual_amount' => $actualAmount,
                    'variance' => $variance,
                    'variance_percentage' => $variancePercentage,
                    'variance_date' => now()->subDays(rand(1, 30)),
                    'variance_reason' => $this->getRandomReason(),
                    'corrective_action' => $this->getRandomAction(),
                    'notes' => 'Sample variance data for testing',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }

        $this->command->info('BudgetVariance data seeded successfully!');
    }

    private function getRandomReason(): string
    {
        $reasons = [
            'Unexpected price increase',
            'Additional requirements',
            'Scope change',
            'Market conditions',
            'Supplier issues',
            'Quality improvements',
            'Regulatory compliance',
            'Emergency expenses'
        ];

        return $reasons[array_rand($reasons)];
    }

    private function getRandomAction(): string
    {
        $actions = [
            'Review and adjust budget',
            'Negotiate with suppliers',
            'Optimize processes',
            'Seek alternative solutions',
            'Implement cost controls',
            'Request additional funding',
            'Prioritize essential items',
            'Delay non-critical expenses'
        ];

        return $actions[array_rand($actions)];
    }
}
