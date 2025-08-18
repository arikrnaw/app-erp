<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ReportSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportScheduleController extends Controller
{
    public function index()
    {
        $query = ReportSchedule::with(['creator', 'company'])
            ->where('company_id', Auth::user()->company_id ?? 1);

        // Apply filters
        if (request('search')) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                  ->orWhere('description', 'like', '%' . request('search') . '%')
                  ->orWhere('schedule_code', 'like', '%' . request('search') . '%');
            });
        }

        if (request('report_type')) {
            $query->where('report_type', request('report_type'));
        }

        if (request('frequency')) {
            $query->where('frequency', request('frequency'));
        }

        if (request('delivery_method')) {
            $query->where('delivery_method', request('delivery_method'));
        }

        if (request('is_active') !== null) {
            $query->where('is_active', request('is_active'));
        }

        $schedules = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get statistics
        $stats = [
            'total' => ReportSchedule::where('company_id', Auth::user()->company_id ?? 1)->count(),
            'active' => ReportSchedule::where('company_id', Auth::user()->company_id ?? 1)
                ->where('is_active', true)->count(),
            'next_generation' => ReportSchedule::where('company_id', Auth::user()->company_id ?? 1)
                ->where('next_generation_at', '>=', now())->count(),
            'email_delivery' => ReportSchedule::where('company_id', Auth::user()->company_id ?? 1)
                ->where('delivery_method', 'email')->count(),
        ];

        // Get upcoming generations
        $upcomingGenerations = ReportSchedule::where('company_id', Auth::user()->company_id ?? 1)
            ->where('is_active', true)
            ->where('next_generation_at', '>=', now())
            ->orderBy('next_generation_at', 'asc')
            ->limit(5)
            ->get();

        return Inertia::render('Reports/Schedules/Index', [
            'schedules' => $schedules,
            'stats' => $stats,
            'upcomingGenerations' => $upcomingGenerations,
            'filters' => request()->only(['search', 'report_type', 'frequency', 'delivery_method', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Reports/Schedules/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|in:financial_report,business_analytics',
            'report_template' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_of_month' => 'nullable|integer|between:1,31',
            'delivery_time' => 'required|date_format:H:i',
            'delivery_method' => 'required|in:email,dashboard,pdf,excel,api',
            'recipients' => 'nullable|array',
            'parameters' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['schedule_code'] = 'RS-' . str_pad(ReportSchedule::count() + 1, 6, '0', STR_PAD_LEFT);
        $validated['created_by'] = Auth::id();
        $validated['company_id'] = Auth::user()->company_id ?? 1;
        $validated['next_generation_at'] = $this->calculateNextGeneration($validated);

        ReportSchedule::create($validated);

        return redirect()->route('reports.schedules.index')->with('success', 'Report schedule created successfully.');
    }

    public function show(ReportSchedule $reportSchedule)
    {
        $reportSchedule->load(['creator', 'company']);

        return Inertia::render('Reports/Schedules/Show', [
            'schedule' => $reportSchedule,
        ]);
    }

    public function edit(ReportSchedule $reportSchedule)
    {
        return Inertia::render('Reports/Schedules/Edit', [
            'schedule' => $reportSchedule,
        ]);
    }

    public function update(Request $request, ReportSchedule $reportSchedule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|in:financial_report,business_analytics',
            'report_template' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_of_month' => 'nullable|integer|between:1,31',
            'delivery_time' => 'required|date_format:H:i',
            'delivery_method' => 'required|in:email,dashboard,pdf,excel,api',
            'recipients' => 'nullable|array',
            'parameters' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['next_generation_at'] = $this->calculateNextGeneration($validated);

        $reportSchedule->update($validated);

        return redirect()->route('reports.schedules.index')->with('success', 'Report schedule updated successfully.');
    }

    public function destroy(ReportSchedule $reportSchedule)
    {
        $reportSchedule->delete();

        return redirect()->route('reports.schedules.index')->with('success', 'Report schedule deleted successfully.');
    }

    private function calculateNextGeneration($data)
    {
        $now = now();
        $deliveryTime = $data['delivery_time'];
        
        switch ($data['frequency']) {
            case 'daily':
                return $now->addDay()->setTimeFromTimeString($deliveryTime);
                
            case 'weekly':
                $dayOfWeek = $data['day_of_week'] ?? 'monday';
                $nextDay = $now->copy()->next($dayOfWeek);
                return $nextDay->setTimeFromTimeString($deliveryTime);
                
            case 'monthly':
                $dayOfMonth = $data['day_of_month'] ?? 1;
                $nextMonth = $now->copy()->addMonth()->startOfMonth()->addDays($dayOfMonth - 1);
                return $nextMonth->setTimeFromTimeString($deliveryTime);
                
            case 'quarterly':
                $nextQuarter = $now->copy()->addMonths(3)->startOfQuarter();
                return $nextQuarter->setTimeFromTimeString($deliveryTime);
                
            case 'yearly':
                $nextYear = $now->copy()->addYear()->startOfYear();
                return $nextYear->setTimeFromTimeString($deliveryTime);
                
            default:
                return $now->addDay()->setTimeFromTimeString($deliveryTime);
        }
    }
}
