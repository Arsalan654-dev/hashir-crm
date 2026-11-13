<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureOrganizationActive;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\PreventCachedAuthenticatedPages;
use App\Http\Middleware\UpdateLastSeen;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => EnsurePermission::class,
            'subscribed' => EnsureSubscriptionActive::class,
        ]);

        // External services (WhatsApp webhooks, bot API, customer payment page) cannot send CSRF tokens.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'api/bot/*',
            'pay/*',
        ]);

        $middleware->web(append: [
            EnsureAccountIsActive::class,
            EnsureOrganizationActive::class,
            UpdateLastSeen::class,
            PreventCachedAuthenticatedPages::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('auth-login'));
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => '403 — You cannot access this.'], 403);
            }

            return response()->view('pages.pages-403', [], 403);
        });
    })->create();
