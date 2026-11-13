<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function parameterlessGetRoutes(): array
    {
        $skip = [
            '/', '/auth-login', '/auth-recoverpw', '/auth-reset-password', '/auth-confirm-mail',
            '/auth-logout', '/up',
        ];

        return collect(Route::getRoutes())
            ->filter(function ($route): bool {
                $uri = $route->uri();

                return in_array('GET', $route->methods(), true)
                    && trim($uri, '/') !== ''
                    && ! str_contains($uri, '{')
                    && ! str_starts_with($uri, '_')
                    && ! str_starts_with($uri, 'webhooks')
                    && ! str_starts_with($uri, 'api/');
            })
            ->map(fn ($route): string => '/'.$route->uri())
            ->reject(fn (string $uri): bool => in_array($uri, $skip, true))
            ->unique()
            ->values()
            ->all();
    }

    public function test_every_get_page_renders_for_super_admin(): void
    {
        $admin = $this->superAdmin();
        $failures = [];

        foreach ($this->parameterlessGetRoutes() as $uri) {
            $response = $this->actingAs($admin)->get($uri);

            if ($response->getStatusCode() >= 500) {
                $failures[$uri] = $response->getStatusCode();
            }
        }

        $this->assertSame([], $failures, 'Server errors: '.json_encode($failures));
    }

    public function test_every_get_page_renders_for_organization_admin(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = Role::query()->where('slug', Role::ORGANIZATION_ADMIN)->firstOrFail();
        $organization = Organization::factory()->paid()->create();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $failures = [];

        foreach ($this->parameterlessGetRoutes() as $uri) {
            $response = $this->actingAs($user)->get($uri);
            $status = $response->getStatusCode();

            if ($status >= 500) {
                $failures[$uri] = $status;
            }
        }

        $this->assertSame([], $failures, 'Server errors: '.json_encode($failures));
    }
}
