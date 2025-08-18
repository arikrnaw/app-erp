<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier', 'createdByUser', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Apply date filter
        if ($request->filled('date_filter')) {
            $query->dateFilter($request->date_filter);
        }

        $purchaseOrders = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $purchaseOrders->items(),
            'links' => $purchaseOrders->linkCollection(),
            'meta' => [
                'current_page' => $purchaseOrders->currentPage(),
                'from' => $purchaseOrders->firstItem(),
                'last_page' => $purchaseOrders->lastPage(),
                'per_page' => $purchaseOrders->perPage(),
                'to' => $purchaseOrders->lastItem(),
                'total' => $purchaseOrders->total(),
            ]
        ]);
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:50|unique:purchase_orders',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'status' => ['required', Rule::in(['draft', 'confirmed', 'received', 'cancelled'])],
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            // Create purchase order items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'description' => $item['description'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Calculate total amount
            $purchaseOrder->calculateTotalAmount();

            DB::commit();

            $purchaseOrder->load(['supplier', 'createdByUser', 'items.product']);

            return response()->json([
                'message' => 'Purchase order created successfully',
                'purchase_order' => $purchaseOrder
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['supplier', 'createdByUser', 'updatedByUser', 'items.product']);

        return response()->json([
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Purchase order cannot be edited in its current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'po_number' => ['required', 'string', 'max:50', Rule::unique('purchase_orders')->ignore($purchaseOrder->id)],
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'status' => ['required', Rule::in(['draft', 'confirmed', 'received', 'cancelled'])],
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseOrder->update([
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'updated_by' => $request->user()->id,
            ]);

            // Delete existing items
            $purchaseOrder->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'description' => $item['description'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Calculate total amount
            $purchaseOrder->calculateTotalAmount();

            DB::commit();

            $purchaseOrder->load(['supplier', 'createdByUser', 'updatedByUser', 'items.product']);

            return response()->json([
                'message' => 'Purchase order updated successfully',
                'purchase_order' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!$purchaseOrder->canBeDeleted()) {
            return response()->json([
                'message' => 'Purchase order cannot be deleted in its current status'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete items first
            $purchaseOrder->items()->delete();
            
            // Delete purchase order
            $purchaseOrder->delete();

            DB::commit();

            return response()->json([
                'message' => 'Purchase order deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update purchase order status
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['draft', 'confirmed', 'received', 'cancelled'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $newStatus = $request->status;

        // Validate status transition
        if ($newStatus === 'cancelled' && !$purchaseOrder->canBeCancelled()) {
            return response()->json([
                'message' => 'Purchase order cannot be cancelled in its current status'
            ], 422);
        }

        if ($newStatus === 'received' && !$purchaseOrder->canBeReceived()) {
            return response()->json([
                'message' => 'Purchase order cannot be marked as received in its current status'
            ], 422);
        }

        try {
            $purchaseOrder->update([
                'status' => $newStatus,
                'updated_by' => $request->user()->id,
            ]);

            $purchaseOrder->load(['supplier', 'createdByUser', 'updatedByUser', 'items.product']);

            return response()->json([
                'message' => 'Purchase order status updated successfully',
                'purchase_order' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update purchase order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
