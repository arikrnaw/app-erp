<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetPeriod;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetVariance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BudgetingController extends Controller
{
    /**
     * Get budgeting dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $budgets = Budget::with('category')->get();
            $budgetPeriods = BudgetPeriod::with('budgets')->get();
            $budgetCategories = BudgetCategory::withCount('budgets')->get();
            $varianceAnalysis = BudgetVariance::with(['budget.category'])->get();

            $totalBudget = $budgets->sum('amount');
            $totalSpent = $budgets->sum('spent');
            $remainingBudget = $totalBudget - $totalSpent;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_budget' => $totalBudget,
                    'total_spent' => $totalSpent,
                    'remaining_budget' => $remainingBudget,
                    'budgets' => $budgets,
                    'budget_periods' => $budgetPeriods,
                    'budget_categories' => $budgetCategories,
                    'variance_analysis' => $varianceAnalysis
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all budgets with pagination
     */
    public function getBudgets(Request $request): JsonResponse
    {
        try {
            $query = Budget::with(['category', 'period']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('period_id')) {
                $query->where('period_id', $request->period_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $budgets = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $budgets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching budgets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new budget
     */
    public function createBudget(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'category_id' => 'required|exists:budget_categories,id',
                'period_id' => 'required|exists:budget_periods,id',
                'amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'type' => 'required|string|in:revenue,expense,capital',
                'status' => 'nullable|string|in:active,inactive,draft',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $budget = Budget::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Budget created successfully',
                'data' => $budget->load(['category', 'period'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating budget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget periods
     */
    public function getBudgetPeriods(): JsonResponse
    {
        try {
            $periods = BudgetPeriod::with(['budgets' => function ($query) {
                $query->with('category');
            }])->get();

            // Calculate progress for each period
            $periods->each(function ($period) {
                $totalBudget = $period->budgets->sum('amount');
                $totalSpent = $period->budgets->sum('spent');
                $period->progress = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;
                $period->total_budget = $totalBudget;
            });

            return response()->json([
                'success' => true,
                'data' => $periods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching budget periods: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create budget period
     */
    public function createBudgetPeriod(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'fiscal_year' => 'required|string|max:4',
                'status' => 'nullable|string|in:active,inactive,planning',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $period = BudgetPeriod::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Budget period created successfully',
                'data' => $period
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating budget period: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget categories
     */
    public function getBudgetCategories(): JsonResponse
    {
        try {
            $categories = BudgetCategory::withCount('budgets')->get();

            // Calculate totals for each category
            $categories->each(function ($category) {
                $category->total_budget = $category->budgets->sum('amount');
                $category->total_spent = $category->budgets->sum('spent');
                $category->utilization = $category->total_budget > 0 ? 
                    ($category->total_spent / $category->total_budget) * 100 : 0;
                
                if ($category->utilization > 100) {
                    $category->overage = $category->total_spent - $category->total_budget;
                }
            });

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching budget categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create budget category
     */
    public function createBudgetCategory(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:budget_categories',
                'description' => 'nullable|string|max:1000',
                'type' => 'required|string|in:revenue,expense,capital',
                'parent_id' => 'nullable|exists:budget_categories,id',
                'status' => 'nullable|string|in:active,inactive',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = BudgetCategory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Budget category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating budget category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get variance analysis
     */
    public function getVarianceAnalysis(Request $request): JsonResponse
    {
        try {
            $query = BudgetVariance::with(['budget.category']);

            if ($request->filled('period_id')) {
                $query->whereHas('budget', function ($q) use ($request) {
                    $q->where('period_id', $request->period_id);
                });
            }

            if ($request->filled('category_id')) {
                $query->whereHas('budget', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $variances = $query->get();

            return response()->json([
                'success' => true,
                'data' => $variances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching variance analysis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create variance analysis
     */
    public function createVarianceAnalysis(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'budget_id' => 'required|exists:budgets,id',
                'actual_amount' => 'required|numeric',
                'variance_date' => 'required|date',
                'variance_reason' => 'nullable|string|max:1000',
                'corrective_action' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $budget = Budget::findOrFail($request->budget_id);
            $variance = $request->actual_amount - $budget->amount;
            $variancePercentage = $budget->amount > 0 ? ($variance / $budget->amount) * 100 : 0;

            $varianceAnalysis = BudgetVariance::create([
                'budget_id' => $request->budget_id,
                'actual_amount' => $request->actual_amount,
                'variance' => $variance,
                'variance_percentage' => $variancePercentage,
                'variance_date' => $request->variance_date,
                'variance_reason' => $request->variance_reason,
                'corrective_action' => $request->corrective_action,
                'notes' => $request->notes
            ]);

            // Update budget spent amount
            $budget->increment('spent', $request->actual_amount);

            return response()->json([
                'success' => true,
                'message' => 'Variance analysis created successfully',
                'data' => $varianceAnalysis->load('budget.category')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating variance analysis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export budget report
     */
    public function exportBudgetReport(Request $request): JsonResponse
    {
        try {
            $periodId = $request->get('period_id');
            
            $query = Budget::with(['category', 'period', 'variances']);
            
            if ($periodId) {
                $query->where('period_id', $periodId);
            }

            $budgets = $query->get();

            return response()->json([
                'success' => true,
                'data' => $budgets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting budget report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial forecast
     */
    public function getFinancialForecast(Request $request): JsonResponse
    {
        try {
            $periods = BudgetPeriod::with(['budgets.category'])
                ->where('status', 'active')
                ->orderBy('start_date')
                ->get();

            $forecast = [];
            
            foreach ($periods as $period) {
                $revenue = $period->budgets->where('type', 'revenue')->sum('amount');
                $expenses = $period->budgets->where('type', 'expense')->sum('amount');
                $capital = $period->budgets->where('type', 'capital')->sum('amount');
                
                $forecast[] = [
                    'period' => $period->name,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                    'capital' => $capital,
                    'net_income' => $revenue - $expenses,
                    'cash_flow' => ($revenue - $expenses) - $capital
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $forecast
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating financial forecast: ' . $e->getMessage()
            ], 500);
        }
    }
}
