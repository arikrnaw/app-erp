<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductionPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductionPlan::with(['product', 'billOfMaterial', 'warehouse', 'createdBy', 'approvedBy'])
            ->where('company_id', auth()->user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plan_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $plans = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json($plans);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'planned_quantity' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,approved,in_progress,completed,cancelled',
            'estimated_cost' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $plan = ProductionPlan::create([
                'company_id' => auth()->user()->company_id,
                'plan_number' => (new ProductionPlan())->generatePlanNumber(),
                'name' => $request->name,
                'description' => $request->description,
                'product_id' => $request->product_id,
                'bill_of_material_id' => $request->bill_of_material_id,
                'planned_quantity' => $request->planned_quantity,
                'unit' => $request->unit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'estimated_cost' => $request->estimated_cost ?? 0,
                'warehouse_id' => $request->warehouse_id,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $plan->load(['product', 'billOfMaterial', 'warehouse', 'createdBy']);

            return response()->json([
                'message' => 'Production Plan created successfully',
                'production_plan' => $plan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create Production Plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(ProductionPlan $productionPlan): JsonResponse
    {
        $productionPlan->load([
            'product',
            'billOfMaterial',
            'warehouse',
            'createdBy',
            'approvedBy',
            'workOrders'
        ]);

        return response()->json([
            'data' => $productionPlan
        ]);
    }

    public function update(Request $request, ProductionPlan $productionPlan): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'planned_quantity' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,approved,in_progress,completed,cancelled',
            'estimated_cost' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $productionPlan->update([
                'name' => $request->name,
                'description' => $request->description,
                'product_id' => $request->product_id,
                'bill_of_material_id' => $request->bill_of_material_id,
                'planned_quantity' => $request->planned_quantity,
                'unit' => $request->unit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'estimated_cost' => $request->estimated_cost ?? 0,
                'warehouse_id' => $request->warehouse_id,
                'notes' => $request->notes,
            ]);

            $productionPlan->load(['product', 'billOfMaterial', 'warehouse', 'createdBy', 'approvedBy']);

            return response()->json([
                'message' => 'Production Plan updated successfully',
                'production_plan' => $productionPlan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update Production Plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ProductionPlan $productionPlan): JsonResponse
    {
        try {
            $productionPlan->delete();
            return response()->json([
                'message' => 'Production Plan deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete Production Plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateNumber(): JsonResponse
    {
        $planNumber = (new ProductionPlan())->generatePlanNumber();
        
        return response()->json([
            'plan_number' => $planNumber
        ]);
    }

    public function approve(Request $request, ProductionPlan $productionPlan): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $productionPlan->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            return response()->json([
                'message' => 'Production Plan approved successfully',
                'production_plan' => $productionPlan->load(['product', 'billOfMaterial', 'warehouse', 'createdBy', 'approvedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve Production Plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
