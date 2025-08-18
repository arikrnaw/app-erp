<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Role::with(['permissions', 'users'])
            ->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by system roles
        if ($request->has('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        $roles = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
            'links' => $roles->linkCollection(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_system'] = false;

        $role = Role::create($validated);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load(['permissions', 'users']),
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        // Check if role belongs to user's company
        if ($role->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'role' => $role->load(['permissions', 'users']),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        // Check if role belongs to user's company
        if ($role->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent editing system roles
        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be modified'], 422);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                })->ignore($role->id),
            ],
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $role->update($validated);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role->load(['permissions', 'users']),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        // Check if role belongs to user's company
        if ($role->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deleting system roles
        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be deleted'], 422);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    public function statistics(): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'total_roles' => Role::where('company_id', $companyId)->count(),
            'system_roles' => Role::where('company_id', $companyId)->where('is_system', true)->count(),
            'custom_roles' => Role::where('company_id', $companyId)->where('is_system', false)->count(),
            'roles_with_users' => Role::where('company_id', $companyId)
                ->whereHas('users')
                ->count(),
        ];

        return response()->json($stats);
    }
}
