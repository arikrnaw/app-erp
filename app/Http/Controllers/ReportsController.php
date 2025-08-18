<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FinancialReport;
use App\Models\BusinessAnalytics;
use App\Models\ReportSchedule;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        // Get statistics for the dashboard
        $stats = [
            'total_revenue' => FinancialReport::sum('total_revenue'),
            'total_expenses' => FinancialReport::sum('total_expenses'),
            'net_profit' => FinancialReport::sum('net_profit'),
            'profit_margin' => FinancialReport::avg('gross_margin'),
            'revenue_growth' => 12.5, // Mock data
            'expense_growth' => -8.2, // Mock data
            'total_customers' => 1250, // Mock data
            'total_orders' => 3450, // Mock data
            'average_order_value' => 2500000, // Mock data
            'customer_growth' => 8.5, // Mock data
            'top_products' => [
                ['name' => 'Product A', 'revenue' => 150000000, 'quantity' => 150],
                ['name' => 'Product B', 'revenue' => 120000000, 'quantity' => 120],
                ['name' => 'Product C', 'revenue' => 100000000, 'quantity' => 100],
                ['name' => 'Product D', 'revenue' => 80000000, 'quantity' => 80],
            ],
            'recent_reports' => FinancialReport::with('creator')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'title' => $report->title,
                        'type' => $report->report_type,
                        'status' => $report->status,
                        'created_at' => $report->created_at->toISOString(),
                    ];
                }),
            'upcoming_schedules' => ReportSchedule::where('is_active', true)
                ->where('next_generation_at', '>=', now())
                ->orderBy('next_generation_at')
                ->take(5)
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'name' => $schedule->name,
                        'frequency' => $schedule->frequency,
                        'next_generation' => $schedule->next_generation_at?->toISOString(),
                    ];
                }),
        ];

        return Inertia::render('Reports/Index', [
            'stats' => $stats,
        ]);
    }
}
