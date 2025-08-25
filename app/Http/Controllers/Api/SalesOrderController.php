<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class SalesOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SalesOrder::with(['customer', 'created_by_user', 'company']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $salesOrders = $query->latest()->paginate(15);

        return JsonResource::collection($salesOrders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'so_number' => 'required|string|max:100|unique:sales_orders',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['created_by'] = Auth::id();

        $salesOrder = SalesOrder::create($validated);

        // Create order items
        foreach ($validated['items'] as $item) {
            $salesOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'message' => 'Sales order created successfully',
            'sales_order' => $salesOrder->load(['customer', 'created_by_user', 'items.product']),
        ], 201);
    }

    public function show(SalesOrder $salesOrder): JsonResource
    {
        return new JsonResource($salesOrder->load(['customer', 'created_by_user', 'items.product', 'company']));
    }

    public function update(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'so_number' => 'required|string|max:100|unique:sales_orders,so_number,' . $salesOrder->id,
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $salesOrder->update($validated);

        // Update order items
        $salesOrder->items()->delete();
        foreach ($validated['items'] as $item) {
            $salesOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'message' => 'Sales order updated successfully',
            'sales_order' => $salesOrder->load(['customer', 'created_by_user', 'items.product']),
        ]);
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->delete();

        return response()->json([
            'message' => 'Sales order deleted successfully',
        ]);
    }
}
