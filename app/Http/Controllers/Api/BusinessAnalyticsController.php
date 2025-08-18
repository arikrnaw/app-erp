<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessAnalytics;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BusinessAnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusinessAnalytics::with(['creator', 'company'])
            ->where('company_id', auth()->user()->company_id);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('analysis_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('analysis_type')) {
            $query->where('analysis_type', $request->analysis_type);
        }

        if ($request->filled('data_source')) {
            $query->where('data_source', $request->data_source);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('analysis_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('analysis_date', '<=', $request->date_to);
        }

        $analytics = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Business analytics retrieved successfully'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'analysis_type' => ['required', Rule::in([
                'sales_analysis', 'customer_analysis', 'product_analysis', 'market_analysis',
                'performance_analysis', 'trend_analysis', 'forecasting', 'custom'
            ])],
            'data_source' => ['required', Rule::in([
                'sales_orders', 'customers', 'products', 'inventory',
                'financial_reports', 'external_api', 'custom'
            ])],
            'analysis_date' => 'required|date',
            'data_start_date' => 'required|date',
            'data_end_date' => 'required|date|after_or_equal:data_start_date',
            'key_metrics' => 'nullable|array',
            'insights' => 'nullable|array',
            'recommendations' => 'nullable|array',
            'visualization_data' => 'nullable|array',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
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

            $analytics = BusinessAnalytics::create([
                'analysis_code' => 'BA-' . date('Ymd') . '-' . str_pad(BusinessAnalytics::count() + 1, 4, '0', STR_PAD_LEFT),
                'title' => $request->title,
                'description' => $request->description,
                'analysis_type' => $request->analysis_type,
                'data_source' => $request->data_source,
                'analysis_date' => $request->analysis_date,
                'data_start_date' => $request->data_start_date,
                'data_end_date' => $request->data_end_date,
                'key_metrics' => $request->key_metrics,
                'insights' => $request->insights,
                'recommendations' => $request->recommendations,
                'visualization_data' => $request->visualization_data,
                'priority' => $request->priority ?? 'medium',
                'status' => $request->status ?? 'draft',
                'created_by' => auth()->id(),
                'company_id' => auth()->user()->company_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $analytics->load(['creator', 'company']),
                'message' => 'Business analytics created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create business analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(BusinessAnalytics $businessAnalytics): JsonResponse
    {
        if ($businessAnalytics->company_id !== auth()->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $businessAnalytics->load(['creator', 'company']),
            'message' => 'Business analytics retrieved successfully'
        ]);
    }

    public function update(Request $request, BusinessAnalytics $businessAnalytics): JsonResponse
    {
        if ($businessAnalytics->company_id !== auth()->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'analysis_type' => ['sometimes', 'required', Rule::in([
                'sales_analysis', 'customer_analysis', 'product_analysis', 'market_analysis',
                'performance_analysis', 'trend_analysis', 'forecasting', 'custom'
            ])],
            'data_source' => ['sometimes', 'required', Rule::in([
                'sales_orders', 'customers', 'products', 'inventory',
                'financial_reports', 'external_api', 'custom'
            ])],
            'analysis_date' => 'sometimes|required|date',
            'data_start_date' => 'sometimes|required|date',
            'data_end_date' => 'sometimes|required|date|after_or_equal:data_start_date',
            'key_metrics' => 'nullable|array',
            'insights' => 'nullable|array',
            'recommendations' => 'nullable|array',
            'visualization_data' => 'nullable|array',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
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

            $businessAnalytics->update($request->only([
                'title', 'description', 'analysis_type', 'data_source', 'analysis_date',
                'data_start_date', 'data_end_date', 'key_metrics', 'insights',
                'recommendations', 'visualization_data', 'priority', 'status'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $businessAnalytics->load(['creator', 'company']),
                'message' => 'Business analytics updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update business analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(BusinessAnalytics $businessAnalytics): JsonResponse
    {
        if ($businessAnalytics->company_id !== auth()->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $businessAnalytics->delete();

            return response()->json([
                'success' => true,
                'message' => 'Business analytics deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete business analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'total_analytics' => BusinessAnalytics::where('company_id', $companyId)->count(),
            'published_analytics' => BusinessAnalytics::where('company_id', $companyId)->where('status', 'published')->count(),
            'draft_analytics' => BusinessAnalytics::where('company_id', $companyId)->where('status', 'draft')->count(),
            'archived_analytics' => BusinessAnalytics::where('company_id', $companyId)->where('status', 'archived')->count(),
            'critical_priority' => BusinessAnalytics::where('company_id', $companyId)->where('priority', 'critical')->count(),
            'high_priority' => BusinessAnalytics::where('company_id', $companyId)->where('priority', 'high')->count(),
            'analytics_by_type' => BusinessAnalytics::where('company_id', $companyId)
                ->select('analysis_type', DB::raw('count(*) as count'))
                ->groupBy('analysis_type')
                ->get(),
            'analytics_by_source' => BusinessAnalytics::where('company_id', $companyId)
                ->select('data_source', DB::raw('count(*) as count'))
                ->groupBy('data_source')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }

    public function generateAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'analysis_type' => ['required', Rule::in([
                'sales_analysis', 'customer_analysis', 'product_analysis', 'market_analysis',
                'performance_analysis', 'trend_analysis', 'forecasting'
            ])],
            'data_source' => ['required', Rule::in([
                'sales_orders', 'customers', 'products', 'inventory', 'financial_reports'
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
            // Here you would implement the actual analysis generation logic
            // This is a placeholder that returns sample data
            $analysisData = [
                'analysis_type' => $request->analysis_type,
                'data_source' => $request->data_source,
                'period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
                'key_metrics' => [
                    'total_sales' => rand(50000, 500000),
                    'total_orders' => rand(100, 1000),
                    'average_order_value' => rand(200, 2000),
                    'customer_count' => rand(50, 500),
                    'conversion_rate' => rand(2, 15),
                    'repeat_customer_rate' => rand(20, 60),
                ],
                'insights' => [
                    'top_performing_product' => 'Product A',
                    'best_selling_category' => 'Electronics',
                    'peak_sales_period' => 'Q4',
                    'customer_segment_trend' => 'Growing',
                    'market_opportunity' => 'Expansion in Asia',
                ],
                'recommendations' => [
                    'Increase marketing budget for Q4',
                    'Focus on customer retention programs',
                    'Expand product line in Electronics category',
                    'Consider entering Asian markets',
                    'Optimize pricing strategy for better margins',
                ],
                'trends' => [
                    'sales_trend' => [rand(40000, 60000), rand(45000, 65000), rand(50000, 70000)],
                    'customer_growth' => [rand(40, 60), rand(45, 65), rand(50, 70)],
                    'revenue_trend' => [rand(80000, 120000), rand(90000, 130000), rand(100000, 140000)],
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $analysisData,
                'message' => 'Analysis generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
