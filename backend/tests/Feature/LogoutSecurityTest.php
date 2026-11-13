<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class LogoutSecurityTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_authenticated_pages_send_no_cache_headers(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $response->headers->get('Cache-Control'));
    }

    public function test_logout_redirects_and_protected_pages_are_not_accessible_afterwards(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/dashboard')->assertOk();

        $this->post('/auth-logout')->assertRedirect(route('auth-login'));

        auth()->guard('web')->forgetUser();

        $this->get('/dashboard')->assertRedirect(route('auth-login'));
        $this->get('/kitchen')->assertRedirect(route('auth-login'));
        $this->get('/organizations')->assertRedirect(route('auth-login'));
    }

    public function test_layout_contains_back_forward_cache_guard(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('pageshow', false)
            ->assertSee('sidebar-logout-form', false);
    }

    public function test_template_pages_are_removed(): void
    {
        $admin = $this->superAdmin();

        $removed = [
            '/ui-general', '/ui-buttons', '/charts-apex', '/charts-chartjs', '/charts-tui',
            '/form-elements', '/form-wizard', '/icons-boxicons', '/icons-fontawesome',
            '/maps-leaflet', '/maps-google', '/tables-basic', '/tables-advanced',
            '/layouts-horizontal', '/pages-faqs', '/pages-500', '/pages-comingsoon',
            '/pages-maintenance', '/pages-pricing', '/pages-starter', '/pages-timeline',
            '/auth-email-verification',
        ];

        foreach ($removed as $uri) {
            $this->actingAs($admin)->get($uri)->assertNotFound();
        }
    }

    public function test_login_page_has_no_social_login_buttons(): void
    {
        $response = $this->get('/auth-login');

        $response->assertOk();
        $response->assertDontSee('Sign in with');
        $response->assertDontSee('bxl-google');
        $response->assertDontSee('bxl-facebook');
        $response->assertDontSee('bxl-linkedin');
    }
}
