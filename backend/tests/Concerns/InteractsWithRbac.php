<?php

namespace Tests\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

trait InteractsWithRbac
{
    protected function superAdmin(array $attributes = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => Role::SUPER_ADMIN],
            ['name' => 'Super Admin', 'is_system' => true, 'is_active' => true],
        );

        return User::factory()->create($attributes + [
            'role_id' => $role->id,
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<int, string>  $permissionKeys
     */
    protected function userWithPermissions(array $permissionKeys, array $attributes = []): User
    {
        $role = Role::query()->create([
            'name' => 'Role '.Str::random(6),
            'slug' => 'role-'.Str::lower(Str::random(8)),
            'is_active' => true,
        ]);

        $ids = collect($permissionKeys)->map(function (string $key): int {
            $permission = Permission::query()->firstOrCreate(
                ['key' => $key],
                [
                    'label' => $key,
                    'group' => 'Test',
                    'module' => Str::before($key, '.'),
                    'sort_order' => 0,
                ],
            );

            return $permission->id;
        });

        $role->permissions()->sync($ids->all());

        return User::factory()->create($attributes + [
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
