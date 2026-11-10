<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $key = 'user.last_seen.'.$user->id;

            if (Cache::add($key, true, 60)) {
                $user->forceFill(['last_seen_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}
