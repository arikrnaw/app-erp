<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PositionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Position::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('position_code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $positions = $query->orderBy('title')->paginate($perPage);

        return response()->json([
            'data' => $positions->items(),
            'meta' => [
                'current_page' => $positions->currentPage(),
                'last_page' => $positions->lastPage(),
                'per_page' => $positions->perPage(),
                'total' => $positions->total(),
                'from' => $positions->firstItem(),
                'to' => $positions->lastItem(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255|unique:positions,title',
                'position_code' => 'required|string|max:50|unique:positions,position_code',
                'description' => 'nullable|string',
                'department_id' => 'required|exists:departments,id',
                'job_level' => 'nullable|string|max:100',
                'is_active' => 'boolean',
            ]);

            $position = Position::create($validated);

            return response()->json([
                'message' => 'Position created successfully',
                'data' => $position
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(Position $position): JsonResponse
    {
        return response()->json([
            'data' => $position->load('employees')
        ]);
    }

    public function update(Request $request, Position $position): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255|unique:positions,title,' . $position->id,
                'position_code' => 'required|string|max:50|unique:positions,position_code,' . $position->id,
                'description' => 'nullable|string',
                'department_id' => 'required|exists:departments,id',
                'job_level' => 'nullable|string|max:100',
                'is_active' => 'boolean',
            ]);

            $position->update($validated);

            return response()->json([
                'message' => 'Position updated successfully',
                'data' => $position
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Position $position): JsonResponse
    {
        // Check if position has employees
        if ($position->employees()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete position with existing employees'
            ], 422);
        }

        $position->delete();

        return response()->json([
            'message' => 'Position deleted successfully'
        ]);
    }

    public function active(): JsonResponse
    {
        $positions = Position::where('is_active', true)
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $positions
        ]);
    }
}
