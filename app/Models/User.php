<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'workos_id',
        'auth_provider',
        'auth_metadata',
        'avatar',
        'company_id',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'email_verification_token',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'workos_id',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'auth_metadata' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('slug', $permissions);
            })
            ->exists();
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('slug');
            })
            ->unique()
            ->values();

        return count(array_intersect($permissions, $userPermissions->toArray())) === count($permissions);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        if (!$this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    /**
     * Get all permissions for the user.
     */
    public function getAllPermissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique('id');
    }
}
