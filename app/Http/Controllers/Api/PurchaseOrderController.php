<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Finance\ApprovalWorkflow;
use App\Models\Finance\ApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    /**
     * Get purchase orders with approval status
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PurchaseOrder::with(['supplier', 'items', 'approvalRequest'])
                ->when($request->has('status'), function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->has('supplier_id'), function ($q) use ($request) {
                    $q->where('supplier_id', $request->supplier_id);
                })
                ->when($request->has('date_from'), function ($q) use ($request) {
                    $q->where('order_date', '>=', $request->date_from);
                })
                ->when($request->has('date_to'), function ($q) use ($request) {
                    $q->where('order_date', '<=', $request->date_to);
                });

            $purchaseOrders = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $purchaseOrders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading purchase orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create purchase order with approval workflow
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'required|date|after:order_date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'shipping_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Calculate total amount
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $taxAmount = collect($validated['items'])->sum(function ($item) {
                $taxRate = $item['tax_rate'] ?? 0;
                return ($item['quantity'] * $item['unit_price']) * ($taxRate / 100);
            });

            $totalAmount = $subtotal + $taxAmount + ($validated['shipping_cost'] ?? 0);

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Check if approval is required
            $this->checkApprovalRequired($purchaseOrder, $totalAmount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'data' => $purchaseOrder->load(['supplier', 'items', 'approvalRequest'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update purchase order
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Check if PO can be updated (not in approval process)
            if ($purchaseOrder->approvalRequest && $purchaseOrder->approvalRequest->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update purchase order while approval is pending'
                ], 400);
            }

            $validated = $request->validate([
                'supplier_id' => 'sometimes|exists:suppliers,id',
                'order_date' => 'sometimes|date',
                'expected_delivery_date' => 'sometimes|date|after:order_date',
                'shipping_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $purchaseOrder->update($validated);

            // Recalculate total if items changed
            if ($request->has('items')) {
                $this->updateOrderItems($purchaseOrder, $request->items);
                $this->recalculateTotal($purchaseOrder);
            }

            // Check if approval is still required
            $this->checkApprovalRequired($purchaseOrder, $purchaseOrder->total_amount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => $purchaseOrder->load(['supplier', 'items', 'approvalRequest'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit purchase order for approval
     */
    public function submitForApproval(int $id): JsonResponse
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Check if already submitted
            if ($purchaseOrder->approvalRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order already submitted for approval'
                ], 400);
            }

            // Check if approval is required
            $workflow = $this->getApprovalWorkflow($purchaseOrder->total_amount);
            if (!$workflow) {
                // Auto-approve if no workflow found
                $purchaseOrder->update(['status' => 'approved']);
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase order auto-approved',
                    'data' => $purchaseOrder
                ]);
            }

            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => PurchaseOrder::class,
                'approvable_id' => $purchaseOrder->id,
                'amount' => $purchaseOrder->total_amount,
                'description' => "Purchase Order #{$purchaseOrder->id} - {$purchaseOrder->supplier->name}",
                'priority' => $this->determinePriority($purchaseOrder->total_amount),
                'due_date' => now()->addDays(3),
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

            // Update PO status
            $purchaseOrder->update(['status' => 'pending_approval']);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order submitted for approval',
                'data' => $purchaseOrder->load(['supplier', 'items', 'approvalRequest'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting for approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase order with approval details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $purchaseOrder = PurchaseOrder::with([
                'supplier', 
                'items.product', 
                'approvalRequest.workflow',
                'approvalRequest.approver',
                'approvalRequest.requestor'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $purchaseOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel purchase order
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Check if can be cancelled
            if (!in_array($purchaseOrder->status, ['draft', 'pending_approval'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order cannot be cancelled in current status'
                ], 400);
            }

            DB::beginTransaction();

            // Cancel approval request if exists
            if ($purchaseOrder->approvalRequest) {
                $purchaseOrder->approvalRequest->update([
                    'status' => 'cancelled',
                    'approver_comments' => 'Cancelled by requestor'
                ]);
            }

            // Update PO status
            $purchaseOrder->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order cancelled successfully',
                'data' => $purchaseOrder
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase orders pending approval
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        try {
            $query = PurchaseOrder::with(['supplier', 'approvalRequest.workflow'])
                ->whereHas('approvalRequest', function ($q) {
                    $q->where('status', 'pending');
                });

            $purchaseOrders = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $purchaseOrders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading pending approvals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if approval is required and create approval request
     */
    private function checkApprovalRequired(PurchaseOrder $purchaseOrder, float $totalAmount): void
    {
        $workflow = $this->getApprovalWorkflow($totalAmount);
        
        if ($workflow) {
            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => PurchaseOrder::class,
                'approvable_id' => $purchaseOrder->id,
                'amount' => $totalAmount,
                'description' => "Purchase Order #{$purchaseOrder->id} - {$purchaseOrder->supplier->name}",
                'priority' => $this->determinePriority($totalAmount),
                'due_date' => now()->addDays(3),
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

            // Update PO status
            $purchaseOrder->update(['status' => 'pending_approval']);
        } else {
            // Auto-approve if no workflow found
            $purchaseOrder->update(['status' => 'approved']);
        }
    }

    /**
     * Get appropriate approval workflow based on amount
     */
    private function getApprovalWorkflow(float $amount): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::active()
            ->byType('purchase_order')
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
        if ($amount >= 10000) return 'medium';
        return 'low';
    }

    /**
     * Update order items
     */
    private function updateOrderItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        // Delete existing items
        $purchaseOrder->items()->delete();

        // Create new items
        foreach ($items as $item) {
            $purchaseOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }
    }

    /**
     * Recalculate total amount
     */
    private function recalculateTotal(PurchaseOrder $purchaseOrder): void
    {
        $subtotal = $purchaseOrder->items->sum('total_price');
        $taxAmount = $purchaseOrder->items->sum(function ($item) {
            return $item->total_price * ($item->tax_rate / 100);
        });
        $totalAmount = $subtotal + $taxAmount + ($purchaseOrder->shipping_cost ?? 0);

        $purchaseOrder->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }
}
