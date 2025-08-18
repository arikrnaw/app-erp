<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkOSService
{
    public function authenticateUser(array $workosUser): User
    {
        // Find user by WorkOS ID
        $user = User::where('workos_id', $workosUser['id'])->first();

        if (!$user) {
            // Find user by email
            $user = User::where('email', $workosUser['email'])->first();

            if ($user) {
                // Update existing user with WorkOS ID
                $user->update([
                    'workos_id' => $workosUser['id'],
                    'auth_provider' => 'workos',
                    'auth_metadata' => [
                        'workos_profile' => $workosUser,
                        'connected_at' => now(),
                    ],
                    'email_verified_at' => now(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $workosUser['first_name'] . ' ' . $workosUser['last_name'],
                    'email' => $workosUser['email'],
                    'workos_id' => $workosUser['id'],
                    'auth_provider' => 'workos',
                    'auth_metadata' => [
                        'workos_profile' => $workosUser,
                        'connected_at' => now(),
                    ],
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'company_id' => 1, // Default company ID
                    'password' => Hash::make(Str::random(32)), // Random password for SSO users
                ]);

                // Assign default role if available
                $defaultRole = \App\Models\Role::where('slug', 'user')->first();
                if ($defaultRole) {
                    $user->assignRole($defaultRole);
                }
            }
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        return $user;
    }

    public function syncUserProfile(User $user, array $workosUser): void
    {
        $user->update([
            'name' => $workosUser['first_name'] . ' ' . $workosUser['last_name'],
            'email' => $workosUser['email'],
            'auth_metadata' => array_merge($user->auth_metadata ?? [], [
                'workos_profile' => $workosUser,
                'last_sync' => now(),
            ]),
        ]);
    }

    public function disconnectUser(User $user): void
    {
        $user->update([
            'workos_id' => null,
            'auth_provider' => 'email',
            'auth_metadata' => array_merge($user->auth_metadata ?? [], [
                'workos_disconnected_at' => now(),
            ]),
        ]);
    }
}
