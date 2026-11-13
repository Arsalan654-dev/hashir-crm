<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\MailSettingsService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class EmailSettingsController extends Controller
{
    public function index(): View
    {
        return view('pages.settings-email', [
            'settings' => [
                'enabled' => Setting::boolean('mail.enabled', false),
                'mailer' => (string) Setting::getValue('mail.mailer', 'smtp'),
                'host' => (string) Setting::getValue('mail.host', ''),
                'port' => (string) Setting::getValue('mail.port', '587'),
                'username' => (string) Setting::getValue('mail.username', ''),
                'has_password' => filled(Setting::getValue('mail.password')),
                'encryption' => (string) Setting::getValue('mail.encryption', 'tls'),
                'from_address' => (string) Setting::getValue('mail.from_address', config('mail.from.address')),
                'from_name' => (string) Setting::getValue('mail.from_name', config('mail.from.name')),
                'bank_details' => (string) Setting::getValue('billing.bank_details', ''),
                'billing_instructions' => (string) Setting::getValue('billing.instructions', ''),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail.enabled' => ['nullable', 'boolean'],
            'mail.mailer' => ['required', Rule::in(['smtp', 'log', 'array'])],
            'mail.host' => ['nullable', 'string', 'max:255'],
            'mail.port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail.username' => ['nullable', 'string', 'max:255'],
            'mail.password' => ['nullable', 'string', 'max:255'],
            'mail.encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'mail.from_address' => ['nullable', 'email', 'max:255'],
            'mail.from_name' => ['nullable', 'string', 'max:150'],
            'billing.bank_details' => ['nullable', 'string', 'max:2000'],
            'billing.instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        $mail = $data['mail'] ?? [];

        Setting::setValue('mail.enabled', $request->boolean('mail.enabled') ? '1' : '0', 'mail', 'boolean');
        Setting::setValue('mail.mailer', $mail['mailer'] ?? 'smtp', 'mail');
        Setting::setValue('mail.host', $mail['host'] ?? '', 'mail');
        Setting::setValue('mail.port', (string) ($mail['port'] ?? 587), 'mail');
        Setting::setValue('mail.username', $mail['username'] ?? '', 'mail');
        Setting::setValue('mail.encryption', $mail['encryption'] ?? 'tls', 'mail');
        Setting::setValue('mail.from_address', $mail['from_address'] ?? '', 'mail');
        Setting::setValue('mail.from_name', $mail['from_name'] ?? '', 'mail');

        if (filled($mail['password'] ?? null)) {
            Setting::setValue('mail.password', Crypt::encryptString((string) $mail['password']), 'mail', 'encrypted');
        }

        Setting::setValue('billing.bank_details', $data['billing']['bank_details'] ?? '', 'billing', 'text');
        Setting::setValue('billing.instructions', $data['billing']['instructions'] ?? '', 'billing', 'text');

        Activity::log('settings_updated', 'Email / billing settings updated', null, [], 'settings');

        return back()->with('status', 'Email settings saved.');
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $result = MailSettingsService::sendTest($data['test_email']);

        if (! $result['ok']) {
            return back()->withErrors(['test_email' => 'Test failed: '.($result['error'] ?? 'unknown error')]);
        }

        return back()->with('status', 'Test email sent to '.$data['test_email'].'.');
    }
}
