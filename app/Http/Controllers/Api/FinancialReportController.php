<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FinancialReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FinancialReport::with(['creator', 'company'])
            ->where('company_id', auth()->user()->company_id);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('report_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $reports = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports,
            'message' => 'Financial reports retrieved successfully'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => ['required', Rule::in([
                'income_statement', 'balance_sheet', 'cash_flow', 'profit_loss',
                'revenue_analysis', 'expense_analysis', 'budget_variance', 'custom'
            ])],
            'period_type' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_revenue' => 'nullable|numeric|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
            'net_profit' => 'nullable|numeric',
            'gross_margin' => 'nullable|numeric|min:0|max:100',
            'operating_margin' => 'nullable|numeric|min:0|max:100',
            'financial_metrics' => 'nullable|array',
            'chart_data' => 'nullable|array',
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $report = FinancialReport::create([
                'report_code' => 'FR-' . date('Ymd') . '-' . str_pad(FinancialReport::count() + 1, 4, '0', STR_PAD_LEFT),
                'title' => $request->title,
                'description' => $request->description,
                'report_type' => $request->report_type,
                'period_type' => $request->period_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_revenue' => $request->total_revenue ?? 0,
                'total_expenses' => $request->total_expenses ?? 0,
                'net_profit' => $request->net_profit ?? 0,
                'gross_margin' => $request->gross_margin ?? 0,
                'operating_margin' => $request->operating_margin ?? 0,
                'financial_metrics' => $request->financial_metrics,
                'chart_data' => $request->chart_data,
                'status' => $request->status ?? 'draft',
                'created_by' => auth()->id(),
                'company_id' => auth()->user()->company_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $report->load(['creator', 'company']),
                'message' => 'Financial report created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create financial report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(FinancialReport $financialReport): JsonResponse
    {
        if ($financialReport->company_id !== auth()->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $financialReport->load(['creator', 'company']),
            'message' => 'Financial report retrieved successfully'
        ]);
    }

    public function update(Request $request, FinancialReport $financialReport): JsonResponse
    {
        if ($financialReport->company_id !== auth()->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'report_type' => ['sometimes', 'required', Rule::in([
                'income_statement', 'balance_sheet', 'cash_flow', 'profit_loss',
                'revenue_analysis', 'expense_analysis', 'budget_variance', 'custom'
            ])],
            'period_type' => ['sometimes', 'required', Rule::in(['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])],
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'total_revenue' => 'nullable|numeric|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
            'net_profit' => 'nullable|numeric',
            'gross_margin' => 'nullable|numeric|min:0|max:100',
            'operating_margin' => 'nullable|numeric|min:0|max:100',
            'financial_metrics' => 'nullable|array',
            'chart_data' => 'nullable|array',
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $financialReport->update($request->only([
                'title', 'description', 'report_type', 'period_type', 'start_date', 'end_date',
                'total_revenue', 'total_expenses', 'net_profit', 'gross_margin', 'operating_margin',
                'financial_metrics', 'chart_data', 'status'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $financialReport->load(['creator', 'company']),
                'message' => 'Financial report updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update financial report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(FinancialReport $financialReport): JsonResponse
    {
        if ($financialReport->company_id !== auth()->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $financialReport->delete();

            return response()->json([
                'success' => true,
                'message' => 'Financial report deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete financial report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'total_reports' => FinancialReport::where('company_id', $companyId)->count(),
            'published_reports' => FinancialReport::where('company_id', $companyId)->where('status', 'published')->count(),
            'draft_reports' => FinancialReport::where('company_id', $companyId)->where('status', 'draft')->count(),
            'archived_reports' => FinancialReport::where('company_id', $companyId)->where('status', 'archived')->count(),
            'total_revenue' => FinancialReport::where('company_id', $companyId)->sum('total_revenue'),
            'total_expenses' => FinancialReport::where('company_id', $companyId)->sum('total_expenses'),
            'total_profit' => FinancialReport::where('company_id', $companyId)->sum('net_profit'),
            'avg_gross_margin' => FinancialReport::where('company_id', $companyId)->avg('gross_margin'),
            'reports_by_type' => FinancialReport::where('company_id', $companyId)
                ->select('report_type', DB::raw('count(*) as count'))
                ->groupBy('report_type')
                ->get(),
            'reports_by_period' => FinancialReport::where('company_id', $companyId)
                ->select('period_type', DB::raw('count(*) as count'))
                ->groupBy('period_type')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }

    public function generateReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'report_type' => ['required', Rule::in([
                'income_statement', 'balance_sheet', 'cash_flow', 'profit_loss',
                'revenue_analysis', 'expense_analysis', 'budget_variance'
            ])],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Here you would implement the actual report generation logic
            // This is a placeholder that returns sample data
            $reportData = [
                'report_type' => $request->report_type,
                'period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
                'summary' => [
                    'total_revenue' => rand(100000, 1000000),
                    'total_expenses' => rand(50000, 800000),
                    'net_profit' => rand(20000, 500000),
                    'gross_margin' => rand(15, 45),
                    'operating_margin' => rand(8, 25),
                ],
                'details' => [
                    'revenue_breakdown' => [
                        'product_sales' => rand(50000, 300000),
                        'service_revenue' => rand(30000, 200000),
                        'other_income' => rand(5000, 50000),
                    ],
                    'expense_breakdown' => [
                        'cost_of_goods' => rand(20000, 150000),
                        'operating_expenses' => rand(15000, 100000),
                        'marketing_expenses' => rand(5000, 30000),
                        'administrative_expenses' => rand(10000, 50000),
                    ],
                ],
                'trends' => [
                    'revenue_trend' => [rand(80000, 120000), rand(90000, 130000), rand(100000, 140000)],
                    'expense_trend' => [rand(40000, 80000), rand(45000, 85000), rand(50000, 90000)],
                    'profit_trend' => [rand(15000, 40000), rand(20000, 45000), rand(25000, 50000)],
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $reportData,
                'message' => 'Report generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
