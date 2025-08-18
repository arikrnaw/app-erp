<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['purchaseOrder', 'goodsReceipt', 'supplier', 'warehouse', 'returnedBy', 'approvedBy', 'items.product'])
            ->where('company_id', auth()->user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($q) use ($search) {
                      $q->where('po_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by return type
        if ($request->has('return_type') && $request->return_type) {
            $query->where('return_type', $request->return_type);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by purchase order
        if ($request->has('purchase_order_id') && $request->purchase_order_id) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('return_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('return_date', '<=', $request->end_date);
        }

        $purchaseReturns = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($purchaseReturns);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'goods_receipt_id' => 'nullable|exists:goods_receipts,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:defective,wrong_item,overstock,other',
            'status' => 'required|in:draft,submitted,approved,returned,rejected,cancelled',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.goods_receipt_item_id' => 'nullable|exists:goods_receipt_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.return_quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.return_reason' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseReturn = PurchaseReturn::create([
                'company_id' => auth()->user()->company_id,
                'return_number' => $request->return_number ?: $this->generateReturnNumber(),
                'purchase_order_id' => $request->purchase_order_id,
                'goods_receipt_id' => $request->goods_receipt_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'returned_by' => auth()->id(),
                'return_date' => $request->return_date,
                'return_type' => $request->return_type,
                'status' => $request->status,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'total_amount' => $request->total_amount ?: 0,
                'approved_by' => $request->status === 'approved' ? auth()->id() : null,
                'approved_at' => $request->status === 'approved' ? now() : null,
                'approval_notes' => $request->approval_notes,
            ]);

            // Create items
            foreach ($request->items as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'goods_receipt_item_id' => $item['goods_receipt_item_id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'received_quantity' => $item['received_quantity'],
                    'return_quantity' => $item['return_quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['return_quantity'] * $item['unit_price'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'return_reason' => $item['return_reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $purchaseReturn->load(['purchaseOrder', 'goodsReceipt', 'supplier', 'warehouse', 'returnedBy', 'approvedBy', 'items.product']);

            return response()->json([
                'message' => 'Purchase return created successfully',
                'purchase_return' => $purchaseReturn
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating purchase return: ' . $e->getMessage()], 500);
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchaseOrder', 'goodsReceipt', 'supplier', 'warehouse', 'returnedBy', 'approvedBy', 'items.product']);
        
        return response()->json([
            'data' => $purchaseReturn
        ]);
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'goods_receipt_id' => 'nullable|exists:goods_receipts,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:defective,wrong_item,overstock,other',
            'status' => 'required|in:draft,submitted,approved,returned,rejected,cancelled',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.goods_receipt_item_id' => 'nullable|exists:goods_receipt_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.return_quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.return_reason' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseReturn->update([
                'purchase_order_id' => $request->purchase_order_id,
                'goods_receipt_id' => $request->goods_receipt_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'return_date' => $request->return_date,
                'return_type' => $request->return_type,
                'status' => $request->status,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'total_amount' => $request->total_amount ?: 0,
                'approved_by' => $request->status === 'approved' ? auth()->id() : $purchaseReturn->approved_by,
                'approved_at' => $request->status === 'approved' ? now() : $purchaseReturn->approved_at,
                'approval_notes' => $request->approval_notes,
            ]);

            // Delete existing items
            $purchaseReturn->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'goods_receipt_item_id' => $item['goods_receipt_item_id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'received_quantity' => $item['received_quantity'],
                    'return_quantity' => $item['return_quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['return_quantity'] * $item['unit_price'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'return_reason' => $item['return_reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $purchaseReturn->load(['purchaseOrder', 'goodsReceipt', 'supplier', 'warehouse', 'returnedBy', 'approvedBy', 'items.product']);

            return response()->json([
                'message' => 'Purchase return updated successfully',
                'purchase_return' => $purchaseReturn
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating purchase return: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        try {
            DB::beginTransaction();
            
            // Delete items first
            $purchaseReturn->items()->delete();
            
            // Delete the purchase return
            $purchaseReturn->delete();
            
            DB::commit();

            return response()->json(['message' => 'Purchase return deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting purchase return: ' . $e->getMessage()], 500);
        }
    }

    public function generateNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastReturn = PurchaseReturn::where('return_number', 'like', "PRET{$year}{$month}%")
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            $lastNumber = intval(substr($lastReturn->return_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $returnNumber = "PRET{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'return_number' => $returnNumber
        ]);
    }

    private function generateReturnNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastReturn = PurchaseReturn::where('return_number', 'like', "PRET{$year}{$month}%")
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            $lastNumber = intval(substr($lastReturn->return_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PRET{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
