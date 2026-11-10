<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationActive
{
    /**
     * Routes an organization user may still access while their CRM is blocked.
     *
     * @var array<int, string>
     */
    protected array $allowedRoutes = [
        'billing.*',
        'auth-logout',
        'profile.*',
        'dashboard.profile',
        'contacts-profile',
        'contacts-profile-template',
        'notifications.*',
        'api.notifications.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isSuperAdmin() || $user->organization_id === null) {
            return $next($request);
        }

        $organization = $user->organization;

        if ($organization === null || ! $organization->crmIsBlocked()) {
            return $next($request);
        }

        if ($request->routeIs(...$this->allowedRoutes)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Your organization access has been blocked. Please contact support.',
            ], 403);
        }

        return redirect()->route('billing.index')->with('error', 'Your organization access has been blocked. Please contact support.');
    }
}
