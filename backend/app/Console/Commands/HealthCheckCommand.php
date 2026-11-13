<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class HealthCheckCommand extends Command
{
    protected $signature = 'crm:health-check {--email= : User to impersonate (defaults to the first super admin)}';

    protected $description = 'Crawl every parameterless GET page and report server errors (A-to-Z smoke check).';

    public function handle(): int
    {
        $user = filled($this->option('email'))
            ? User::query()->where('email', $this->option('email'))->first()
            : User::query()->where('is_super_admin', true)->first();

        if ($user === null) {
            $this->error('No super admin user found. Pass --email=user@example.com.');

            return self::FAILURE;
        }

        Auth::login($user);

        $this->info('Health check as: '.$user->name.' <'.$user->email.'>');

        $skip = [
            '/', '/auth-login', '/auth-recoverpw', '/auth-reset-password', '/auth-confirm-mail',
            '/auth-logout', '/up',
        ];

        $uris = collect(Route::getRoutes())
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
            ->values();

        $failures = [];
        $bar = $this->output->createProgressBar($uris->count());

        foreach ($uris as $uri) {
            try {
                $request = Request::create($uri, 'GET');
                $response = app()->handle($request);
                $status = $response->getStatusCode();

                if ($status >= 500) {
                    $failures[$uri] = (string) $status;
                }
            } catch (\Throwable $e) {
                $failures[$uri] = 'EXCEPTION: '.$e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($failures === []) {
            $this->info('PASS — all '.$uris->count().' GET pages responded without server errors.');

            return self::SUCCESS;
        }

        $this->error('FAILURES ('.count($failures).'):');

        foreach ($failures as $uri => $status) {
            $this->error('  '.$uri.' → '.$status);
        }

        return self::FAILURE;
    }
}
