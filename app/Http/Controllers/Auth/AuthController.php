<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WorkOSService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected WorkOSService $workOSService;

    public function __construct(WorkOSService $workOSService)
    {
        $this->workOSService = $workOSService;
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        Auth::login($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load(['company', 'roles.permissions']),
        ]);
    }

    public function workosLogin(Request $request): JsonResponse
    {
        $request->validate([
            'workos_user' => 'required|array',
        ]);

        try {
            $user = $this->workOSService->authenticateUser($request->workos_user);
            Auth::login($user);

            return response()->json([
                'message' => 'WorkOS login successful',
                'user' => $user->load(['company', 'roles.permissions']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'WorkOS authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $request->company_id,
            'auth_provider' => 'email',
            'is_active' => true,
        ]);

        // Assign default role
        $defaultRole = \App\Models\Role::where('slug', 'user')->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }

        Auth::login($user);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user->load(['company', 'roles.permissions']),
        ], 201);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function me(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'user' => $user->load(['company', 'roles.permissions']),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load(['company', 'roles.permissions']),
        ]);
    }

    public function disconnectWorkOS(): JsonResponse
    {
        $user = Auth::user();

        if ($user->auth_provider !== 'workos') {
            return response()->json([
                'message' => 'Account is not connected to WorkOS',
            ], 400);
        }

        $this->workOSService->disconnectUser($user);

        return response()->json([
            'message' => 'WorkOS disconnected successfully',
        ]);
    }
}
