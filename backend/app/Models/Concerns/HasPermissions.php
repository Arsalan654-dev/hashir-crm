<?php

namespace App\Models\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasPermissions
{
    protected ?Collection $permissionCache = null;

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->withPivot('effect')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        if ((bool) $this->is_super_admin) {
            return true;
        }

        return $this->assignedRole?->slug === Role::SUPER_ADMIN;
    }

    /**
     * @return Collection<int, string>
     */
    public function effectivePermissionKeys(): Collection
    {
        if ($this->permissionCache !== null) {
            return $this->permissionCache;
        }

        if ($this->isSuperAdmin()) {
            return $this->permissionCache = collect(Permissions::keys());
        }

        $rolePermissions = $this->assignedRole
            ? $this->assignedRole->permissions()->pluck('key')
            : collect();

        $direct = $this->directPermissions()->get();

        $allowed = $direct->where('pivot.effect', 'allow')->pluck('key');
        $denied = $direct->where('pivot.effect', 'deny')->pluck('key');

        return $this->permissionCache = $rolePermissions
            ->merge($allowed)
            ->unique()
            ->diff($denied)
            ->values();
    }

    public function hasPermission(string $key): bool
    {
        return $this->effectivePermissionKeys()->contains($key);
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function hasAnyPermission(array $keys): bool
    {
        return collect($keys)->contains(fn (string $key) => $this->hasPermission($key));
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function hasAllPermissions(array $keys): bool
    {
        return collect($keys)->every(fn (string $key) => $this->hasPermission($key));
    }

    public function flushPermissionCache(): void
    {
        $this->permissionCache = null;
    }
}
