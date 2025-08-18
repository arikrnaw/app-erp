<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductLot;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductLotController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ProductLot::with(['product.category', 'company']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('lot_number', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Product filter
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Expiry date filter
        if ($request->has('expiry_from')) {
            $query->where('expiry_date', '>=', $request->expiry_from);
        }
        if ($request->has('expiry_to')) {
            $query->where('expiry_date', '<=', $request->expiry_to);
        }

        $lots = $query->orderBy('expiry_date', 'asc')->paginate(15);

        return JsonResource::collection($lots);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|string|max:100|unique:product_lots',
            'batch_number' => 'nullable|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:manufacturing_date',
            'initial_quantity' => 'required|integer|min:0',
            'current_quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,expired,depleted',
        ]);

        $validated['company_id'] = 1; // Assuming single company for now

        $lot = ProductLot::create($validated);

        return response()->json([
            'message' => 'Product lot created successfully',
            'product_lot' => $lot->load(['product.category', 'company']),
        ], 201);
    }

    public function show(ProductLot $productLot): JsonResource
    {
        return new JsonResource($productLot->load(['product.category', 'company', 'serials']));
    }

    public function update(Request $request, ProductLot $productLot): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|string|max:100|unique:product_lots,lot_number,' . $productLot->id,
            'batch_number' => 'nullable|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:manufacturing_date',
            'initial_quantity' => 'required|integer|min:0',
            'current_quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,expired,depleted',
        ]);

        $productLot->update($validated);

        return response()->json([
            'message' => 'Product lot updated successfully',
            'product_lot' => $productLot->load(['product.category', 'company', 'serials']),
        ]);
    }

    public function destroy(ProductLot $productLot): JsonResponse
    {
        // Check if lot has any transactions
        if ($productLot->inventoryTransactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete lot with existing inventory transactions',
            ], 422);
        }

        $productLot->delete();

        return response()->json([
            'message' => 'Product lot deleted successfully',
        ]);
    }

    public function getByProduct(Product $product): JsonResponse
    {
        $lots = $product->productLots()
            ->where('status', 'active')
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json($lots);
    }

    public function getExpiringSoon(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $date = now()->addDays($days);

        $lots = ProductLot::with(['product.category'])
            ->where('status', 'active')
            ->where('current_quantity', '>', 0)
            ->where('expiry_date', '<=', $date)
            ->where('expiry_date', '>=', now())
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json($lots);
    }
}
