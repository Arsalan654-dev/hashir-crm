<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\RolePermissionService;
use App\Support\Activity;
use App\Support\Permissions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct(private readonly RolePermissionService $roles) {}

    public function index(Request $request): View
    {
        $roles = Role::query()
            ->visibleTo($request->user())
            ->withCount(['users', 'permissions'])
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('pages.roles', [
            'roles' => $roles,
            'permissionGroups' => Permissions::all(),
            'rolePermissions' => $roles->mapWithKeys(
                fn (Role $role) => [$role->id => $role->permissions->pluck('key')->all()]
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:30'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? 'primary',
            'is_system' => false,
            'is_active' => true,
            'organization_id' => $request->user()->organization_id,
        ]);

        $this->roles->syncPermissions($role, $this->assignablePermissionKeys($request->user(), $data['permissions'] ?? []));

        Activity::log('created', 'Role created: '.$role->name, $role, [], 'roles');

        return back()->with('status', 'Role created successfully.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (! $this->canManageRole($request->user(), $role)) {
            return back()->withErrors(['role' => 'You cannot modify this role.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:30'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? $role->color,
        ]);

        if (! $role->isSuperAdmin()) {
            $this->roles->syncPermissions($role, $this->assignablePermissionKeys($request->user(), $data['permissions'] ?? []));
        }

        Activity::log('updated', 'Role updated: '.$role->name, $role, [], 'roles');

        return back()->with('status', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['role' => 'System roles cannot be deleted.']);
        }

        if (! $this->canManageRole($request->user(), $role)) {
            return back()->withErrors(['role' => 'You cannot delete this role.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Reassign users before deleting this role.']);
        }

        Activity::log('deleted', 'Role deleted: '.$role->name, null, ['role' => $role->slug], 'roles');
        $role->delete();

        return back()->with('status', 'Role deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $counter = 2;

        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function canManageRole(?User $actor, Role $role): bool
    {
        if ($actor === null) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($role->is_system || $role->organization_id === null) {
            return false;
        }

        return (int) $role->organization_id === (int) $actor->organization_id;
    }

    /**
     * Organization users can only grant permissions they hold themselves.
     *
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    private function assignablePermissionKeys(User $actor, array $keys): array
    {
        if ($actor->isSuperAdmin()) {
            return $keys;
        }

        return array_values(array_intersect($keys, $actor->effectivePermissionKeys()->all()));
    }
}
