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
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function __construct(private readonly RolePermissionService $roles) {}

    public function index(Request $request): View
    {
        $roles = Role::query()->visibleTo($request->user())->with('permissions')->orderBy('name')->get();
        $selectedRole = $request->filled('role')
            ? $roles->firstWhere('id', $request->integer('role'))
            : $roles->first();

        return view('pages.permissions', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'rolePermissions' => $selectedRole?->permissions->pluck('key')->all() ?? [],
            'permissionGroups' => Permissions::all(),
            'users' => User::query()->with('assignedRole')->orderBy('name')->get(),
            'selectedUser' => $request->filled('user') ? User::query()->find($request->integer('user')) : null,
        ]);
    }

    public function syncRole(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        if ($role->isSuperAdmin()) {
            return back()->withErrors(['role' => 'The super admin role always has full access.']);
        }

        if (! $this->canManageRole($request->user(), $role)) {
            return back()->withErrors(['role' => 'You cannot modify this role.']);
        }

        $this->roles->syncPermissions($role, $this->assignablePermissionKeys($request->user(), $data['permissions'] ?? []));

        Activity::log('permissions_updated', 'Permissions updated for role '.$role->name, $role, [], 'roles');

        return redirect()->route('permissions.index', ['role' => $role->id])
            ->with('status', 'Role permissions updated.');
    }

    public function syncUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'allowed' => ['nullable', 'array'],
            'allowed.*' => ['string', Rule::in(Permissions::keys())],
            'denied' => ['nullable', 'array'],
            'denied.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        $this->roles->syncUserPermissions(
            $user,
            $this->assignablePermissionKeys($request->user(), $data['allowed'] ?? []),
            $data['denied'] ?? [],
        );

        Activity::log('permissions_updated', 'Direct permissions updated for '.$user->name, $user, [], 'users');

        return redirect()->route('permissions.index', ['user' => $user->id])
            ->with('status', 'User specific permissions updated.');
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
