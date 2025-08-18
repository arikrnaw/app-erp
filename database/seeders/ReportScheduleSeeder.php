<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReportSchedule;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class ReportScheduleSeeder extends Seeder
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
            'financial_report',
            'business_analytics'
        ];

        $frequencies = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        $deliveryMethods = ['email', 'dashboard', 'pdf', 'excel', 'api'];
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        for ($i = 1; $i <= 10; $i++) {
            $frequency = $frequencies[array_rand($frequencies)];
            $isActive = rand(0, 1);
            $lastGeneratedAt = $isActive ? Carbon::now()->subDays(rand(1, 30)) : null;
            $nextGenerationAt = $isActive ? $this->calculateNextGeneration($frequency, $lastGeneratedAt) : null;

            ReportSchedule::create([
                'schedule_code' => 'RS-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'name' => $this->getScheduleName($reportTypes[array_rand($reportTypes)]),
                'description' => $this->getScheduleDescription(),
                'report_type' => $reportTypes[array_rand($reportTypes)],
                'report_template' => $this->getReportTemplate(),
                'frequency' => $frequency,
                'day_of_week' => $frequency === 'weekly' ? $daysOfWeek[array_rand($daysOfWeek)] : null,
                'day_of_month' => $frequency === 'monthly' ? rand(1, 28) : null,
                'delivery_time' => $this->getDeliveryTime(),
                'delivery_method' => $deliveryMethods[array_rand($deliveryMethods)],
                'recipients' => $this->getRecipients(),
                'parameters' => $this->getParameters(),
                'is_active' => $isActive,
                'last_generated_at' => $lastGeneratedAt,
                'next_generation_at' => $nextGenerationAt,
                'created_by' => $users->random()->id,
                'company_id' => $companies->random()->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 29)),
            ]);
        }

        $this->command->info('Report schedules seeded successfully!');
    }

    private function getScheduleName($reportType): string
    {
        $names = [
            'financial_report' => [
                'Daily Financial Summary',
                'Weekly Financial Report',
                'Monthly Financial Statement',
                'Quarterly Financial Review',
                'Annual Financial Report'
            ],
            'business_analytics' => [
                'Daily Analytics Dashboard',
                'Weekly Business Analytics',
                'Monthly Performance Analytics',
                'Quarterly Business Intelligence',
                'Annual Analytics Report'
            ]
        ];

        return $names[$reportType][array_rand($names[$reportType])];
    }

    private function getScheduleDescription(): string
    {
        $descriptions = [
            'Automated report generation for regular business monitoring and analysis.',
            'Scheduled report delivery to support decision-making processes.',
            'Regular report generation to track key performance indicators.',
            'Automated business intelligence report for strategic planning.',
            'Scheduled analytics report for operational monitoring.',
            'Regular financial reporting for compliance and analysis.',
            'Automated performance metrics report for business review.',
            'Scheduled operational report for process monitoring.',
            'Regular custom report generation for specific business needs.',
            'Automated dashboard report for executive overview.'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getReportTemplate(): string
    {
        $templates = [
            'financial_summary_template',
            'business_analytics_template',
            'operational_report_template',
            'executive_summary_template',
            'performance_metrics_template',
            'custom_analytics_template',
            'monthly_review_template',
            'quarterly_report_template',
            'annual_summary_template',
            'dashboard_report_template'
        ];

        return $templates[array_rand($templates)];
    }

    private function getDeliveryTime(): string
    {
        $times = [
            '09:00:00',
            '10:00:00',
            '11:00:00',
            '14:00:00',
            '15:00:00',
            '16:00:00',
            '17:00:00',
            '18:00:00'
        ];

        return $times[array_rand($times)];
    }

    private function getRecipients(): array
    {
        $recipients = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@company.com',
                'role' => 'CEO'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@company.com',
                'role' => 'CFO'
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@company.com',
                'role' => 'COO'
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@company.com',
                'role' => 'CTO'
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@company.com',
                'role' => 'Manager'
            ]
        ];

        // Return random number of recipients (1-3)
        return array_slice($recipients, 0, rand(1, 3));
    }

    private function getParameters(): array
    {
        return [
            'include_charts' => rand(0, 1),
            'include_summary' => rand(0, 1),
            'include_details' => rand(0, 1),
            'format' => ['pdf', 'excel', 'html'][array_rand(['pdf', 'excel', 'html'])],
            'timezone' => 'Asia/Jakarta',
            'language' => 'en',
            'currency' => 'IDR',
            'date_format' => 'Y-m-d',
            'include_attachments' => rand(0, 1),
            'compression' => rand(0, 1)
        ];
    }

    private function calculateNextGeneration($frequency, $lastGeneratedAt): ?string
    {
        if (!$lastGeneratedAt) {
            return null;
        }

        $lastDate = Carbon::parse($lastGeneratedAt);

        switch ($frequency) {
            case 'daily':
                return $lastDate->addDay()->format('Y-m-d H:i:s');
            case 'weekly':
                return $lastDate->addWeek()->format('Y-m-d H:i:s');
            case 'monthly':
                return $lastDate->addMonth()->format('Y-m-d H:i:s');
            case 'quarterly':
                return $lastDate->addMonths(3)->format('Y-m-d H:i:s');
            case 'yearly':
                return $lastDate->addYear()->format('Y-m-d H:i:s');
            default:
                return null;
        }
    }
}
