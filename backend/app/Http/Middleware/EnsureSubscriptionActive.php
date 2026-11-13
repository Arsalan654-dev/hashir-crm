<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isSuperAdmin() || $user->organization_id === null) {
            return $next($request);
        }

        $organization = $user->organization;

        if ($organization === null || $organization->isPaid()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Your subscription is not active yet. Please complete your payment to unlock this feature.',
            ], 403);
        }

        return redirect()->route('billing.index')->with('error', 'Please complete your payment to unlock this feature.');
    }
}
