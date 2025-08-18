<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WarehouseLocation;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseLocationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = WarehouseLocation::with(['warehouse']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('aisle', 'like', "%{$search}%")
                  ->orWhere('rack', 'like', "%{$search}%");
            });
        }

        // Warehouse filter
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $locations = $query->paginate(15);

        return JsonResource::collection($locations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouse_locations',
            'aisle' => 'nullable|string|max:50',
            'rack' => 'nullable|string|max:50',
            'level' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $location = WarehouseLocation::create($validated);

        return response()->json([
            'message' => 'Warehouse location created successfully',
            'warehouse_location' => $location->load(['warehouse']),
        ], 201);
    }

    public function show(WarehouseLocation $warehouseLocation): JsonResource
    {
        return new JsonResource($warehouseLocation->load(['warehouse']));
    }

    public function update(Request $request, WarehouseLocation $warehouseLocation): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouse_locations,code,' . $warehouseLocation->id,
            'aisle' => 'nullable|string|max:50',
            'rack' => 'nullable|string|max:50',
            'level' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $warehouseLocation->update($validated);

        return response()->json([
            'message' => 'Warehouse location updated successfully',
            'warehouse_location' => $warehouseLocation->load(['warehouse']),
        ]);
    }

    public function destroy(WarehouseLocation $warehouseLocation): JsonResponse
    {
        // Check if location has any transactions
        if ($warehouseLocation->inventoryTransactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete location with existing inventory transactions',
            ], 422);
        }

        $warehouseLocation->delete();

        return response()->json([
            'message' => 'Warehouse location deleted successfully',
        ]);
    }

    public function getByWarehouse(Warehouse $warehouse): JsonResponse
    {
        $locations = $warehouse->locations()
            ->where('status', 'active')
            ->select('id', 'name', 'code', 'aisle', 'rack', 'level', 'position')
            ->get();

        return response()->json($locations);
    }
}
