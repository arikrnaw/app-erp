<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerSegmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CustomerSegment::with(['createdByUser', 'company'])
            ->where('company_id', Auth::user()->company_id);

        // Filter by active status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Search by name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $segments = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $segments->items(),
            'pagination' => [
                'current_page' => $segments->currentPage(),
                'last_page' => $segments->lastPage(),
                'per_page' => $segments->perPage(),
                'total' => $segments->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'criteria' => 'nullable|array',
            'color' => 'nullable|string|max:7', // hex color
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $segment = CustomerSegment::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'description' => $request->description,
            'criteria' => $request->criteria,
            'color' => $request->color,
            'is_active' => $request->is_active ?? true,
            'created_by' => Auth::id(),
        ]);

        $segment->load(['createdByUser', 'company']);

        return response()->json([
            'message' => 'Customer segment created successfully',
            'data' => $segment,
        ], 201);
    }

    public function show(CustomerSegment $customerSegment): JsonResponse
    {
        if ($customerSegment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customerSegment->load(['createdByUser', 'company', 'customers']);

        return response()->json(['data' => $customerSegment]);
    }

    public function update(Request $request, CustomerSegment $customerSegment): JsonResponse
    {
        if ($customerSegment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'criteria' => 'nullable|array',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customerSegment->update($request->only([
            'name', 'description', 'criteria', 'color', 'is_active'
        ]));

        $customerSegment->load(['createdByUser', 'company']);

        return response()->json([
            'message' => 'Customer segment updated successfully',
            'data' => $customerSegment,
        ]);
    }

    public function destroy(CustomerSegment $customerSegment): JsonResponse
    {
        if ($customerSegment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customerSegment->delete();

        return response()->json(['message' => 'Customer segment deleted successfully']);
    }

    public function toggleStatus(CustomerSegment $customerSegment): JsonResponse
    {
        if ($customerSegment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customerSegment->update([
            'is_active' => !$customerSegment->is_active,
        ]);

        return response()->json([
            'message' => 'Customer segment status updated successfully',
            'data' => $customerSegment,
        ]);
    }

    public function getActive(): JsonResponse
    {
        $segments = CustomerSegment::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->select('id', 'name', 'color')
            ->get();

        return response()->json(['data' => $segments]);
    }

    public function getCustomers(CustomerSegment $customerSegment): JsonResponse
    {
        if ($customerSegment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customers = $customerSegment->customers()
            ->with(['customerSegment'])
            ->paginate(15);

        return response()->json([
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }
}

