<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FinancialReport;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class FinancialReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $companies = Company::all();

        if ($users->isEmpty() || $companies->isEmpty()) {
            $this->command->warn('No users or companies found. Please run UserSeeder and CompanySeeder first.');
            return;
        }

        $reportTypes = [
            'income_statement',
            'balance_sheet',
            'cash_flow',
            'profit_loss',
            'revenue_analysis',
            'expense_analysis',
            'budget_variance',
            'custom'
        ];

        $periodTypes = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        $statuses = ['draft', 'published', 'archived'];

        for ($i = 1; $i <= 20; $i++) {
            $startDate = Carbon::now()->subDays(rand(30, 365));
            $endDate = $startDate->copy()->addDays(rand(1, 30));
            
            $totalRevenue = rand(10000000, 1000000000);
            $totalExpenses = rand(5000000, $totalRevenue * 0.8);
            $netProfit = $totalRevenue - $totalExpenses;
            $grossMargin = $totalRevenue > 0 ? (($totalRevenue - $totalExpenses) / $totalRevenue) * 100 : 0;
            $operatingMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

            FinancialReport::create([
                'report_code' => 'FR-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'title' => $this->getReportTitle($reportTypes[array_rand($reportTypes)]),
                'description' => $this->getReportDescription(),
                'report_type' => $reportTypes[array_rand($reportTypes)],
                'period_type' => $periodTypes[array_rand($periodTypes)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'gross_margin' => $grossMargin,
                'operating_margin' => $operatingMargin,
                'financial_metrics' => [
                    'revenue_growth' => rand(-20, 50),
                    'expense_ratio' => $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0,
                    'profit_margin' => $grossMargin,
                    'return_on_revenue' => $operatingMargin,
                    'cash_flow_ratio' => rand(10, 200) / 100,
                    'debt_to_equity' => rand(10, 150) / 100,
                ],
                'chart_data' => [
                    'revenue_trend' => [
                        $totalRevenue * 0.8,
                        $totalRevenue * 0.9,
                        $totalRevenue
                    ],
                    'expense_trend' => [
                        $totalExpenses * 0.8,
                        $totalExpenses * 0.9,
                        $totalExpenses
                    ],
                    'profit_trend' => [
                        $netProfit * 0.8,
                        $netProfit * 0.9,
                        $netProfit
                    ],
                    'monthly_breakdown' => [
                        'jan' => rand(5000000, 50000000),
                        'feb' => rand(5000000, 50000000),
                        'mar' => rand(5000000, 50000000),
                        'apr' => rand(5000000, 50000000),
                        'may' => rand(5000000, 50000000),
                        'jun' => rand(5000000, 50000000),
                    ]
                ],
                'status' => $statuses[array_rand($statuses)],
                'created_by' => $users->random()->id,
                'company_id' => $companies->random()->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 29)),
            ]);
        }

        $this->command->info('Financial reports seeded successfully!');
    }

    private function getReportTitle($reportType): string
    {
        $titles = [
            'income_statement' => [
                'Monthly Income Statement',
                'Quarterly Income Statement',
                'Annual Income Statement',
                'Consolidated Income Statement',
                'Segment Income Statement'
            ],
            'balance_sheet' => [
                'Monthly Balance Sheet',
                'Quarterly Balance Sheet',
                'Annual Balance Sheet',
                'Consolidated Balance Sheet',
                'Comparative Balance Sheet'
            ],
            'cash_flow' => [
                'Monthly Cash Flow Statement',
                'Quarterly Cash Flow Statement',
                'Annual Cash Flow Statement',
                'Operating Cash Flow Analysis',
                'Free Cash Flow Report'
            ],
            'profit_loss' => [
                'Monthly Profit & Loss',
                'Quarterly Profit & Loss',
                'Annual Profit & Loss',
                'Department P&L Report',
                'Product Line P&L'
            ],
            'revenue_analysis' => [
                'Revenue Growth Analysis',
                'Revenue by Product Line',
                'Revenue by Region',
                'Revenue Trend Analysis',
                'Revenue Forecast Report'
            ],
            'expense_analysis' => [
                'Expense Breakdown Report',
                'Cost Center Analysis',
                'Expense Trend Analysis',
                'Budget vs Actual Expenses',
                'Operating Expense Report'
            ],
            'budget_variance' => [
                'Monthly Budget Variance',
                'Quarterly Budget Variance',
                'Annual Budget Variance',
                'Department Budget Variance',
                'Project Budget Variance'
            ],
            'custom' => [
                'Custom Financial Report',
                'Special Analysis Report',
                'Management Report',
                'Board Report',
                'Executive Summary'
            ]
        ];

        return $titles[$reportType][array_rand($titles[$reportType])];
    }

    private function getReportDescription(): string
    {
        $descriptions = [
            'Comprehensive financial analysis covering revenue, expenses, and profitability metrics.',
            'Detailed breakdown of financial performance with key insights and recommendations.',
            'Financial report providing insights into business performance and trends.',
            'Analysis of financial data to support strategic decision making.',
            'Comprehensive overview of financial position and operational results.',
            'Financial performance report with comparative analysis and forecasting.',
            'Detailed financial metrics and KPIs for business intelligence.',
            'Financial analysis report with actionable insights and recommendations.',
            'Comprehensive financial review with trend analysis and projections.',
            'Financial performance summary with key metrics and analysis.'
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
