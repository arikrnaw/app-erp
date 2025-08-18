<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BusinessAnalytics;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BusinessAnalyticsController extends Controller
{
    public function index()
    {
        $query = BusinessAnalytics::with(['creator', 'company'])
            ->where('company_id', Auth::user()->company_id ?? 1);

        // Apply filters
        if (request('search')) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . request('search') . '%')
                  ->orWhere('description', 'like', '%' . request('search') . '%')
                  ->orWhere('analysis_code', 'like', '%' . request('search') . '%');
            });
        }

        if (request('analysis_type')) {
            $query->where('analysis_type', request('analysis_type'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('priority')) {
            $query->where('priority', request('priority'));
        }

        if (request('data_source')) {
            $query->where('data_source', request('data_source'));
        }

        if (request('date_from')) {
            $query->where('analysis_date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->where('analysis_date', '<=', request('date_to'));
        }

        $analytics = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get statistics
        $stats = [
            'total' => BusinessAnalytics::where('company_id', Auth::user()->company_id ?? 1)->count(),
            'published' => BusinessAnalytics::where('company_id', Auth::user()->company_id ?? 1)
                ->where('status', 'published')->count(),
            'draft' => BusinessAnalytics::where('company_id', Auth::user()->company_id ?? 1)
                ->where('status', 'draft')->count(),
            'high_priority' => BusinessAnalytics::where('company_id', Auth::user()->company_id ?? 1)
                ->whereIn('priority', ['high', 'critical'])->count(),
        ];

        return Inertia::render('Reports/Analytics/Index', [
            'analytics' => $analytics,
            'stats' => $stats,
            'filters' => request()->only(['search', 'analysis_type', 'status', 'priority', 'data_source', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Reports/Analytics/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'analysis_type' => 'required|in:sales_analysis,customer_analysis,product_analysis,market_analysis,performance_analysis,trend_analysis,forecasting,custom',
            'data_source' => 'required|in:sales_orders,customers,products,inventory,financial_reports,external_api,custom',
            'analysis_date' => 'required|date',
            'data_start_date' => 'nullable|date',
            'data_end_date' => 'nullable|date|after_or_equal:data_start_date',
            'key_metrics' => 'nullable|array',
            'insights' => 'nullable|array',
            'recommendations' => 'nullable|array',
            'visualization_data' => 'nullable|array',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:draft,published,archived',
        ]);

        $validated['analysis_code'] = 'BA-' . str_pad(BusinessAnalytics::count() + 1, 6, '0', STR_PAD_LEFT);
        $validated['created_by'] = Auth::id();
        $validated['company_id'] = Auth::user()->company_id ?? 1;

        BusinessAnalytics::create($validated);

        return redirect()->route('reports.analytics.index')->with('success', 'Business analytics created successfully.');
    }

    public function show(BusinessAnalytics $businessAnalytics)
    {
        $businessAnalytics->load(['creator', 'company']);

        return Inertia::render('Reports/Analytics/Show', [
            'analytics' => $businessAnalytics,
        ]);
    }

    public function edit(BusinessAnalytics $businessAnalytics)
    {
        return Inertia::render('Reports/Analytics/Edit', [
            'analytics' => $businessAnalytics,
        ]);
    }

    public function update(Request $request, BusinessAnalytics $businessAnalytics)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'analysis_type' => 'required|in:sales_analysis,customer_analysis,product_analysis,market_analysis,performance_analysis,trend_analysis,forecasting,custom',
            'data_source' => 'required|in:sales_orders,customers,products,inventory,financial_reports,external_api,custom',
            'analysis_date' => 'required|date',
            'data_start_date' => 'nullable|date',
            'data_end_date' => 'nullable|date|after_or_equal:data_start_date',
            'key_metrics' => 'nullable|array',
            'insights' => 'nullable|array',
            'recommendations' => 'nullable|array',
            'visualization_data' => 'nullable|array',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:draft,published,archived',
        ]);

        $businessAnalytics->update($validated);

        return redirect()->route('reports.analytics.index')->with('success', 'Business analytics updated successfully.');
    }

    public function destroy(BusinessAnalytics $businessAnalytics)
    {
        $businessAnalytics->delete();

        return redirect()->route('reports.analytics.index')->with('success', 'Business analytics deleted successfully.');
    }
}
