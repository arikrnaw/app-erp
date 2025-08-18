<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\ProductLot;
use App\Models\ReorderAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryTransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = InventoryTransaction::with([
            'product.category',
            'warehouse',
            'warehouseLocation',
            'productLot',
            'createdBy'
        ]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Product filter
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Warehouse filter
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(15);

        return JsonResource::collection($transactions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment,transfer,return,damage',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'transaction_date' => 'required|date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
            'product_lot_id' => 'nullable|exists:product_lots,id',
            'serial_numbers' => 'nullable|array',
            'serial_numbers.*' => 'string',
        ]);

        $validated['company_id'] = 1; // Assuming single company for now
        $validated['created_by'] = Auth::id();
        $validated['transaction_number'] = $this->generateTransactionNumber();
        $validated['total_cost'] = $validated['quantity'] * ($validated['unit_cost'] ?? 0);

        DB::beginTransaction();
        try {
            $transaction = InventoryTransaction::create($validated);

            // Update product stock
            $product = Product::find($validated['product_id']);
            if ($product) {
                $this->updateProductStock($product, $validated['type'], $validated['quantity'], $validated['unit_cost'] ?? 0);
            }

            // Update lot quantity if applicable
            if (isset($validated['product_lot_id'])) {
                $this->updateLotQuantity($validated['product_lot_id'], $validated['type'], $validated['quantity']);
            }

            // Check for reorder alerts
            if ($validated['type'] === 'out' && $product) {
                $this->checkReorderAlert($product, $validated['warehouse_id'] ?? null);
            }

            DB::commit();

            return response()->json([
                'message' => 'Inventory transaction created successfully',
                'inventory_transaction' => $transaction->load([
                    'product.category',
                    'warehouse',
                    'warehouseLocation',
                    'productLot',
                    'createdBy'
                ]),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(InventoryTransaction $inventoryTransaction): JsonResource
    {
        return new JsonResource($inventoryTransaction->load([
            'product.category',
            'warehouse',
            'warehouseLocation',
            'productLot',
            'createdBy'
        ]));
    }

    public function update(Request $request, InventoryTransaction $inventoryTransaction): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment,transfer,return,damage',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'transaction_date' => 'required|date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
            'product_lot_id' => 'nullable|exists:product_lots,id',
            'serial_numbers' => 'nullable|array',
            'serial_numbers.*' => 'string',
        ]);

        $validated['total_cost'] = $validated['quantity'] * ($validated['unit_cost'] ?? 0);

        DB::beginTransaction();
        try {
            // Reverse the old transaction
            $oldType = $inventoryTransaction->type;
            $oldQuantity = $inventoryTransaction->quantity;
            $oldUnitCost = $inventoryTransaction->unit_cost ?? 0;
            $oldProductId = $inventoryTransaction->product_id;
            $oldLotId = $inventoryTransaction->product_lot_id;

            $product = Product::find($oldProductId);
            if ($product) {
                $this->updateProductStock($product, $this->getReverseType($oldType), $oldQuantity, $oldUnitCost);
            }

            if ($oldLotId) {
                $this->updateLotQuantity($oldLotId, $this->getReverseType($oldType), $oldQuantity);
            }

            // Apply the new transaction
            $inventoryTransaction->update($validated);

            $product = Product::find($validated['product_id']);
            if ($product) {
                $this->updateProductStock($product, $validated['type'], $validated['quantity'], $validated['unit_cost'] ?? 0);
            }

            if (isset($validated['product_lot_id'])) {
                $this->updateLotQuantity($validated['product_lot_id'], $validated['type'], $validated['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Inventory transaction updated successfully',
                'inventory_transaction' => $inventoryTransaction->load([
                    'product.category',
                    'warehouse',
                    'warehouseLocation',
                    'productLot',
                    'createdBy'
                ]),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(InventoryTransaction $inventoryTransaction): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Reverse the transaction
            $product = Product::find($inventoryTransaction->product_id);
            if ($product) {
                $this->updateProductStock($product, $this->getReverseType($inventoryTransaction->type), $inventoryTransaction->quantity, $inventoryTransaction->unit_cost ?? 0);
            }

            if ($inventoryTransaction->product_lot_id) {
                $this->updateLotQuantity($inventoryTransaction->product_lot_id, $this->getReverseType($inventoryTransaction->type), $inventoryTransaction->quantity);
            }

            $inventoryTransaction->delete();

            DB::commit();

            return response()->json([
                'message' => 'Inventory transaction deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateTransactionNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastTransaction = InventoryTransaction::where('transaction_number', 'like', "{$prefix}{$date}%")
            ->orderBy('transaction_number', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function updateProductStock(Product $product, string $type, int $quantity, float $unitCost): void
    {
        $stockChange = $type === 'in' ? $quantity : -$quantity;
        $product->stock_quantity += $stockChange;

        if ($type === 'in') {
            $product->last_stock_in_date = now();
            $product->last_cost = $unitCost;
            
            // Update average cost
            if ($product->stock_quantity > 0) {
                $totalValue = ($product->stock_quantity * $product->average_cost) + ($quantity * $unitCost);
                $product->average_cost = $totalValue / $product->stock_quantity;
            }
        } elseif (in_array($type, ['out', 'damage'])) {
            $product->last_stock_out_date = now();
        }

        $product->save();
    }

    private function updateLotQuantity(int $lotId, string $type, int $quantity): void
    {
        $lot = ProductLot::find($lotId);
        if ($lot) {
            $stockChange = $type === 'in' ? $quantity : -$quantity;
            $lot->current_quantity += $stockChange;
            
            if ($lot->current_quantity <= 0) {
                $lot->status = 'depleted';
            }
            
            $lot->save();
        }
    }

    private function checkReorderAlert(Product $product, ?int $warehouseId): void
    {
        if ($product->auto_reorder && $product->needs_reorder) {
            ReorderAlert::create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'current_stock' => $product->stock_quantity,
                'reorder_point' => $product->reorder_point,
                'suggested_quantity' => $product->reorder_quantity,
                'status' => 'pending',
            ]);
        }
    }

    private function getReverseType(string $type): string
    {
        $reverseMap = [
            'in' => 'out',
            'out' => 'in',
            'adjustment' => 'adjustment',
            'transfer' => 'transfer',
            'return' => 'out',
            'damage' => 'in',
        ];

        return $reverseMap[$type] ?? 'adjustment';
    }

    public function getSummary(Request $request): JsonResponse
    {
        $query = InventoryTransaction::with('product');

        if ($request->has('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $summary = [
            'total_transactions' => $query->count(),
            'total_stock_in' => $query->where('type', 'in')->sum('quantity'),
            'total_stock_out' => $query->where('type', 'out')->sum('quantity'),
            'total_adjustments' => $query->where('type', 'adjustment')->sum('quantity'),
            'total_value_in' => $query->where('type', 'in')->sum('total_cost'),
            'total_value_out' => $query->where('type', 'out')->sum('total_cost'),
        ];

        return response()->json($summary);
    }
}
