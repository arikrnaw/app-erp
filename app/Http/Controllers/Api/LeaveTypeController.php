<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveType::with(['company'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by paid status
        if ($request->has('is_paid') && $request->is_paid !== '') {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        // Filter by requires approval
        if ($request->has('requires_approval') && $request->requires_approval !== '') {
            $query->where('requires_approval', $request->boolean('requires_approval'));
        }

        // Filter by can carry forward
        if ($request->has('can_carry_forward') && $request->can_carry_forward !== '') {
            $query->where('can_carry_forward', $request->boolean('can_carry_forward'));
        }

        // Sort
        $sortField = $request->get('sort_field', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $leaveTypes = $query->paginate($perPage);

        return response()->json([
            'data' => $leaveTypes->items(),
            'pagination' => [
                'current_page' => $leaveTypes->currentPage(),
                'last_page' => $leaveTypes->lastPage(),
                'per_page' => $leaveTypes->perPage(),
                'total' => $leaveTypes->total(),
            ]
        ]);
    }

    /**
     * Store a newly created leave type.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'default_days_per_year' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_document' => 'boolean',
            'can_carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::create([
                'company_id' => Auth::user()->company_id,
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color ?? '#3B82F6',
                'default_days_per_year' => $request->default_days_per_year,
                'is_paid' => $request->boolean('is_paid', false),
                'requires_approval' => $request->boolean('requires_approval', true),
                'requires_document' => $request->boolean('requires_document', false),
                'can_carry_forward' => $request->boolean('can_carry_forward', false),
                'max_carry_forward_days' => $request->max_carry_forward_days,
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Leave type created successfully',
                'data' => $leaveType->load('company')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create leave type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified leave type.
     */
    public function show(LeaveType $leaveType): JsonResponse
    {
        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave type not found'], 404);
        }

        $leaveType->load(['company', 'leaveRequests']);

        return response()->json([
            'data' => $leaveType
        ]);
    }

    /**
     * Update the specified leave type.
     */
    public function update(Request $request, LeaveType $leaveType): JsonResponse
    {
        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave type not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'default_days_per_year' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_document' => 'boolean',
            'can_carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveType->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color ?? $leaveType->color,
                'default_days_per_year' => $request->default_days_per_year,
                'is_paid' => $request->boolean('is_paid', $leaveType->is_paid),
                'requires_approval' => $request->boolean('requires_approval', $leaveType->requires_approval),
                'requires_document' => $request->boolean('requires_document', $leaveType->requires_document),
                'can_carry_forward' => $request->boolean('can_carry_forward', $leaveType->can_carry_forward),
                'max_carry_forward_days' => $request->max_carry_forward_days,
                'is_active' => $request->boolean('is_active', $leaveType->is_active),
                'sort_order' => $request->sort_order ?? $leaveType->sort_order,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Leave type updated successfully',
                'data' => $leaveType->load('company')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update leave type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified leave type.
     */
    public function destroy(LeaveType $leaveType): JsonResponse
    {
        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave type not found'], 404);
        }

        // Check if leave type has associated leave requests
        if ($leaveType->leaveRequests()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete leave type with associated leave requests'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveType->delete();

            DB::commit();

            return response()->json([
                'message' => 'Leave type deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete leave type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active leave types.
     */
    public function active(): JsonResponse
    {
        $leaveTypes = LeaveType::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->ordered()
            ->get();

        return response()->json([
            'data' => $leaveTypes
        ]);
    }

    /**
     * Get leave type statistics.
     */
    public function statistics(LeaveType $leaveType): JsonResponse
    {
        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave type not found'], 404);
        }

        $statistics = [
            'total_requests' => $leaveType->usage_count,
            'approved_requests' => $leaveType->active_usage_count,
            'pending_requests' => $leaveType->pending_usage_count,
            'rejected_requests' => $leaveType->rejected_usage_count,
            'total_days_used' => $leaveType->total_days_used,
            'average_days_per_request' => $leaveType->average_days_per_request,
            'remaining_days' => $leaveType->remaining_days,
            'usage_percentage' => $leaveType->usage_percentage,
        ];

        return response()->json([
            'data' => $statistics
        ]);
    }

    /**
     * Toggle leave type active status.
     */
    public function toggleStatus(LeaveType $leaveType): JsonResponse
    {
        // Check if leave type belongs to user's company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave type not found'], 404);
        }

        try {
            $leaveType->update([
                'is_active' => !$leaveType->is_active
            ]);

            return response()->json([
                'message' => 'Leave type status updated successfully',
                'data' => $leaveType->load('company')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update leave type status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
