<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class RolePermissionService
{
    /**
     * @param  array<int, string>  $permissionKeys
     */
    public function syncPermissions(Role $role, array $permissionKeys): void
    {
        $ids = Permission::query()->whereIn('key', $permissionKeys)->pluck('id');

        $role->permissions()->sync($ids->all());

        $role->users->each->flushPermissionCache();
    }

    /**
     * @param  array<int, string>  $allowedKeys
     * @param  array<int, string>  $deniedKeys
     */
    public function syncUserPermissions(User $user, array $allowedKeys, array $deniedKeys = []): void
    {
        $allowed = Permission::query()->whereIn('key', $allowedKeys)->pluck('id')
            ->mapWithKeys(fn (int $id) => [$id => ['effect' => 'allow']]);
        $denied = Permission::query()->whereIn('key', $deniedKeys)->pluck('id')
            ->mapWithKeys(fn (int $id) => [$id => ['effect' => 'deny']]);

        $user->directPermissions()->sync($allowed->merge($denied)->all());
        $user->flushPermissionCache();
    }
}
