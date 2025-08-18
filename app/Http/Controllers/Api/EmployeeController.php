<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['company', 'department', 'position', 'supervisor']);
        
        // For now, we'll get all employees since User doesn't have company_id
        // In a real application, you might want to add company_id to User model
        // or implement a different way to scope employees by company

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by position
        if ($request->has('position_id') && $request->position_id) {
            $query->where('position_id', $request->position_id);
        }

        // Filter by supervisor
        if ($request->has('supervisor_id') && $request->supervisor_id) {
            $query->where('supervisor_id', $request->supervisor_id);
        }

        // Filter by employment status
        if ($request->has('employment_status') && $request->employment_status) {
            $query->where('employment_status', $request->employment_status);
        }

        // Filter by employment type
        if ($request->has('employment_type') && $request->employment_type) {
            $query->where('employment_type', $request->employment_type);
        }

        // Filter by gender
        if ($request->has('gender') && $request->gender) {
            $query->where('gender', $request->gender);
        }

        // Filter by work location
        if ($request->has('is_remote') && $request->is_remote !== '') {
            $query->where('is_remote', $request->boolean('is_remote'));
        }

        // Filter by active status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by hire date range
        if ($request->has('hire_date_from') && $request->hire_date_from) {
            $query->where('hire_date', '>=', $request->hire_date_from);
        }

        if ($request->has('hire_date_to') && $request->hire_date_to) {
            $query->where('hire_date', '<=', $request->hire_date_to);
        }

        // Sort
        $sortField = $request->get('sort_field', 'first_name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $employees = $query->paginate($perPage);

        return response()->json([
            'data' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_number' => 'required|string|max:50|unique:employees,employee_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_routing_number' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'employment_status' => 'required|in:active,inactive,terminated,resigned,retired',
            'employment_type' => 'required|in:full_time,part_time,contract,intern,temporary',
            'base_salary' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'pay_frequency' => 'required|in:weekly,bi_weekly,monthly,quarterly,yearly',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'working_hours_per_day' => 'nullable|numeric|min:0|max:24',
            'working_days_per_week' => 'nullable|integer|min:1|max:7',
            'work_location' => 'nullable|string|max:255',
            'is_remote' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // For now, we'll skip company validation since User doesn't have company_id
        // In a real application, you should implement proper company scoping
        
        try {
            DB::beginTransaction();

            $employee = Employee::create([
                'company_id' => 1, // Default company ID for now
                'employee_number' => $request->employee_number,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'national_id' => $request->national_id,
                'tax_number' => $request->tax_number,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_routing_number' => $request->bank_routing_number,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'supervisor_id' => $request->supervisor_id,
                'hire_date' => $request->hire_date,
                'contract_start_date' => $request->contract_start_date,
                'contract_end_date' => $request->contract_end_date,
                'employment_status' => $request->employment_status,
                'employment_type' => $request->employment_type,
                'base_salary' => $request->base_salary,
                'currency' => $request->currency,
                'pay_frequency' => $request->pay_frequency,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'working_hours_per_day' => $request->working_hours_per_day,
                'working_days_per_week' => $request->working_days_per_week,
                'work_location' => $request->work_location,
                'is_remote' => $request->boolean('is_remote', false),
                'user_id' => $request->user_id,
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Employee created successfully',
                'data' => $employee->load(['company', 'department', 'position', 'supervisor'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): JsonResponse
    {
        // For now, we'll skip company validation since User doesn't have company_id

        $employee->load([
            'company', 
            'department', 
            'position', 
            'supervisor', 
            'subordinates',
            'attendanceRecords',
            'leaveRequests',
            'payrollRecords',
            'performanceReviews',
            'employeeBenefits',
            'employeeDocuments'
        ]);

        return response()->json([
            'data' => $employee
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        // Check if employee belongs to user's company
        if ($employee->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_number' => 'required|string|max:50|unique:employees,employee_number,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_routing_number' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'employment_status' => 'required|in:active,inactive,terminated,resigned,retired',
            'employment_type' => 'required|in:full_time,part_time,contract,intern,temporary',
            'base_salary' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'pay_frequency' => 'required|in:weekly,bi_weekly,monthly,quarterly,yearly',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'working_hours_per_day' => 'nullable|numeric|min:0|max:24',
            'working_days_per_week' => 'nullable|integer|min:1|max:7',
            'work_location' => 'nullable|string|max:255',
            'is_remote' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // For now, we'll skip company validation since User doesn't have company_id

        try {
            DB::beginTransaction();

            $employee->update([
                'employee_number' => $request->employee_number,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'national_id' => $request->national_id,
                'tax_number' => $request->tax_number,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_routing_number' => $request->bank_routing_number,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'supervisor_id' => $request->supervisor_id,
                'hire_date' => $request->hire_date,
                'contract_start_date' => $request->contract_start_date,
                'contract_end_date' => $request->contract_end_date,
                'employment_status' => $request->employment_status,
                'employment_type' => $request->employment_type,
                'base_salary' => $request->base_salary,
                'currency' => $request->currency,
                'pay_frequency' => $request->pay_frequency,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'working_hours_per_day' => $request->working_hours_per_day,
                'working_days_per_week' => $request->working_days_per_week,
                'work_location' => $request->work_location,
                'is_remote' => $request->boolean('is_remote', $employee->is_remote),
                'user_id' => $request->user_id,
                'is_active' => $request->boolean('is_active', $employee->is_active),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Employee updated successfully',
                'data' => $employee->load(['company', 'department', 'position', 'supervisor'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        // For now, we'll skip company validation since User doesn't have company_id

        // Check if employee has subordinates
        if ($employee->subordinates()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete employee with subordinates'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $employee->delete();

            DB::commit();

            return response()->json([
                'message' => 'Employee deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active employees.
     */
    public function active(): JsonResponse
    {
        $employees = Employee::with(['company', 'department', 'position'])
            ->where('company_id', 1) // Default company ID for now
            ->where('is_active', true)
            ->where('employment_status', 'active')
            ->orderBy('first_name', 'asc')
            ->get();

        return response()->json([
            'data' => $employees
        ]);
    }

    /**
     * Get employee statistics.
     */
    public function statistics(): JsonResponse
    {
        // For now, we'll use company_id = 1 since User doesn't have company_id
        $companyId = 1;

        $statistics = [
            'total_employees' => Employee::where('company_id', $companyId)->count(),
            'active_employees' => Employee::where('company_id', $companyId)
                ->where('is_active', true)
                ->where('employment_status', 'active')
                ->count(),
            'new_hires_this_month' => Employee::where('company_id', $companyId)
                ->where('hire_date', '>=', now()->startOfMonth())
                ->count(),
            'contracts_expiring_soon' => Employee::where('company_id', $companyId)
                ->where('contract_end_date', '<=', now()->addDays(30))
                ->where('contract_end_date', '>=', now())
                ->count(),
            'remote_employees' => Employee::where('company_id', $companyId)
                ->where('is_remote', true)
                ->where('is_active', true)
                ->count(),
            'onsite_employees' => Employee::where('company_id', $companyId)
                ->where('is_remote', false)
                ->where('is_active', true)
                ->count(),
        ];

        return response()->json([
            'data' => $statistics
        ]);
    }

    /**
     * Toggle employee active status.
     */
    public function toggleStatus(Employee $employee): JsonResponse
    {
        // For now, we'll skip company validation since User doesn't have company_id

        try {
            $employee->update([
                'is_active' => !$employee->is_active
            ]);

            return response()->json([
                'message' => 'Employee status updated successfully',
                'data' => $employee->load(['company', 'department', 'position'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update employee status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employees by department.
     */
    public function byDepartment($departmentId): JsonResponse
    {
        $employees = Employee::with(['company', 'department', 'position'])
            ->where('company_id', 1) // Default company ID for now
            ->where('department_id', $departmentId)
            ->where('is_active', true)
            ->orderBy('first_name', 'asc')
            ->get();

        return response()->json([
            'data' => $employees
        ]);
    }

    /**
     * Get employees by position.
     */
    public function byPosition($positionId): JsonResponse
    {
        $employees = Employee::with(['company', 'department', 'position'])
            ->where('company_id', 1) // Default company ID for now
            ->where('position_id', $positionId)
            ->where('is_active', true)
            ->orderBy('first_name', 'asc')
            ->get();

        return response()->json([
            'data' => $employees
        ]);
    }
}
