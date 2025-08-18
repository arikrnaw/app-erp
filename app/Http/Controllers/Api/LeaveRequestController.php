<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveRequest::with(['company', 'employee', 'leaveType', 'approvedBy'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($emp) use ($search) {
                      $emp->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by employee
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->has('leave_type_id') && $request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by urgency
        if ($request->has('is_urgent') && $request->is_urgent !== '') {
            $query->where('is_urgent', $request->boolean('is_urgent'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Sort
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $leaveRequests = $query->paginate($perPage);

        return response()->json([
            'data' => $leaveRequests->items(),
            'pagination' => [
                'current_page' => $leaveRequests->currentPage(),
                'last_page' => $leaveRequests->lastPage(),
                'per_page' => $leaveRequests->perPage(),
                'total' => $leaveRequests->total(),
            ]
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'leave_duration' => 'required|in:full_day,half_day,hours',
            'reason' => 'required|string|max:500',
            'additional_notes' => 'nullable|string',
            'attachment_file' => 'nullable|string',
            'is_urgent' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if employee belongs to user's company
        $employee = Employee::find($request->employee_id);
        if ($employee->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Check if leave type belongs to user's company
        $leaveType = LeaveType::find($request->leave_type_id);
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave type not found'], 404);
        }

        // Check for overlapping leave requests
        $overlapping = LeaveRequest::where('employee_id', $request->employee_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function ($subQ) use ($request) {
                      $subQ->where('start_date', '<=', $request->start_date)
                           ->where('end_date', '>=', $request->end_date);
                  });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'message' => 'Leave request overlaps with existing approved or pending requests'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate request number
            $requestNumber = 'LR-' . date('Y') . '-' . str_pad(LeaveRequest::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

            $leaveRequest = LeaveRequest::create([
                'company_id' => Auth::user()->company_id,
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'request_number' => $requestNumber,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'leave_duration' => $request->leave_duration,
                'reason' => $request->reason,
                'additional_notes' => $request->additional_notes,
                'attachment_file' => $request->attachment_file,
                'is_urgent' => $request->boolean('is_urgent', false),
                'status' => 'pending',
            ]);

            // Calculate total days
            $leaveRequest->calculateTotalDays();
            $leaveRequest->calculateTotalHours();

            DB::commit();

            return response()->json([
                'message' => 'Leave request created successfully',
                'data' => $leaveRequest->load(['company', 'employee', 'leaveType'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if leave request belongs to user's company
        if ($leaveRequest->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        $leaveRequest->load(['company', 'employee', 'leaveType', 'approvedBy']);

        return response()->json([
            'data' => $leaveRequest
        ]);
    }

    /**
     * Update the specified leave request.
     */
    public function update(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if leave request belongs to user's company
        if ($leaveRequest->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Only allow updates if status is pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update leave request that is not pending'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'leave_duration' => 'required|in:full_day,half_day,hours',
            'reason' => 'required|string|max:500',
            'additional_notes' => 'nullable|string',
            'attachment_file' => 'nullable|string',
            'is_urgent' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for overlapping leave requests (excluding current request)
        $overlapping = LeaveRequest::where('employee_id', $leaveRequest->employee_id)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function ($subQ) use ($request) {
                      $subQ->where('start_date', '<=', $request->start_date)
                           ->where('end_date', '>=', $request->end_date);
                  });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'message' => 'Leave request overlaps with existing approved or pending requests'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveRequest->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'leave_duration' => $request->leave_duration,
                'reason' => $request->reason,
                'additional_notes' => $request->additional_notes,
                'attachment_file' => $request->attachment_file,
                'is_urgent' => $request->boolean('is_urgent', $leaveRequest->is_urgent),
            ]);

            // Recalculate total days
            $leaveRequest->calculateTotalDays();
            $leaveRequest->calculateTotalHours();

            DB::commit();

            return response()->json([
                'message' => 'Leave request updated successfully',
                'data' => $leaveRequest->load(['company', 'employee', 'leaveType'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if leave request belongs to user's company
        if ($leaveRequest->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Only allow deletion if status is pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete leave request that is not pending'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveRequest->delete();

            DB::commit();

            return response()->json([
                'message' => 'Leave request deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if leave request belongs to user's company
        if ($leaveRequest->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Only allow approval if status is pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Leave request is not pending'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Leave request approved successfully',
                'data' => $leaveRequest->load(['company', 'employee', 'leaveType', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if leave request belongs to user's company
        if ($leaveRequest->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Only allow rejection if status is pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Leave request is not pending'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Leave request rejected successfully',
                'data' => $leaveRequest->load(['company', 'employee', 'leaveType', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel leave request.
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if leave request belongs to user's company
        if ($leaveRequest->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        // Only allow cancellation if status is pending or approved
        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            return response()->json([
                'message' => 'Leave request cannot be cancelled'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveRequest->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Leave request cancelled successfully',
                'data' => $leaveRequest->load(['company', 'employee', 'leaveType', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending leave requests.
     */
    public function pending(): JsonResponse
    {
        $leaveRequests = LeaveRequest::with(['company', 'employee', 'leaveType'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $leaveRequests
        ]);
    }

    /**
     * Get urgent leave requests.
     */
    public function urgent(): JsonResponse
    {
        $leaveRequests = LeaveRequest::with(['company', 'employee', 'leaveType'])
            ->where('company_id', Auth::user()->company_id)
            ->where('is_urgent', true)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'data' => $leaveRequests
        ]);
    }
}
