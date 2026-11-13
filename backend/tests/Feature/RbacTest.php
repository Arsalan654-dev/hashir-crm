<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_super_admin_can_access_every_module(): void
    {
        $admin = $this->superAdmin();

        foreach (['/dashboard', '/users', '/roles', '/permissions', '/settings', '/activity-logs', '/employees', '/payroll', '/sales', '/leads', '/products', '/customers', '/invoices', '/whatsapp', '/chat', '/reports'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_user_without_permission_receives_403_page_with_modal_message(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(403);
        $response->assertSee('You cannot access this');
    }

    public function test_ajax_requests_receive_json_403(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($user)
            ->getJson('/users')
            ->assertStatus(403)
            ->assertJsonPath('message', '403 — You cannot access this.');
    }

    public function test_user_only_sees_permitted_pages_in_the_sidebar(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'sales.view', 'sales.create']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('/sales');
        $response->assertDontSee('Roles &amp; Access');
        $response->assertDontSee(url('/payroll'));
    }

    public function test_permissions_are_inherited_from_role_and_can_be_extended_per_user(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->assertFalse($user->hasPermission('users.view'));

        $permission = Permission::query()->firstOrCreate(
            ['key' => 'users.view'],
            ['label' => 'View users', 'group' => 'Test', 'module' => 'users', 'sort_order' => 0],
        );

        $user->directPermissions()->attach($permission->id, ['effect' => 'allow']);
        $user->flushPermissionCache();

        $this->assertTrue($user->fresh()->hasPermission('users.view'));
    }

    public function test_user_level_deny_overrides_role_permission(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'sales.view']);

        $this->assertTrue($user->hasPermission('sales.view'));

        $permission = Permission::query()->where('key', 'sales.view')->firstOrFail();
        $user->directPermissions()->attach($permission->id, ['effect' => 'deny']);
        $user->flushPermissionCache();

        $this->assertFalse($user->fresh()->hasPermission('sales.view'));

        $this->actingAs($user->fresh())->get('/sales')->assertStatus(403);
    }

    public function test_deactivated_user_is_logged_out(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('auth-login'));
        $this->assertGuest();
    }

    public function test_role_permissions_can_be_updated_by_super_admin(): void
    {
        $admin = $this->superAdmin();
        $role = Role::query()->create([
            'name' => 'Content Team',
            'slug' => 'content-team',
            'is_active' => true,
        ]);

        foreach (['dashboard.view', 'products.view'] as $key) {
            Permission::query()->firstOrCreate(
                ['key' => $key],
                ['label' => $key, 'group' => 'Test', 'module' => Str::before($key, '.'), 'sort_order' => 0],
            );
        }

        $this->actingAs($admin)->post('/permissions/roles/'.$role->id, [
            'permissions' => ['dashboard.view', 'products.view'],
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'products.view'],
            $role->fresh()->permissions()->pluck('key')->all(),
        );
    }

    public function test_super_admin_can_assign_a_role_to_a_user(): void
    {
        $admin = $this->superAdmin();
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Sales Person',
            'slug' => 'sales-person-test',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/users/'.$user->id.'/role', ['role_id' => $role->id])
            ->assertRedirect();

        $this->assertSame($role->id, $user->fresh()->role_id);
    }
}
