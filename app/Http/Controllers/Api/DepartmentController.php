<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Department::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $departments = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $departments->items(),
            'meta' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
                'from' => $departments->firstItem(),
                'to' => $departments->lastItem(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:departments,name',
                'code' => 'required|string|max:50|unique:departments,code',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $department = Department::create($validated);

            return response()->json([
                'message' => 'Department created successfully',
                'data' => $department
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json([
            'data' => $department->load('employees')
        ]);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
                'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $department->update($validated);

            return response()->json([
                'message' => 'Department updated successfully',
                'data' => $department
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Department $department): JsonResponse
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete department with existing employees'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }

    public function active(): JsonResponse
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $departments
        ]);
    }
}
