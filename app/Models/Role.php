<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withTimestamps();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('slug', $permission)->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('slug', $permissions)->exists();
    }

    public function hasAllPermissions(array $permissions): bool
    {
        $permissionCount = $this->permissions()->whereIn('slug', $permissions)->count();
        return $permissionCount === count($permissions);
    }

    public function assignPermission(Permission $permission): void
    {
        if (!$this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
        }
    }

    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}
