<?php

namespace App\Providers;

use App\Models\User;
use App\Services\MailSettingsService;
use App\Support\Permissions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (Permissions::keys() as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        Blade::if('permission', function (string ...$permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });

        try {
            MailSettingsService::apply();
        } catch (\Throwable) {
            // Settings table may not exist yet (fresh install / migrations).
        }
    }
}
