<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Activity;
use App\Support\Permissions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['assignedRole:id,name,slug', 'department:id,name', 'designationRecord:id,title'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('username', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('employee_code', 'like', $search);
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role_id', $request->integer('role')))
            ->when($request->filled('department'), fn ($query) => $query->where('department_id', $request->integer('department')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pages.users', [
            'users' => $users,
            'roles' => Role::query()->visibleTo($request->user())->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
            'superAdmins' => User::query()->where('is_super_admin', true)->count(),
            'permissionGroups' => Permissions::all(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('pages.users-form', [
            'user' => new User,
            'roles' => Role::query()->visibleTo($request->user())->orderBy('name')->get(),
            'departments' => Department::query()->with('designations')->orderBy('name')->get(),
            'designations' => Designation::query()->orderBy('title')->get(),
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'permissionGroups' => Permissions::all(),
            'userPermissions' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $orgId = $request->user()->organization_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', $this->orgScopedExists('departments', $orgId)],
            'designation_id' => ['nullable', $this->orgScopedExists('designations', $orgId)],
            'reports_to' => ['nullable', $this->orgScopedExists('users', $orgId)],
            'employment_type' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:users,employee_code'],
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        $role = Role::query()->findOrFail($data['role_id']);

        if (! $this->canAssignRole($request->user(), $role)) {
            return back()->withErrors(['role_id' => 'You cannot assign this role.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'password' => $data['password'],
            'role' => $role->isSuperAdmin() ? 'admin' : 'user',
            'role_id' => $role->id,
            'department_id' => $data['department_id'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'reports_to' => $data['reports_to'] ?? null,
            'employment_type' => $data['employment_type'] ?? 'full-time',
            'joining_date' => $data['joining_date'] ?? now()->toDateString(),
            'basic_salary' => $data['basic_salary'] ?? 0,
            'commission_rate' => $data['commission_rate'] ?? 0,
            'employee_code' => $data['employee_code'] ?? 'EMP-'.str_pad((string) (User::query()->count() + 1), 4, '0', STR_PAD_LEFT),
            'is_active' => $request->boolean('is_active', true),
            'is_super_admin' => $role->isSuperAdmin(),
            'email_verified_at' => now(),
            'avatar' => 'assets/images/users/avatar-'.random_int(1, 8).'.jpg',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->forceFill(['avatar' => $path])->save();
        }

        $this->syncDirectPermissions($user, $this->assignablePermissionKeys($request->user(), $request->input('permissions', [])));

        Activity::log('created', 'User created: '.$user->name, $user, [], 'users');

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(Request $request, User $user): View
    {
        return view('pages.users-form', [
            'user' => $user,
            'roles' => Role::query()->visibleTo($request->user())->orderBy('name')->get(),
            'departments' => Department::query()->with('designations')->orderBy('name')->get(),
            'designations' => Designation::query()->orderBy('title')->get(),
            'managers' => User::query()->where('is_active', true)->whereKeyNot($user->id)->orderBy('name')->get(['id', 'name']),
            'permissionGroups' => Permissions::all(),
            'userPermissions' => $user->directPermissions()->pluck('key'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $orgId = $request->user()->organization_id;

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_id' => ['nullable', 'exists:roles,id'],
            'department_id' => ['nullable', $this->orgScopedExists('departments', $orgId)],
            'designation_id' => ['nullable', $this->orgScopedExists('designations', $orgId)],
            'reports_to' => ['nullable', $this->orgScopedExists('users', $orgId)],
            'employment_type' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_code')->ignore($user->id)],
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        unset($data['password']);

        if (isset($data['role_id'])) {
            $role = Role::query()->find($data['role_id']);

            if ($role === null || ! $this->canAssignRole($request->user(), $role)) {
                return back()->withErrors(['role_id' => 'You cannot assign this role.'])->withInput();
            }

            $data['role'] = $role->isSuperAdmin() ? 'admin' : 'user';
            $data['is_super_admin'] = $role->isSuperAdmin();
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data)->save();
        $user->flushPermissionCache();

        if ($request->has('permissions')) {
            $this->syncDirectPermissions($user, $this->assignablePermissionKeys($request->user(), $request->input('permissions', [])));
        }

        Activity::log('updated', 'User updated: '.$user->name, $user, ['changed' => array_keys($data)], 'users');

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function toggle(User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        if ($user->isSuperAdmin() && ! Auth::user()->isSuperAdmin()) {
            return back()->withErrors(['user' => 'You cannot modify the super admin account.']);
        }
        $user->update(['is_active' => ! $user->is_active]);

        Activity::log('toggled', ($user->is_active ? 'Activated' : 'Deactivated').' user: '.$user->name, $user, [], 'users');

        return back()->with('status', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::query()->findOrFail($data['role_id']);

        if (! $this->canAssignRole($request->user(), $role)) {
            return back()->withErrors(['role_id' => 'You cannot assign this role.']);
        }

        $user->forceFill([
            'role_id' => $role->id,
            'role' => $role->isSuperAdmin() ? 'admin' : 'user',
            'is_super_admin' => $role->isSuperAdmin(),
        ])->save();

        $user->flushPermissionCache();

        Activity::log('role_assigned', $role->name.' assigned to '.$user->name, $user, ['role' => $role->slug], 'users');

        return back()->with('status', 'Role assigned successfully.');
    }

    public function syncPermissions(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::keys())],
        ]);

        $this->syncDirectPermissions($user, $this->assignablePermissionKeys($request->user(), $data['permissions'] ?? []));

        Activity::log('permissions_updated', 'Permissions updated for '.$user->name, $user, [], 'users');

        return back()->with('status', 'Permissions updated for '.$user->name.'.');
    }

    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $original = Auth::id();

        if ($original === $user->id) {
            return back()->withErrors(['user' => 'You are already logged in as this user.']);
        }

        Activity::log('impersonated', 'Impersonated '.$user->name, $user, [], 'users');

        $request->session()->put('impersonator_id', $original !== null ? $original : null);
        Auth::login($user);

        return redirect()->route('home')->with('status', 'You are now viewing the dashboard as '.$user->name.'.');
    }

    public function stopImpersonating(Request $request): RedirectResponse
    {
        $originalId = $request->session()->pull('impersonator_id');

        if ($originalId !== null) {
            $original = User::query()->find($originalId);

            if ($original !== null) {
                Auth::login($original);

                return redirect()->route('users.index')->with('status', 'Impersonation stopped.');
            }
        }

        return redirect()->route('home');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->is_super_admin) {
            return back()->withErrors(['user' => 'The super admin account cannot be deleted.']);
        }

        Activity::log('deleted', 'User deleted: '.$user->name, null, ['user_id' => $user->id], 'users');
        $user->delete();

        return back()->with('status', 'User deleted.');
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function syncDirectPermissions(User $user, array $keys): void
    {
        $ids = Permission::query()->whereIn('key', $keys)->pluck('id');

        $user->directPermissions()->sync($ids->mapWithKeys(fn (int $id) => [$id => ['effect' => 'allow']])->all());

        $user->flushPermissionCache();
    }

    /**
     * Existence rule that keeps organization users within their own tenant.
     */
    private function orgScopedExists(string $table, ?int $organizationId): Exists
    {
        return Rule::exists($table, 'id')
            ->when($organizationId !== null, fn (Exists $rule) => $rule->where('organization_id', $organizationId));
    }

    private function canAssignRole(?User $actor, Role $role): bool
    {
        if ($actor === null) {
            return false;
        }

        if ($role->isSuperAdmin()) {
            return $actor->isSuperAdmin();
        }

        if ($actor->isSuperAdmin() || $actor->organization_id === null) {
            return true;
        }

        return $role->organization_id === null
            || (int) $role->organization_id === (int) $actor->organization_id;
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
