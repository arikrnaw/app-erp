<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AfterSalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AfterSalesServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = AfterSalesService::where('company_id', Auth::user()->company_id)
            ->with(['customer', 'assignedTechnician', 'creator']);

        // Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('service_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('assigned_technician')) {
            $query->where('assigned_technician', $request->assigned_technician);
        }

        if ($request->filled('is_warranty_covered')) {
            $query->where('is_warranty_covered', $request->is_warranty_covered);
        }

        // Statistics
        $statistics = [
            'total' => AfterSalesService::where('company_id', Auth::user()->company_id)->count(),
            'pending' => AfterSalesService::where('company_id', Auth::user()->company_id)->pending()->count(),
            'completed' => AfterSalesService::where('company_id', Auth::user()->company_id)->completed()->count(),
            'warranty_covered' => AfterSalesService::where('company_id', Auth::user()->company_id)->warrantyCovered()->count(),
        ];

        $services = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $services,
            'statistics' => $statistics,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'service_type' => 'required|in:warranty_service,repair_service,maintenance_service,installation_service,training_service,consultation_service,replacement_service,upgrade_service,other',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:pending,scheduled,in_progress,completed,cancelled,on_hold',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'product_name' => 'nullable|string|max:255',
            'product_model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'requested_date' => 'required|date',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'service_location' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'assigned_technician' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = AfterSalesService::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
            'company_id' => Auth::user()->company_id,
        ]);

        $service->load(['customer', 'assignedTechnician', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'After-sales service created successfully',
            'data' => $service,
        ], 201);
    }

    public function show($id)
    {
        $service = AfterSalesService::where('company_id', Auth::user()->company_id)
            ->with(['customer', 'assignedTechnician', 'creator', 'company'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function update(Request $request, $id)
    {
        $service = AfterSalesService::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'service_type' => 'sometimes|required|in:warranty_service,repair_service,maintenance_service,installation_service,training_service,consultation_service,replacement_service,upgrade_service,other',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'status' => 'sometimes|required|in:pending,scheduled,in_progress,completed,cancelled,on_hold',
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_email' => 'sometimes|required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'product_name' => 'nullable|string|max:255',
            'product_model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'requested_date' => 'sometimes|required|date',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'service_location' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'service_notes' => 'nullable|string',
            'work_performed' => 'nullable|string',
            'parts_used' => 'nullable|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'is_warranty_covered' => 'nullable|boolean',
            'completion_date' => 'nullable|date',
            'service_duration_hours' => 'nullable|integer|min:0',
            'service_quality' => 'nullable|in:poor,fair,good,excellent',
            'customer_signature' => 'nullable|string',
            'technician_signature' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
            'customer_satisfaction' => 'nullable|in:very_dissatisfied,dissatisfied,neutral,satisfied,very_satisfied',
            'customer_feedback' => 'nullable|string',
            'assigned_technician' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $service->update($request->validated());
        $service->load(['customer', 'assignedTechnician', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'After-sales service updated successfully',
            'data' => $service,
        ]);
    }

    public function destroy($id)
    {
        $service = AfterSalesService::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'After-sales service deleted successfully',
        ]);
    }

    public function statistics()
    {
        $companyId = Auth::user()->company_id;

        $statistics = [
            'total_services' => AfterSalesService::where('company_id', $companyId)->count(),
            'pending_services' => AfterSalesService::where('company_id', $companyId)->pending()->count(),
            'completed_services' => AfterSalesService::where('company_id', $companyId)->completed()->count(),
            'warranty_services' => AfterSalesService::where('company_id', $companyId)->warrantyCovered()->count(),
            'average_service_duration' => AfterSalesService::where('company_id', $companyId)
                ->whereNotNull('service_duration_hours')
                ->avg('service_duration_hours'),
            'total_revenue' => AfterSalesService::where('company_id', $companyId)
                ->whereNotNull('total_cost')
                ->sum('total_cost'),
            'satisfaction_breakdown' => AfterSalesService::where('company_id', $companyId)
                ->whereNotNull('customer_satisfaction')
                ->selectRaw('customer_satisfaction, COUNT(*) as count')
                ->groupBy('customer_satisfaction')
                ->get(),
            'services_by_type' => AfterSalesService::where('company_id', $companyId)
                ->selectRaw('service_type, COUNT(*) as count')
                ->groupBy('service_type')
                ->get(),
            'services_by_priority' => AfterSalesService::where('company_id', $companyId)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
