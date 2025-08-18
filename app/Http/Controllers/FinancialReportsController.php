<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FinancialReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FinancialReportsController extends Controller
{
    public function index()
    {
        $reports = FinancialReport::with('creator')
            ->latest()
            ->paginate(10);

        $statistics = [
            'total_reports' => FinancialReport::count(),
            'published_reports' => FinancialReport::where('status', 'published')->count(),
            'draft_reports' => FinancialReport::where('status', 'draft')->count(),
            'archived_reports' => FinancialReport::where('status', 'archived')->count(),
            'total_revenue' => FinancialReport::sum('total_revenue'),
            'total_expenses' => FinancialReport::sum('total_expenses'),
            'total_profit' => FinancialReport::sum('net_profit'),
            'avg_gross_margin' => FinancialReport::avg('gross_margin'),
            'reports_by_type' => FinancialReport::select('report_type', DB::raw('count(*) as count'))
                ->groupBy('report_type')
                ->get(),
            'reports_by_period' => FinancialReport::select('period_type', DB::raw('count(*) as count'))
                ->groupBy('period_type')
                ->get(),
        ];

        return Inertia::render('Reports/Financial/Index', [
            'reports' => $reports,
            'statistics' => $statistics,
        ]);
    }

    public function create()
    {
        return Inertia::render('Reports/Financial/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|string',
            'period_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_revenue' => 'required|numeric|min:0',
            'total_expenses' => 'required|numeric|min:0',
            'status' => 'required|string',
        ]);

        $validated['report_code'] = 'FR-' . str_pad(FinancialReport::count() + 1, 6, '0', STR_PAD_LEFT);
        $validated['net_profit'] = $validated['total_revenue'] - $validated['total_expenses'];
        $validated['gross_margin'] = $validated['total_revenue'] > 0 ? 
            (($validated['total_revenue'] - $validated['total_expenses']) / $validated['total_revenue']) * 100 : 0;
        $validated['operating_margin'] = $validated['total_revenue'] > 0 ? 
            ($validated['net_profit'] / $validated['total_revenue']) * 100 : 0;
        $validated['created_by'] = Auth::id();
        $validated['company_id'] = Auth::user()->company_id ?? 1;

        FinancialReport::create($validated);

        return redirect()->route('reports.financial.index')
            ->with('success', 'Financial report created successfully.');
    }

    public function show(FinancialReport $financialReport)
    {
        $financialReport->load(['creator', 'company']);
        
        return Inertia::render('Reports/Financial/Show', [
            'report' => $financialReport,
        ]);
    }

    public function edit(FinancialReport $financialReport)
    {
        return Inertia::render('Reports/Financial/Edit', [
            'report' => $financialReport,
        ]);
    }

    public function update(Request $request, FinancialReport $financialReport)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => 'required|string',
            'period_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_revenue' => 'required|numeric|min:0',
            'total_expenses' => 'required|numeric|min:0',
            'status' => 'required|string',
        ]);

        $validated['net_profit'] = $validated['total_revenue'] - $validated['total_expenses'];
        $validated['gross_margin'] = $validated['total_revenue'] > 0 ? 
            (($validated['total_revenue'] - $validated['total_expenses']) / $validated['total_revenue']) * 100 : 0;
        $validated['operating_margin'] = $validated['total_revenue'] > 0 ? 
            ($validated['net_profit'] / $validated['total_revenue']) * 100 : 0;

        $financialReport->update($validated);

        return redirect()->route('reports.financial.show', $financialReport)
            ->with('success', 'Financial report updated successfully.');
    }

    public function destroy(FinancialReport $financialReport)
    {
        $financialReport->delete();

        return redirect()->route('reports.financial.index')
            ->with('success', 'Financial report deleted successfully.');
    }
}
