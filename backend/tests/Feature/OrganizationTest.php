<?php

namespace Tests\Feature;

use App\Mail\OrganizationCredentialsMail;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Services\MailSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_super_admin_can_create_organization_with_owner_and_credentials_are_emailed(): void
    {
        Mail::fake();

        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post('/organizations', [
            'name' => 'Spice Route Restaurant',
            'business_type' => 'restaurant',
            'email' => 'info@spiceroute.test',
            'phone' => '+971500000000',
            'currency' => 'AED',
            'billing_cycle' => 'monthly',
            'plan_name' => 'Restaurant Starter',
            'plan_amount' => 199,
            'owner_name' => 'Owner One',
            'owner_email' => 'owner@spiceroute.test',
            'owner_password' => 'SecretPass123',
        ]);

        $organization = Organization::query()->where('name', 'Spice Route Restaurant')->first();

        $this->assertNotNull($organization);
        $this->assertSame('restaurant', $organization->business_type);
        $response->assertRedirect(route('organizations.show', $organization));

        $owner = $organization->owner;
        $this->assertNotNull($owner);
        $this->assertSame($organization->id, $owner->organization_id);
        $this->assertNotNull($owner->role_id);
        $this->assertSame('owner@spiceroute.test', $owner->email);

        Mail::assertSent(OrganizationCredentialsMail::class, fn (OrganizationCredentialsMail $mail): bool => $mail->hasTo('owner@spiceroute.test'));
    }

    public function test_organization_creation_requires_owner_details(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post('/organizations', [
            'name' => 'No Owner Biz',
            'business_type' => 'general',
            'billing_cycle' => 'monthly',
        ]);

        $response->assertSessionHasErrors(['owner_name', 'owner_email']);
        $this->assertDatabaseMissing('organizations', ['name' => 'No Owner Biz']);
    }

    public function test_business_data_is_scoped_to_the_users_organization(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        Product::factory()->create(['organization_id' => $organizationA->id, 'name' => 'Alpha Burger']);
        Product::factory()->create(['organization_id' => $organizationB->id, 'name' => 'Beta Pizza']);

        $userA = $this->userWithPermissions(
            ['dashboard.view', 'products.view'],
            ['organization_id' => $organizationA->id],
        );

        $this->actingAs($userA);

        $visible = Product::query()->pluck('name');
        $this->assertTrue($visible->contains('Alpha Burger'));
        $this->assertFalse($visible->contains('Beta Pizza'));

        $admin = $this->superAdmin();
        $this->actingAs($admin);

        $this->assertSame(2, Product::query()->count());
    }

    public function test_new_records_are_automatically_assigned_to_the_users_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->userWithPermissions(
            ['dashboard.view', 'products.create'],
            ['organization_id' => $organization->id],
        );

        $this->actingAs($user);

        $product = Product::factory()->create(['name' => 'Auto Assigned Item']);

        $this->assertSame($organization->id, $product->organization_id);
    }

    public function test_blocked_organization_user_is_redirected_to_billing(): void
    {
        $organization = Organization::factory()->crmBlocked()->create();
        $user = $this->userWithPermissions(
            ['dashboard.view', 'billing.view'],
            ['organization_id' => $organization->id],
        );

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('billing.index'));
    }

    public function test_unpaid_organization_cannot_access_premium_features_but_paid_can(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->userWithPermissions(
            ['dashboard.view', 'billing.view', 'products.view', 'products.create'],
            ['organization_id' => $organization->id],
        );

        $this->actingAs($user)->get('/products/create')->assertRedirect(route('billing.index'));

        $organization->update(['payment_status' => 'paid', 'paid_until' => now()->addMonth()]);

        $this->actingAs($user->fresh())->get('/products/create')->assertOk();
    }

    public function test_recording_a_payment_activates_the_organization(): void
    {
        $admin = $this->superAdmin();
        $organization = Organization::factory()->create(['billing_cycle' => 'monthly', 'plan_amount' => 150]);

        $this->actingAs($admin)->post(route('organizations.payments.store', $organization), [
            'amount' => 150,
            'method' => 'bank_transfer',
            'status' => 'paid',
            'period_start' => '2026-09-01',
        ])->assertRedirect();

        $organization->refresh();

        $this->assertSame('paid', $organization->payment_status);
        $this->assertSame('2026-10-01', $organization->paid_until->toDateString());
        $this->assertDatabaseHas('subscription_payments', [
            'organization_id' => $organization->id,
            'status' => 'paid',
        ]);
    }

    public function test_pending_payment_can_be_approved(): void
    {
        $admin = $this->superAdmin();
        $organization = Organization::factory()->create();
        $payment = SubscriptionPayment::factory()->pending()->create(['organization_id' => $organization->id]);

        $this->actingAs($admin)
            ->post(route('organizations.payments.approve', [$organization, $payment]))
            ->assertRedirect();

        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame('paid', $organization->refresh()->payment_status);
    }

    public function test_super_admin_can_block_and_unblock_organization_crm_and_bot(): void
    {
        $admin = $this->superAdmin();
        $organization = Organization::factory()->create();

        $this->actingAs($admin)->post(route('organizations.toggle-crm', $organization))->assertRedirect();
        $this->assertTrue($organization->refresh()->crm_blocked);

        $this->actingAs($admin)->post(route('organizations.toggle-crm', $organization))->assertRedirect();
        $this->assertFalse($organization->refresh()->crm_blocked);

        $this->actingAs($admin)->post(route('organizations.toggle-bot', $organization))->assertRedirect();
        $this->assertTrue($organization->refresh()->bot_blocked);
    }

    public function test_email_settings_are_saved_with_encrypted_password_and_applied(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('settings.email.update'), [
            'mail' => [
                'enabled' => '1',
                'mailer' => 'smtp',
                'host' => 'smtp.hostinger.com',
                'port' => 465,
                'username' => 'no-reply@example.com',
                'password' => 'SuperSecret',
                'encryption' => 'ssl',
                'from_address' => 'no-reply@example.com',
                'from_name' => 'Cyprus Digital',
            ],
            'billing' => [
                'bank_details' => 'IBAN 123456',
                'instructions' => 'Pay within 7 days',
            ],
        ])->assertRedirect();

        $this->assertSame('smtp.hostinger.com', Setting::getValue('mail.host'));
        $this->assertSame('1', Setting::getValue('mail.enabled'));
        $this->assertSame('IBAN 123456', Setting::getValue('billing.bank_details'));
        $this->assertNotSame('SuperSecret', Setting::getValue('mail.password'));
        $this->assertSame('SuperSecret', MailSettingsService::password());

        MailSettingsService::apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.hostinger.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('SuperSecret', config('mail.mailers.smtp.password'));
    }

    public function test_organization_owner_can_view_billing_page(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->userWithPermissions(
            ['dashboard.view', 'billing.view'],
            ['organization_id' => $organization->id],
        );

        $this->actingAs($user)
            ->get('/billing')
            ->assertOk()
            ->assertSee($organization->name)
            ->assertSee('Submit a Payment');
    }

    public function test_organization_admin_cannot_assign_super_admin_role(): void
    {
        $superAdminRole = Role::query()->create([
            'name' => 'Super Admin',
            'slug' => Role::SUPER_ADMIN,
            'is_system' => true,
            'is_active' => true,
        ]);

        $organization = Organization::factory()->create();
        $admin = $this->userWithPermissions(
            ['dashboard.view', 'users.view', 'users.create'],
            ['organization_id' => $organization->id],
        );

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Sneaky User',
            'username' => 'sneaky',
            'email' => 'sneaky@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_id' => $superAdminRole->id,
        ]);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.test']);
    }

    public function test_organization_admin_cannot_grant_permissions_they_do_not_hold(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->userWithPermissions(
            ['dashboard.view', 'users.view', 'permissions.manage'],
            ['organization_id' => $organization->id],
        );

        $staff = $this->userWithPermissions(['dashboard.view'], ['organization_id' => $organization->id]);

        $this->actingAs($admin)
            ->post(route('permissions.user.sync', $staff), [
                'allowed' => ['organizations.view'],
            ])
            ->assertRedirect();

        $this->assertFalse($staff->fresh()->hasPermission('organizations.view'));
    }

    public function test_organization_admin_cannot_modify_system_roles(): void
    {
        $systemRole = Role::query()->create([
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        $organization = Organization::factory()->create();
        $admin = $this->userWithPermissions(
            ['dashboard.view', 'roles.view', 'roles.update'],
            ['organization_id' => $organization->id],
        );

        $response = $this->actingAs($admin)->put(route('roles.update', $systemRole), [
            'name' => 'Hacked Admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame('Admin', $systemRole->refresh()->name);
    }

    public function test_super_admin_can_view_organization_pages(): void
    {
        $admin = $this->superAdmin();
        $organization = Organization::factory()->create();

        $this->actingAs($admin)->get('/organizations')->assertOk()->assertSee($organization->name);
        $this->actingAs($admin)->get('/organizations/create')->assertOk()->assertSee('Owner Account');
        $this->actingAs($admin)->get(route('organizations.show', $organization))->assertOk()->assertSee($organization->name);
        $this->actingAs($admin)->get(route('organizations.edit', $organization))->assertOk();
        $this->actingAs($admin)->get('/settings/email')->assertOk()->assertSee('SMTP');
    }

    public function test_impersonation_banner_allows_returning_to_original_account(): void
    {
        $admin = $this->superAdmin();
        $target = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($admin)->post(route('users.impersonate', $target))->assertRedirect();

        $this->assertAuthenticatedAs($target);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Return to my account');

        $this->post(route('impersonation.stop'))->assertRedirect();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_fresh_request_resolves_authenticated_user_without_recursion(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->post('/auth-login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect();

        // Simulate the next real HTTP request: the session guard must re-resolve
        // the user from the session (this used to recurse infinitely).
        auth()->guard('web')->forgetUser();

        $this->get('/dashboard')->assertOk();
    }

    public function test_fresh_request_resolves_super_admin_without_recursion(): void
    {
        $admin = $this->superAdmin();

        $this->post('/auth-login', [
            'username' => $admin->username,
            'password' => 'password',
        ])->assertRedirect();

        auth()->guard('web')->forgetUser();

        $this->get('/dashboard')->assertOk();
        $this->get('/organizations')->assertOk();
    }

    public function test_fresh_request_resolves_organization_user_without_recursion_and_keeps_scoping(): void
    {
        $organization = Organization::factory()->paid()->create();
        $otherOrganization = Organization::factory()->create();

        $user = $this->userWithPermissions(['dashboard.view'], ['organization_id' => $organization->id]);

        Product::factory()->create(['organization_id' => $organization->id, 'name' => 'Org A Product']);
        Product::factory()->create(['organization_id' => $otherOrganization->id, 'name' => 'Org B Product']);

        $this->post('/auth-login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect();

        auth()->guard('web')->forgetUser();

        $this->get('/dashboard')->assertOk();

        $this->assertTrue(Product::query()->where('name', 'Org A Product')->exists());
        $this->assertFalse(Product::query()->where('name', 'Org B Product')->exists());
    }
}
