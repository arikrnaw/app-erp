<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayrollPeriodController extends Controller
{
    /**
     * Display a listing of payroll periods.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PayrollPeriod::with(['company', 'createdBy', 'approvedBy'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('period_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by frequency
        if ($request->has('frequency') && $request->frequency) {
            $query->where('frequency', $request->frequency);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Sort
        $sortField = $request->get('sort_field', 'start_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $payrollPeriods = $query->paginate($perPage);

        return response()->json([
            'data' => $payrollPeriods->items(),
            'pagination' => [
                'current_page' => $payrollPeriods->currentPage(),
                'last_page' => $payrollPeriods->lastPage(),
                'per_page' => $payrollPeriods->perPage(),
                'total' => $payrollPeriods->total(),
            ]
        ]);
    }

    /**
     * Store a newly created payroll period.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period_code' => 'required|string|max:50|unique:payroll_periods,period_code',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'frequency' => 'required|in:daily,weekly,bi_weekly,monthly,quarterly,yearly',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod = PayrollPeriod::create([
                'company_id' => Auth::user()->company_id,
                'period_code' => $request->period_code,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pay_date' => $request->pay_date,
                'frequency' => $request->frequency,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payroll period created successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create payroll period',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payroll period.
     */
    public function show(PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        $payrollPeriod->load(['company', 'createdBy', 'approvedBy', 'payrollRecords']);

        return response()->json([
            'data' => $payrollPeriod
        ]);
    }

    /**
     * Update the specified payroll period.
     */
    public function update(Request $request, PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        // Only allow updates if status is draft
        if ($payrollPeriod->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot update payroll period that is not in draft status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'period_code' => 'required|string|max:50|unique:payroll_periods,period_code,' . $payrollPeriod->id,
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'frequency' => 'required|in:daily,weekly,bi_weekly,monthly,quarterly,yearly',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->update([
                'period_code' => $request->period_code,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pay_date' => $request->pay_date,
                'frequency' => $request->frequency,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payroll period updated successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update payroll period',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payroll period.
     */
    public function destroy(PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        // Only allow deletion if status is draft
        if ($payrollPeriod->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot delete payroll period that is not in draft status'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->delete();

            DB::commit();

            return response()->json([
                'message' => 'Payroll period deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete payroll period',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payroll period.
     */
    public function process(PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        // Only allow processing if status is draft
        if ($payrollPeriod->status !== 'draft') {
            return response()->json([
                'message' => 'Payroll period is not in draft status'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->update([
                'status' => 'processing',
            ]);

            // Here you would typically generate payroll records for all active employees
            // This is a placeholder for the actual payroll processing logic

            DB::commit();

            return response()->json([
                'message' => 'Payroll period processing started successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process payroll period',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve payroll period.
     */
    public function approve(Request $request, PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        // Only allow approval if status is processing
        if ($payrollPeriod->status !== 'processing') {
            return response()->json([
                'message' => 'Payroll period is not in processing status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payroll period approved successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve payroll period',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payroll period as paid.
     */
    public function markAsPaid(PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        // Only allow marking as paid if status is approved
        if ($payrollPeriod->status !== 'approved') {
            return response()->json([
                'message' => 'Payroll period is not approved'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->update([
                'status' => 'paid',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payroll period marked as paid successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to mark payroll period as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel payroll period.
     */
    public function cancel(PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        // Only allow cancellation if status is draft or processing
        if (!in_array($payrollPeriod->status, ['draft', 'processing'])) {
            return response()->json([
                'message' => 'Payroll period cannot be cancelled'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payroll period cancelled successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel payroll period',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate payroll period totals.
     */
    public function calculateTotals(PayrollPeriod $payrollPeriod): JsonResponse
    {
        // Check if payroll period belongs to user's company
        if ($payrollPeriod->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payroll period not found'], 404);
        }

        try {
            $payrollPeriod->calculateTotals();

            return response()->json([
                'message' => 'Payroll period totals calculated successfully',
                'data' => $payrollPeriod->load(['company', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to calculate payroll period totals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current payroll period.
     */
    public function current(): JsonResponse
    {
        $currentPeriod = PayrollPeriod::with(['company', 'createdBy'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->first();

        return response()->json([
            'data' => $currentPeriod
        ]);
    }

    /**
     * Get upcoming payroll periods.
     */
    public function upcoming(): JsonResponse
    {
        $upcomingPeriods = PayrollPeriod::with(['company', 'createdBy'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '>', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $upcomingPeriods
        ]);
    }

    /**
     * Get overdue payroll periods.
     */
    public function overdue(): JsonResponse
    {
        $overduePeriods = PayrollPeriod::with(['company', 'createdBy'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('pay_date', '<', now()->toDateString())
            ->orderBy('pay_date', 'asc')
            ->get();

        return response()->json([
            'data' => $overduePeriods
        ]);
    }
}
