<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReorderAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReorderAlertController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ReorderAlert::with(['product.category', 'warehouse']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($productQuery) use ($search) {
                $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Warehouse filter
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(15);

        return JsonResource::collection($alerts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'current_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'suggested_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,processed,cancelled',
        ]);

        $validated['company_id'] = 1; // Assuming single company for now

        $alert = ReorderAlert::create($validated);

        return response()->json([
            'message' => 'Reorder alert created successfully',
            'reorder_alert' => $alert->load(['product.category', 'warehouse']),
        ], 201);
    }

    public function show(ReorderAlert $reorderAlert): JsonResource
    {
        return new JsonResource($reorderAlert->load(['product.category', 'warehouse']));
    }

    public function update(Request $request, ReorderAlert $reorderAlert): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'current_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'suggested_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,processed,cancelled',
        ]);

        if ($validated['status'] === 'processed' && $reorderAlert->status !== 'processed') {
            $validated['processed_at'] = now();
        }

        $reorderAlert->update($validated);

        return response()->json([
            'message' => 'Reorder alert updated successfully',
            'reorder_alert' => $reorderAlert->load(['product.category', 'warehouse']),
        ]);
    }

    public function destroy(ReorderAlert $reorderAlert): JsonResponse
    {
        $reorderAlert->delete();

        return response()->json([
            'message' => 'Reorder alert deleted successfully',
        ]);
    }

    public function getPending(): JsonResponse
    {
        $alerts = ReorderAlert::with(['product.category', 'warehouse'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($alerts);
    }

    public function process(ReorderAlert $reorderAlert): JsonResponse
    {
        if ($reorderAlert->status !== 'pending') {
            return response()->json([
                'message' => 'Alert is not in pending status',
            ], 422);
        }

        $reorderAlert->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Reorder alert processed successfully',
            'reorder_alert' => $reorderAlert->load(['product.category', 'warehouse']),
        ]);
    }

    public function generateAlerts(): JsonResponse
    {
        $products = Product::where('auto_reorder', true)
            ->where('stock_quantity', '<=', DB::raw('reorder_point'))
            ->get();

        $generatedCount = 0;

        foreach ($products as $product) {
            // Check if there's already a pending alert for this product
            $existingAlert = ReorderAlert::where('product_id', $product->id)
                ->where('status', 'pending')
                ->first();

            if (!$existingAlert) {
                ReorderAlert::create([
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'warehouse_id' => $product->default_warehouse_id,
                    'current_stock' => $product->stock_quantity,
                    'reorder_point' => $product->reorder_point,
                    'suggested_quantity' => $product->reorder_quantity,
                    'status' => 'pending',
                ]);

                $generatedCount++;
            }
        }

        return response()->json([
            'message' => "Generated {$generatedCount} new reorder alerts",
            'generated_count' => $generatedCount,
        ]);
    }

    public function getSummary(): JsonResponse
    {
        $summary = [
            'total_alerts' => ReorderAlert::count(),
            'pending_alerts' => ReorderAlert::where('status', 'pending')->count(),
            'processed_alerts' => ReorderAlert::where('status', 'processed')->count(),
            'cancelled_alerts' => ReorderAlert::where('status', 'cancelled')->count(),
        ];

        return response()->json($summary);
    }
}
