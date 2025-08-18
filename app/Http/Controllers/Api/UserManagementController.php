<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['company', 'roles'])
            ->where('company_id', Auth::user()->company_id);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by auth provider
        if ($request->has('auth_provider')) {
            $query->where('auth_provider', $request->auth_provider);
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'links' => $users->linkCollection(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
                }),
            ],
            'password' => 'required|string|min:8|confirmed',
            'auth_provider' => 'in:email,workos,google',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['password'] = Hash::make($validated['password']);
        $validated['auth_provider'] = $validated['auth_provider'] ?? 'email';
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load(['company', 'roles']),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        // Check if user belongs to the same company
        if ($user->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'user' => $user->load(['company', 'roles.permissions']),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // Check if user belongs to the same company
        if ($user->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
                })->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'auth_provider' => 'in:email,workos,google',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            $validated['password_changed_at'] = now();
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load(['company', 'roles']),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        // Check if user belongs to the same company
        if ($user->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deleting own account
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        // Check if user belongs to the same company
        if ($user->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deactivating own account
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot deactivate your own account'], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user->load(['company', 'roles']),
        ]);
    }

    public function assignRoles(Request $request, User $user): JsonResponse
    {
        // Check if user belongs to the same company
        if ($user->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->syncRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles assigned successfully',
            'user' => $user->load(['company', 'roles']),
        ]);
    }

    public function availableRoles(): JsonResponse
    {
        $roles = Role::where('company_id', Auth::user()->company_id)
            ->with('permissions')
            ->get();

        return response()->json($roles);
    }

    public function statistics(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        $stats = [
            'total_users' => User::where('company_id', $companyId)->count(),
            'active_users' => User::where('company_id', $companyId)->where('is_active', true)->count(),
            'inactive_users' => User::where('company_id', $companyId)->where('is_active', false)->count(),
            'users_with_roles' => User::where('company_id', $companyId)
                ->whereHas('roles')
                ->count(),
            'auth_providers' => User::where('company_id', $companyId)
                ->selectRaw('auth_provider, count(*) as count')
                ->groupBy('auth_provider')
                ->get(),
        ];

        return response()->json($stats);
    }
}
