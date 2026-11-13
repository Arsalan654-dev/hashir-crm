<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_guests_see_the_login_page_at_the_root_url(): void
    {
        $this->get('/')->assertOk()->assertSee('Log In');
    }

    public function test_super_admin_can_log_in_and_reach_the_dashboard(): void
    {
        $user = $this->superAdmin([
            'username' => 'superadmin',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->post('/auth-login', [
            'username' => 'superadmin',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->assertNotNull($user->fresh()->last_login_at);

        $this->get('/dashboard')->assertOk();
    }

    public function test_user_can_log_in_with_email(): void
    {
        $this->superAdmin([
            'email' => 'owner@cyprusdigital.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->post('/auth-login', [
            'username' => 'owner@cyprusdigital.com',
            'password' => 'secret-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $this->userWithPermissions(['dashboard.view'], [
            'username' => 'inactive-user',
            'password' => Hash::make('secret-password'),
            'is_active' => false,
        ]);

        $this->post('/auth-login', [
            'username' => 'inactive-user',
            'password' => 'secret-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->userWithPermissions(['dashboard.view'], [
            'username' => 'someone',
            'password' => Hash::make('secret-password'),
        ]);

        $this->post('/auth-login', [
            'username' => 'someone',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $user = $this->userWithPermissions(['dashboard.view'], [
            'email' => 'reset@cyprusdigital.com',
        ]);

        $this->post('/auth-recoverpw', ['email' => $user->email])
            ->assertRedirect(route('auth-confirm-mail'));

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        $user = $this->userWithPermissions(['dashboard.view'], [
            'email' => 'reset2@cyprusdigital.com',
        ]);

        $token = Password::createToken($user);

        $this->post('/auth-reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertRedirect(route('home'));

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/auth-login')
            ->assertRedirect(route('home'));
    }
}
