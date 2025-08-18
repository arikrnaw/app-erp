<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\BomItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillOfMaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BillOfMaterial::with(['product', 'createdBy', 'approvedBy'])
            ->where('company_id', auth()->user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bom_number', 'like', "%{$search}%")
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

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $boms = $query->orderBy('created_at', 'desc')
                     ->paginate($request->get('per_page', 15));

        return response()->json($boms);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'quantity_per_unit' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'status' => 'required|in:draft,active,inactive,archived',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity_required' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.sequence' => 'nullable|integer|min:0',
            'items.*.is_critical' => 'nullable|boolean',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bom = BillOfMaterial::create([
                'company_id' => auth()->user()->company_id,
                'bom_number' => (new BillOfMaterial())->generateBomNumber(),
                'name' => $request->name,
                'description' => $request->description,
                'product_id' => $request->product_id,
                'quantity_per_unit' => $request->quantity_per_unit,
                'unit' => $request->unit,
                'status' => $request->status,
                'effective_date' => $request->effective_date,
                'expiry_date' => $request->expiry_date,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Create BOM items
            $totalCost = 0;
            foreach ($request->items as $item) {
                $itemTotalCost = $item['quantity_required'] * $item['unit_cost'];
                $totalCost += $itemTotalCost;

                BomItem::create([
                    'bill_of_material_id' => $bom->id,
                    'product_id' => $item['product_id'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity_required' => $item['quantity_required'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $itemTotalCost,
                    'sequence' => $item['sequence'] ?? 0,
                    'is_critical' => $item['is_critical'] ?? false,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update total cost
            $bom->update(['total_cost' => $totalCost]);

            DB::commit();

            $bom->load(['product', 'items.product', 'createdBy']);

            return response()->json([
                'message' => 'Bill of Material created successfully',
                'bill_of_material' => $bom
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create Bill of Material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(BillOfMaterial $billOfMaterial): JsonResponse
    {
        $billOfMaterial->load([
            'product',
            'items.product',
            'createdBy',
            'approvedBy'
        ]);

        return response()->json([
            'data' => $billOfMaterial
        ]);
    }

    public function update(Request $request, BillOfMaterial $billOfMaterial): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'quantity_per_unit' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'status' => 'required|in:draft,active,inactive,archived',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity_required' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.sequence' => 'nullable|integer|min:0',
            'items.*.is_critical' => 'nullable|boolean',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $billOfMaterial->update([
                'name' => $request->name,
                'description' => $request->description,
                'product_id' => $request->product_id,
                'quantity_per_unit' => $request->quantity_per_unit,
                'unit' => $request->unit,
                'status' => $request->status,
                'effective_date' => $request->effective_date,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $billOfMaterial->items()->delete();

            // Create new items
            $totalCost = 0;
            foreach ($request->items as $item) {
                $itemTotalCost = $item['quantity_required'] * $item['unit_cost'];
                $totalCost += $itemTotalCost;

                BomItem::create([
                    'bill_of_material_id' => $billOfMaterial->id,
                    'product_id' => $item['product_id'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity_required' => $item['quantity_required'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $itemTotalCost,
                    'sequence' => $item['sequence'] ?? 0,
                    'is_critical' => $item['is_critical'] ?? false,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update total cost
            $billOfMaterial->update(['total_cost' => $totalCost]);

            DB::commit();

            $billOfMaterial->load(['product', 'items.product', 'createdBy', 'approvedBy']);

            return response()->json([
                'message' => 'Bill of Material updated successfully',
                'bill_of_material' => $billOfMaterial
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update Bill of Material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(BillOfMaterial $billOfMaterial): JsonResponse
    {
        try {
            $billOfMaterial->delete();
            return response()->json([
                'message' => 'Bill of Material deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete Bill of Material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateNumber(): JsonResponse
    {
        $bomNumber = (new BillOfMaterial())->generateBomNumber();
        
        return response()->json([
            'bom_number' => $bomNumber
        ]);
    }

    public function approve(Request $request, BillOfMaterial $billOfMaterial): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $billOfMaterial->update([
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            return response()->json([
                'message' => 'Bill of Material approved successfully',
                'bill_of_material' => $billOfMaterial->load(['product', 'items.product', 'createdBy', 'approvedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve Bill of Material',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
