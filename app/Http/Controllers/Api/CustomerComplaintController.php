<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerComplaint::where('company_id', Auth::user()->company_id)
            ->with(['customer', 'assignedTo', 'creator']);

        // Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('complaint_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Statistics
        $statistics = [
            'total' => CustomerComplaint::where('company_id', Auth::user()->company_id)->count(),
            'open' => CustomerComplaint::where('company_id', Auth::user()->company_id)->open()->count(),
            'resolved' => CustomerComplaint::where('company_id', Auth::user()->company_id)->resolved()->count(),
            'critical' => CustomerComplaint::where('company_id', Auth::user()->company_id)->byPriority('critical')->count(),
        ];

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $complaints,
            'statistics' => $statistics,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'complaint_type' => 'required|in:product_issue,service_issue,billing_issue,delivery_issue,technical_issue,quality_issue,communication_issue,other',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,in_progress,resolved,closed,escalated',
            'source' => 'required|in:phone,email,chat,social_media,in_person,website',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'product_name' => 'nullable|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'incident_date' => 'required|date',
            'expected_resolution' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $complaint = CustomerComplaint::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
            'company_id' => Auth::user()->company_id,
        ]);

        $complaint->load(['customer', 'assignedTo', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Customer complaint created successfully',
            'data' => $complaint,
        ], 201);
    }

    public function show($id)
    {
        $complaint = CustomerComplaint::where('company_id', Auth::user()->company_id)
            ->with(['customer', 'assignedTo', 'creator', 'company'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $complaint,
        ]);
    }

    public function update(Request $request, $id)
    {
        $complaint = CustomerComplaint::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'complaint_type' => 'sometimes|required|in:product_issue,service_issue,billing_issue,delivery_issue,technical_issue,quality_issue,communication_issue,other',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'status' => 'sometimes|required|in:open,in_progress,resolved,closed,escalated',
            'source' => 'sometimes|required|in:phone,email,chat,social_media,in_person,website',
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_email' => 'sometimes|required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'product_name' => 'nullable|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'incident_date' => 'sometimes|required|date',
            'expected_resolution' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'resolution_date' => 'nullable|date',
            'resolution_time_hours' => 'nullable|integer',
            'satisfaction_rating' => 'nullable|in:very_dissatisfied,dissatisfied,neutral,satisfied,very_satisfied',
            'customer_feedback' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $complaint->update($request->validated());
        $complaint->load(['customer', 'assignedTo', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Customer complaint updated successfully',
            'data' => $complaint,
        ]);
    }

    public function destroy($id)
    {
        $complaint = CustomerComplaint::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer complaint deleted successfully',
        ]);
    }

    public function statistics()
    {
        $companyId = Auth::user()->company_id;

        $statistics = [
            'total_complaints' => CustomerComplaint::where('company_id', $companyId)->count(),
            'open_complaints' => CustomerComplaint::where('company_id', $companyId)->open()->count(),
            'resolved_complaints' => CustomerComplaint::where('company_id', $companyId)->resolved()->count(),
            'critical_complaints' => CustomerComplaint::where('company_id', $companyId)->byPriority('critical')->count(),
            'high_priority_complaints' => CustomerComplaint::where('company_id', $companyId)->byPriority('high')->count(),
            'average_resolution_time' => CustomerComplaint::where('company_id', $companyId)
                ->whereNotNull('resolution_time_hours')
                ->avg('resolution_time_hours'),
            'satisfaction_breakdown' => CustomerComplaint::where('company_id', $companyId)
                ->whereNotNull('satisfaction_rating')
                ->selectRaw('satisfaction_rating, COUNT(*) as count')
                ->groupBy('satisfaction_rating')
                ->get(),
            'complaints_by_type' => CustomerComplaint::where('company_id', $companyId)
                ->selectRaw('complaint_type, COUNT(*) as count')
                ->groupBy('complaint_type')
                ->get(),
            'complaints_by_source' => CustomerComplaint::where('company_id', $companyId)
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
