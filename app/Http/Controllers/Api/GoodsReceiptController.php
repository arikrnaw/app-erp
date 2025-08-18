<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder', 'supplier', 'warehouse', 'receivedBy', 'items.product'])
            ->where('company_id', auth()->user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('delivery_note_number', 'like', "%{$search}%")
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
            $query->where('receipt_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('receipt_date', '<=', $request->end_date);
        }

        $goodsReceipts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($goodsReceipts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,received,partially_received,cancelled',
            'delivery_note_number' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $goodsReceipt = GoodsReceipt::create([
                'company_id' => auth()->user()->company_id,
                'receipt_number' => $request->receipt_number ?: $this->generateReceiptNumber(),
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'received_by' => auth()->id(),
                'receipt_date' => $request->receipt_date,
                'status' => $request->status,
                'delivery_note_number' => $request->delivery_note_number,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'notes' => $request->notes,
                'total_amount' => $request->total_amount ?: 0,
            ]);

            // Create items
            foreach ($request->items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id' => $item['product_id'] ?? null,
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['received_quantity'] * $item['unit_price'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $goodsReceipt->load(['purchaseOrder', 'supplier', 'warehouse', 'receivedBy', 'items.product']);

            return response()->json([
                'message' => 'Goods receipt created successfully',
                'goods_receipt' => $goodsReceipt
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating goods receipt: ' . $e->getMessage()], 500);
        }
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['purchaseOrder', 'supplier', 'warehouse', 'receivedBy', 'items.product', 'purchaseReturns']);
        
        return response()->json([
            'data' => $goodsReceipt
        ]);
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,received,partially_received,cancelled',
            'delivery_note_number' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $goodsReceipt->update([
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'receipt_date' => $request->receipt_date,
                'status' => $request->status,
                'delivery_note_number' => $request->delivery_note_number,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'notes' => $request->notes,
                'total_amount' => $request->total_amount ?: 0,
            ]);

            // Delete existing items
            $goodsReceipt->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id' => $item['product_id'] ?? null,
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['received_quantity'] * $item['unit_price'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $goodsReceipt->load(['purchaseOrder', 'supplier', 'warehouse', 'receivedBy', 'items.product']);

            return response()->json([
                'message' => 'Goods receipt updated successfully',
                'goods_receipt' => $goodsReceipt
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating goods receipt: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        try {
            DB::beginTransaction();
            
            // Delete items first
            $goodsReceipt->items()->delete();
            
            // Delete the goods receipt
            $goodsReceipt->delete();
            
            DB::commit();

            return response()->json(['message' => 'Goods receipt deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting goods receipt: ' . $e->getMessage()], 500);
        }
    }

    public function generateNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastReceipt = GoodsReceipt::where('receipt_number', 'like', "GR{$year}{$month}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $receiptNumber = "GR{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'receipt_number' => $receiptNumber
        ]);
    }

    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastReceipt = GoodsReceipt::where('receipt_number', 'like', "GR{$year}{$month}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "GR{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
