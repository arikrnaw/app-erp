<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessAnalytics;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class BusinessAnalyticsSeeder extends Seeder
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

        $analysisTypes = [
            'sales_analysis',
            'customer_analysis',
            'product_analysis',
            'market_analysis',
            'performance_analysis',
            'trend_analysis',
            'forecasting',
            'custom'
        ];

        $dataSources = [
            'sales_orders',
            'customers',
            'products',
            'inventory',
            'financial_reports',
            'external_api',
            'custom'
        ];

        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['draft', 'published', 'archived'];

        for ($i = 1; $i <= 15; $i++) {
            $analysisDate = Carbon::now()->subDays(rand(1, 90));
            $dataStartDate = $analysisDate->copy()->subDays(rand(30, 365));
            $dataEndDate = $analysisDate->copy()->subDays(rand(1, 29));

            BusinessAnalytics::create([
                'analysis_code' => 'BA-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'title' => $this->getAnalysisTitle($analysisTypes[array_rand($analysisTypes)]),
                'description' => $this->getAnalysisDescription(),
                'analysis_type' => $analysisTypes[array_rand($analysisTypes)],
                'data_source' => $dataSources[array_rand($dataSources)],
                'analysis_date' => $analysisDate->format('Y-m-d'),
                'data_start_date' => $dataStartDate->format('Y-m-d'),
                'data_end_date' => $dataEndDate->format('Y-m-d'),
                'key_metrics' => $this->getKeyMetrics(),
                'insights' => $this->getInsights(),
                'recommendations' => $this->getRecommendations(),
                'visualization_data' => $this->getVisualizationData(),
                'priority' => $priorities[array_rand($priorities)],
                'status' => $statuses[array_rand($statuses)],
                'created_by' => $users->random()->id,
                'company_id' => $companies->random()->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 29)),
            ]);
        }

        $this->command->info('Business analytics seeded successfully!');
    }

    private function getAnalysisTitle($analysisType): string
    {
        $titles = [
            'sales_analysis' => [
                'Sales Performance Analysis',
                'Sales Trend Analysis',
                'Sales Forecast Report',
                'Sales Channel Analysis',
                'Product Sales Analysis'
            ],
            'customer_analysis' => [
                'Customer Behavior Analysis',
                'Customer Segmentation Report',
                'Customer Lifetime Value Analysis',
                'Customer Journey Analysis',
                'Customer Satisfaction Analysis'
            ],
            'product_analysis' => [
                'Product Performance Analysis',
                'Product Trend Analysis',
                'Product Sales Report',
                'Product Category Analysis',
                'Product Profitability Analysis'
            ],
            'market_analysis' => [
                'Market Trend Analysis',
                'Industry Trend Report',
                'Market Opportunity Analysis',
                'Competitive Market Analysis',
                'Market Forecast Report'
            ],
            'performance_analysis' => [
                'KPI Performance Analysis',
                'Business Performance Report',
                'Operational Metrics Analysis',
                'Performance Benchmarking',
                'Performance Trend Analysis'
            ],
            'trend_analysis' => [
                'Business Trend Analysis',
                'Market Trend Report',
                'Sales Trend Analysis',
                'Customer Trend Analysis',
                'Industry Trend Analysis'
            ],
            'forecasting' => [
                'Sales Forecasting Model',
                'Revenue Forecasting',
                'Demand Forecasting',
                'Market Forecasting',
                'Financial Forecasting'
            ],
            'custom' => [
                'Custom Business Analysis',
                'Special Analytics Report',
                'Strategic Analysis Report',
                'Business Intelligence Report',
                'Executive Analytics Summary'
            ]
        ];

        return $titles[$analysisType][array_rand($titles[$analysisType])];
    }

    private function getAnalysisDescription(): string
    {
        $descriptions = [
            'Comprehensive analysis of business data to identify trends and opportunities.',
            'Deep dive into business metrics to support strategic decision making.',
            'Analysis of key performance indicators and business intelligence insights.',
            'Data-driven analysis providing actionable insights for business growth.',
            'Comprehensive business analytics report with predictive modeling.',
            'Strategic analysis of business performance and market positioning.',
            'Business intelligence report with key insights and recommendations.',
            'Analytics report focusing on operational efficiency and optimization.',
            'Comprehensive market and competitive analysis for strategic planning.',
            'Business performance analysis with trend identification and forecasting.'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getKeyMetrics(): array
    {
        return [
            'conversion_rate' => rand(5, 25) / 100,
            'customer_acquisition_cost' => rand(50000, 500000),
            'customer_lifetime_value' => rand(1000000, 10000000),
            'revenue_growth_rate' => rand(-10, 50) / 100,
            'market_share' => rand(5, 30) / 100,
            'customer_satisfaction_score' => rand(70, 95),
            'operational_efficiency' => rand(60, 90) / 100,
            'profit_margin' => rand(10, 40) / 100,
            'employee_productivity' => rand(70, 95) / 100,
            'inventory_turnover' => rand(3, 12)
        ];
    }

    private function getInsights(): array
    {
        return [
            'Sales performance shows strong growth in Q3 with 15% increase compared to Q2.',
            'Customer retention rate improved by 8% following implementation of new loyalty program.',
            'Market analysis indicates growing demand in the technology sector.',
            'Operational efficiency metrics show 12% improvement in process optimization.',
            'Customer behavior analysis reveals increased preference for online channels.',
            'Competitive analysis shows strong positioning in the premium segment.',
            'Predictive models indicate 20% revenue growth potential in next quarter.',
            'Key performance indicators demonstrate consistent improvement across all metrics.',
            'Market trends suggest expansion opportunities in emerging markets.',
            'Business intelligence data supports strategic investment in digital transformation.'
        ];
    }

    private function getRecommendations(): array
    {
        return [
            'Increase investment in digital marketing channels to capitalize on online growth.',
            'Implement customer feedback system to improve satisfaction scores.',
            'Expand product portfolio to address identified market opportunities.',
            'Optimize operational processes to improve efficiency metrics.',
            'Develop targeted marketing campaigns based on customer segmentation.',
            'Strengthen competitive positioning through product differentiation.',
            'Invest in predictive analytics tools for better forecasting accuracy.',
            'Enhance employee training programs to improve productivity metrics.',
            'Explore strategic partnerships to expand market reach.',
            'Implement data-driven decision making processes across all departments.'
        ];
    }

    private function getVisualizationData(): array
    {
        return [
            'sales_trend' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [
                    rand(1000000, 5000000),
                    rand(1000000, 5000000),
                    rand(1000000, 5000000),
                    rand(1000000, 5000000),
                    rand(1000000, 5000000),
                    rand(1000000, 5000000)
                ]
            ],
            'customer_segments' => [
                'labels' => ['Premium', 'Standard', 'Basic'],
                'data' => [
                    rand(20, 40),
                    rand(30, 50),
                    rand(20, 40)
                ]
            ],
            'performance_metrics' => [
                'labels' => ['Sales', 'Marketing', 'Operations', 'Finance'],
                'data' => [
                    rand(70, 95),
                    rand(70, 95),
                    rand(70, 95),
                    rand(70, 95)
                ]
            ],
            'market_share' => [
                'labels' => ['Our Company', 'Competitor A', 'Competitor B', 'Others'],
                'data' => [
                    rand(15, 35),
                    rand(10, 25),
                    rand(10, 25),
                    rand(20, 50)
                ]
            ]
        ];
    }
}
