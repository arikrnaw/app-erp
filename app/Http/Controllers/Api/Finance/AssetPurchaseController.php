<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\AssetPurchase;
use App\Models\Finance\ApprovalWorkflow;
use App\Models\Finance\ApprovalRequest;
use App\Models\Finance\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetPurchaseController extends Controller
{
    /**
     * Get asset purchases with approval status
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AssetPurchase::with(['category', 'department', 'supplier', 'approvalRequest'])
                ->when($request->has('status'), function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->has('category_id'), function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                })
                ->when($request->has('department_id'), function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                })
                ->when($request->has('supplier_id'), function ($q) use ($request) {
                    $q->where('supplier_id', $request->supplier_id);
                })
                ->when($request->has('date_from'), function ($q) use ($request) {
                    $q->where('purchase_date', '>=', $request->date_from);
                })
                ->when($request->has('date_to'), function ($q) use ($request) {
                    $q->where('purchase_date', '<=', $request->date_to);
                });

            $assetPurchases = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $assetPurchases
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading asset purchases: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create asset purchase with approval workflow and budget checking
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:asset_categories,id',
                'department_id' => 'required|exists:departments,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'asset_name' => 'required|string|max:255',
                'asset_code' => 'required|string|max:100|unique:asset_purchases,asset_code',
                'purchase_date' => 'required|date',
                'description' => 'required|string|max:500',
                'purchase_cost' => 'required|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'installation_cost' => 'nullable|numeric|min:0',
                'total_cost' => 'required|numeric|min:0',
                'expected_life_years' => 'required|integer|min:1',
                'depreciation_method' => 'required|string|in:straight_line,declining_balance,sum_of_years',
                'salvage_value' => 'nullable|numeric|min:0',
                'warranty_period_months' => 'nullable|integer|min:0',
                'maintenance_required' => 'boolean',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'is_capital_expenditure' => 'boolean',
            ]);

            DB::beginTransaction();

            // Check budget availability for capital expenditure
            if ($validated['is_capital_expenditure'] ?? false) {
                $budgetCheck = $this->checkCapitalBudgetAvailability(
                    $validated['department_id'],
                    $validated['total_cost'],
                    $validated['purchase_date']
                );

                if (!$budgetCheck['available']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Capital budget exceeded: ' . $budgetCheck['message'],
                        'budget_info' => $budgetCheck
                    ], 400);
                }
            }

            // Create asset purchase
            $assetPurchase = AssetPurchase::create([
                'category_id' => $validated['category_id'],
                'department_id' => $validated['department_id'],
                'supplier_id' => $validated['supplier_id'],
                'asset_name' => $validated['asset_name'],
                'asset_code' => $validated['asset_code'],
                'purchase_date' => $validated['purchase_date'],
                'description' => $validated['description'],
                'purchase_cost' => $validated['purchase_cost'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'installation_cost' => $validated['installation_cost'] ?? 0,
                'total_cost' => $validated['total_cost'],
                'expected_life_years' => $validated['expected_life_years'],
                'depreciation_method' => $validated['depreciation_method'],
                'salvage_value' => $validated['salvage_value'] ?? 0,
                'warranty_period_months' => $validated['warranty_period_months'],
                'maintenance_required' => $validated['maintenance_required'] ?? false,
                'location' => $validated['location'],
                'notes' => $validated['notes'],
                'is_capital_expenditure' => $validated['is_capital_expenditure'] ?? true,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Check if approval is required
            $this->checkApprovalRequired($assetPurchase, $validated['total_cost']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset purchase created successfully',
                'data' => $assetPurchase->load(['category', 'department', 'supplier', 'approvalRequest']),
                'budget_info' => $budgetCheck ?? null
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating asset purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update asset purchase
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $assetPurchase = AssetPurchase::findOrFail($id);

            // Check if asset purchase can be updated
            if ($assetPurchase->approvalRequest && $assetPurchase->approvalRequest->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update asset purchase while approval is pending'
                ], 400);
            }

            $validated = $request->validate([
                'category_id' => 'sometimes|exists:asset_categories,id',
                'department_id' => 'sometimes|exists:departments,id',
                'supplier_id' => 'sometimes|exists:suppliers,id',
                'asset_name' => 'sometimes|string|max:255',
                'purchase_date' => 'sometimes|date',
                'description' => 'sometimes|string|max:500',
                'purchase_cost' => 'sometimes|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'installation_cost' => 'nullable|numeric|min:0',
                'expected_life_years' => 'sometimes|integer|min:1',
                'depreciation_method' => 'sometimes|string|in:straight_line,declining_balance,sum_of_years',
                'salvage_value' => 'nullable|numeric|min:0',
                'warranty_period_months' => 'nullable|integer|min:0',
                'maintenance_required' => 'boolean',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $assetPurchase->update($validated);

            // Recalculate total if costs changed
            if ($request->has('purchase_cost') || $request->has('tax_amount') || 
                $request->has('shipping_cost') || $request->has('installation_cost')) {
                $totalCost = ($validated['purchase_cost'] ?? $assetPurchase->purchase_cost) +
                            ($validated['tax_amount'] ?? $assetPurchase->tax_amount) +
                            ($validated['shipping_cost'] ?? $assetPurchase->shipping_cost) +
                            ($validated['installation_cost'] ?? $assetPurchase->installation_cost);
                
                $assetPurchase->update(['total_cost' => $totalCost]);
            }

            // Check if approval is still required
            $this->checkApprovalRequired($assetPurchase, $assetPurchase->total_cost);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset purchase updated successfully',
                'data' => $assetPurchase->load(['category', 'department', 'supplier', 'approvalRequest'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating asset purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit asset purchase for approval
     */
    public function submitForApproval(int $id): JsonResponse
    {
        try {
            $assetPurchase = AssetPurchase::findOrFail($id);

            // Check if already submitted
            if ($assetPurchase->approvalRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asset purchase already submitted for approval'
                ], 400);
            }

            // Check if approval is required
            $workflow = $this->getApprovalWorkflow($assetPurchase->total_cost);
            if (!$workflow) {
                // Auto-approve if no workflow found
                $assetPurchase->update(['status' => 'approved']);
                return response()->json([
                    'success' => true,
                    'message' => 'Asset purchase auto-approved',
                    'data' => $assetPurchase
                ]);
            }

            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => AssetPurchase::class,
                'approvable_id' => $assetPurchase->id,
                'amount' => $assetPurchase->total_cost,
                'description' => "Asset Purchase: {$assetPurchase->asset_name} - {$assetPurchase->category->name}",
                'priority' => $this->determinePriority($assetPurchase->total_cost),
                'due_date' => now()->addDays(5),
                'status' => 'pending',
            ]);

            // Assign first level approver
            $firstLevel = $workflow->levels()->orderBy('level')->first();
            if ($firstLevel) {
                $approvalRequest->update([
                    'approver_id' => $firstLevel->approver_id,
                    'current_level' => $firstLevel->level,
                ]);
            }

            // Update asset purchase status
            $assetPurchase->update(['status' => 'pending_approval']);

            return response()->json([
                'success' => true,
                'message' => 'Asset purchase submitted for approval',
                'data' => $assetPurchase->load(['category', 'department', 'supplier', 'approvalRequest'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting for approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get asset purchase with approval details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $assetPurchase = AssetPurchase::with([
                'category', 
                'department', 
                'supplier',
                'approvalRequest.workflow',
                'approvalRequest.approver',
                'approvalRequest.requestor'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $assetPurchase
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading asset purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel asset purchase
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $assetPurchase = AssetPurchase::findOrFail($id);

            // Check if can be cancelled
            if (!in_array($assetPurchase->status, ['draft', 'pending_approval'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asset purchase cannot be cancelled in current status'
                ], 400);
            }

            DB::beginTransaction();

            // Cancel approval request if exists
            if ($assetPurchase->approvalRequest) {
                $assetPurchase->approvalRequest->update([
                    'status' => 'cancelled',
                    'approver_comments' => 'Cancelled by requestor'
                ]);
            }

            // Update asset purchase status
            $assetPurchase->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset purchase cancelled successfully',
                'data' => $assetPurchase
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling asset purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get asset purchases pending approval
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        try {
            $query = AssetPurchase::with(['category', 'department', 'supplier', 'approvalRequest.workflow'])
                ->whereHas('approvalRequest', function ($q) {
                    $q->where('status', 'pending');
                });

            $assetPurchases = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $assetPurchases
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading pending approvals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get asset purchase analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfYear());
            $dateTo = $request->get('date_to', now()->endOfYear());

            $analytics = [
                'total_purchases' => AssetPurchase::whereBetween('purchase_date', [$dateFrom, $dateTo])->sum('total_cost'),
                'pending_approval' => AssetPurchase::whereHas('approvalRequest', function ($q) {
                    $q->where('status', 'pending');
                })->sum('total_cost'),
                'approved_purchases' => AssetPurchase::where('status', 'approved')
                    ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                    ->sum('total_cost'),
                'by_category' => AssetPurchase::with('category')
                    ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                    ->selectRaw('category_id, SUM(total_cost) as total, COUNT(*) as count')
                    ->groupBy('category_id')
                    ->get(),
                'by_department' => AssetPurchase::with('department')
                    ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                    ->selectRaw('department_id, SUM(total_cost) as total, COUNT(*) as count')
                    ->groupBy('department_id')
                    ->get(),
                'by_supplier' => AssetPurchase::with('supplier')
                    ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                    ->selectRaw('supplier_id, SUM(total_cost) as total, COUNT(*) as count')
                    ->groupBy('supplier_id')
                    ->get(),
                'depreciation_impact' => $this->getDepreciationImpact($dateFrom, $dateTo),
                'capital_expenditure' => AssetPurchase::where('is_capital_expenditure', true)
                    ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                    ->where('status', 'approved')
                    ->sum('total_cost'),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check capital budget availability
     */
    private function checkCapitalBudgetAvailability(int $departmentId, float $amount, string $purchaseDate): array
    {
        $purchaseDate = \Carbon\Carbon::parse($purchaseDate);
        $year = $purchaseDate->year;

        // Get capital budget for this department and year
        $budget = Budget::where('department_id', $departmentId)
            ->where('year', $year)
            ->where('type', 'capital')
            ->first();

        if (!$budget) {
            return [
                'available' => true,
                'message' => 'No capital budget set for this department and year',
                'budget_amount' => 0,
                'spent_amount' => 0,
                'remaining_amount' => 0,
            ];
        }

        // Get total spent on capital assets for this department and year
        $spentAmount = AssetPurchase::where('department_id', $departmentId)
            ->whereYear('purchase_date', $year)
            ->where('status', 'approved')
            ->where('is_capital_expenditure', true)
            ->sum('total_cost');

        $remainingAmount = $budget->amount - $spentAmount;
        $available = $remainingAmount >= $amount;

        return [
            'available' => $available,
            'message' => $available ? 'Capital budget available' : 'Capital budget exceeded',
            'budget_amount' => $budget->amount,
            'spent_amount' => $spentAmount,
            'remaining_amount' => $remainingAmount,
            'requested_amount' => $amount,
        ];
    }

    /**
     * Get depreciation impact analysis
     */
    private function getDepreciationImpact(string $dateFrom, string $dateTo): array
    {
        $assetPurchases = AssetPurchase::whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->where('status', 'approved')
            ->with(['category', 'department'])
            ->get();

        $depreciationImpact = [];
        foreach ($assetPurchases as $asset) {
            $annualDepreciation = $this->calculateAnnualDepreciation(
                $asset->total_cost,
                $asset->salvage_value,
                $asset->expected_life_years,
                $asset->depreciation_method
            );

            $depreciationImpact[] = [
                'asset_name' => $asset->asset_name,
                'category' => $asset->category->name,
                'department' => $asset->department->name,
                'total_cost' => $asset->total_cost,
                'annual_depreciation' => $annualDepreciation,
                'monthly_depreciation' => $annualDepreciation / 12,
                'depreciation_method' => $asset->depreciation_method,
            ];
        }

        return $depreciationImpact;
    }

    /**
     * Calculate annual depreciation
     */
    private function calculateAnnualDepreciation(float $cost, float $salvageValue, int $lifeYears, string $method): float
    {
        $depreciableAmount = $cost - $salvageValue;

        return match($method) {
            'straight_line' => $depreciableAmount / $lifeYears,
            'declining_balance' => $depreciableAmount * (2 / $lifeYears), // Double declining balance
            'sum_of_years' => $depreciableAmount * ($lifeYears / (($lifeYears * ($lifeYears + 1)) / 2)),
            default => $depreciableAmount / $lifeYears,
        };
    }

    /**
     * Check if approval is required and create approval request
     */
    private function checkApprovalRequired(AssetPurchase $assetPurchase, float $totalCost): void
    {
        $workflow = $this->getApprovalWorkflow($totalCost);
        
        if ($workflow) {
            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => AssetPurchase::class,
                'approvable_id' => $assetPurchase->id,
                'amount' => $totalCost,
                'description' => "Asset Purchase: {$assetPurchase->asset_name} - {$assetPurchase->category->name}",
                'priority' => $this->determinePriority($totalCost),
                'due_date' => now()->addDays(5),
                'status' => 'pending',
            ]);

            // Assign first level approver
            $firstLevel = $workflow->levels()->orderBy('level')->first();
            if ($firstLevel) {
                $approvalRequest->update([
                    'approver_id' => $firstLevel->approver_id,
                    'current_level' => $firstLevel->level,
                ]);
            }

            // Update asset purchase status
            $assetPurchase->update(['status' => 'pending_approval']);
        } else {
            // Auto-approve if no workflow found
            $assetPurchase->update(['status' => 'approved']);
        }
    }

    /**
     * Get appropriate approval workflow based on amount
     */
    private function getApprovalWorkflow(float $amount): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::active()
            ->byType('asset_purchase')
            ->byThreshold($amount)
            ->first();
    }

    /**
     * Determine priority based on amount
     */
    private function determinePriority(float $amount): string
    {
        if ($amount >= 100000) return 'urgent';
        if ($amount >= 50000) return 'high';
        if ($amount >= 25000) return 'medium';
        return 'low';
    }
}
