<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WorkOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkOrder::with(['product', 'workCenter', 'assignedTo', 'createdBy', 'approvedBy'])
            ->where('company_id', auth()->user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
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

        // Filter by work center
        if ($request->has('work_center_id') && $request->work_center_id) {
            $query->where('work_center_id', $request->work_center_id);
        }

        // Filter by assigned to
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('due_date') && $request->due_date) {
            $query->where('due_date', '<=', $request->due_date);
        }

        $workOrders = $query->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 15));

        return response()->json($workOrders);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'production_plan_id' => 'nullable|exists:production_plans,id',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'planned_quantity' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,approved,in_progress,paused,completed,cancelled',
            'estimated_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $workOrder = WorkOrder::create([
                'company_id' => auth()->user()->company_id,
                'work_order_number' => (new WorkOrder())->generateWorkOrderNumber(),
                'name' => $request->name,
                'description' => $request->description,
                'production_plan_id' => $request->production_plan_id,
                'product_id' => $request->product_id,
                'bill_of_material_id' => $request->bill_of_material_id,
                'planned_quantity' => $request->planned_quantity,
                'unit' => $request->unit,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'estimated_hours' => $request->estimated_hours ?? 0,
                'estimated_cost' => $request->estimated_cost ?? 0,
                'warehouse_id' => $request->warehouse_id,
                'work_center_id' => $request->work_center_id,
                'assigned_to' => $request->assigned_to,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $workOrder->load(['product', 'workCenter', 'assignedTo', 'createdBy']);

            return response()->json([
                'message' => 'Work Order created successfully',
                'work_order' => $workOrder
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create Work Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->load([
            'product',
            'workCenter',
            'assignedTo',
            'createdBy',
            'approvedBy',
            'productionTracking',
            'productionCosts'
        ]);

        return response()->json([
            'data' => $workOrder
        ]);
    }

    public function update(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'production_plan_id' => 'nullable|exists:production_plans,id',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'planned_quantity' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,approved,in_progress,paused,completed,cancelled',
            'estimated_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $workOrder->update([
                'name' => $request->name,
                'description' => $request->description,
                'production_plan_id' => $request->production_plan_id,
                'product_id' => $request->product_id,
                'bill_of_material_id' => $request->bill_of_material_id,
                'planned_quantity' => $request->planned_quantity,
                'unit' => $request->unit,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'estimated_hours' => $request->estimated_hours ?? 0,
                'estimated_cost' => $request->estimated_cost ?? 0,
                'warehouse_id' => $request->warehouse_id,
                'work_center_id' => $request->work_center_id,
                'assigned_to' => $request->assigned_to,
                'notes' => $request->notes,
            ]);

            $workOrder->load(['product', 'workCenter', 'assignedTo', 'createdBy', 'approvedBy']);

            return response()->json([
                'message' => 'Work Order updated successfully',
                'work_order' => $workOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update Work Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        try {
            $workOrder->delete();
            return response()->json([
                'message' => 'Work Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete Work Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateNumber(): JsonResponse
    {
        $workOrderNumber = (new WorkOrder())->generateWorkOrderNumber();
        
        return response()->json([
            'work_order_number' => $workOrderNumber
        ]);
    }

    public function approve(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $workOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            return response()->json([
                'message' => 'Work Order approved successfully',
                'work_order' => $workOrder->load(['product', 'workCenter', 'assignedTo', 'createdBy', 'approvedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve Work Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function start(WorkOrder $workOrder): JsonResponse
    {
        try {
            $workOrder->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            return response()->json([
                'message' => 'Work Order started successfully',
                'work_order' => $workOrder->load(['product', 'workCenter', 'assignedTo'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start Work Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complete(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'completed_quantity' => 'required|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $workOrder->update([
                'status' => 'completed',
                'completed_quantity' => $request->completed_quantity,
                'actual_hours' => $request->actual_hours ?? $workOrder->actual_hours,
                'actual_cost' => $request->actual_cost ?? $workOrder->actual_cost,
                'completed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Work Order completed successfully',
                'work_order' => $workOrder->load(['product', 'workCenter', 'assignedTo'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete Work Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
