<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailSettingsService
{
    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return [
            'mail.enabled', 'mail.mailer', 'mail.host', 'mail.port', 'mail.username',
            'mail.password', 'mail.encryption', 'mail.from_address', 'mail.from_name',
        ];
    }

    /**
     * Apply database-stored SMTP settings to the mailer at runtime.
     */
    public static function apply(): void
    {
        if (! Setting::boolean('mail.enabled', false)) {
            return;
        }

        $mailer = (string) Setting::getValue('mail.mailer', 'smtp');

        if ($mailer === 'smtp') {
            $encryption = (string) Setting::getValue('mail.encryption', 'tls');

            config()->set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'url' => null,
                'host' => (string) Setting::getValue('mail.host', '127.0.0.1'),
                'port' => (int) Setting::getValue('mail.port', 587),
                'username' => Setting::getValue('mail.username'),
                'password' => static::password(),
                'timeout' => null,
                'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
            ]);
        }

        config()->set('mail.default', $mailer);
        config()->set('mail.from', [
            'address' => (string) Setting::getValue('mail.from_address', config('mail.from.address')),
            'name' => (string) Setting::getValue('mail.from_name', config('mail.from.name')),
        ]);

        Mail::purge($mailer);
    }

    public static function password(): ?string
    {
        $value = Setting::getValue('mail.password');

        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            Log::warning('Unable to decrypt stored mail password.');

            return null;
        }
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public static function sendTest(string $to): array
    {
        static::apply();

        try {
            Mail::raw(
                'This is a test email from '.config('app.name').'. Your email settings are working correctly.',
                function ($message) use ($to): void {
                    $message->to($to)->subject('Email settings test — '.config('app.name'));
                },
            );

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
