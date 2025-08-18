<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Permission::with(['roles'])
            ->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        // Filter by module
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $permissions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $permissions->items(),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
            ],
            'links' => $permissions->linkCollection(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100',
            'action' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['slug'] = Str::slug("{$validated['module']}.{$validated['action']}");

        $permission = Permission::create($validated);

        return response()->json([
            'message' => 'Permission created successfully',
            'permission' => $permission->load(['roles']),
        ], 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        // Check if permission belongs to user's company
        if ($permission->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'permission' => $permission->load(['roles']),
        ]);
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        // Check if permission belongs to user's company
        if ($permission->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100',
            'action' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug("{$validated['module']}.{$validated['action']}");

        $permission->update($validated);

        return response()->json([
            'message' => 'Permission updated successfully',
            'permission' => $permission->load(['roles']),
        ]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        // Check if permission belongs to user's company
        if ($permission->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return response()->json(['message' => 'Cannot delete permission assigned to roles'], 422);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully',
        ]);
    }

    public function modules(): JsonResponse
    {
        $modules = Permission::where('company_id', Auth::user()->company_id)
            ->distinct()
            ->pluck('module')
            ->values();

        return response()->json($modules);
    }

    public function actions(): JsonResponse
    {
        $actions = Permission::where('company_id', Auth::user()->company_id)
            ->distinct()
            ->pluck('action')
            ->values();

        return response()->json($actions);
    }

    public function statistics(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        $stats = [
            'total_permissions' => Permission::where('company_id', $companyId)->count(),
            'modules_count' => Permission::where('company_id', $companyId)->distinct('module')->count(),
            'actions_count' => Permission::where('company_id', $companyId)->distinct('action')->count(),
            'permissions_with_roles' => Permission::where('company_id', $companyId)
                ->whereHas('roles')
                ->count(),
        ];

        return response()->json($stats);
    }
}
