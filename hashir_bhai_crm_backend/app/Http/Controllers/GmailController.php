<?php

namespace App\Http\Controllers;

use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Models\Setting;
use App\Services\GmailService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GmailController extends Controller
{
    public function __construct(private readonly GmailService $gmail) {}

    public function settings(): View
    {
        return view('pages.email.settings', [
            'accounts' => GmailAccount::query()->orderByDesc('is_active')->get(),
            'configured' => $this->gmail->isConfigured(),
            'redirectUri' => $this->gmail->redirectUri(),
            'clientId' => Setting::getValue('integrations.google_client_id'),
            'hasSecret' => filled(Setting::getValue('integrations.google_client_secret')),
            'syncedCount' => EmailMessage::query()->where('provider', 'gmail')->count(),
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
            'google_redirect_uri' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            if (filled($value)) {
                Setting::setValue('integrations.'.$key, $value, 'integrations');
            }
        }

        Activity::log('settings_updated', 'Google OAuth credentials updated.', null, [], 'email');

        return back()->with('status', 'Google credentials saved. Now click "Connect Gmail".');
    }

    public function connect(Request $request): RedirectResponse
    {
        if (! $this->gmail->isConfigured()) {
            return back()->withErrors(['gmail' => 'Add the Google Client ID and Client Secret first.']);
        }

        $state = Str::random(40);
        $request->session()->put('gmail_oauth_state', $state);

        return redirect()->away($this->gmail->authUrl($state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $state = (string) $request->session()->pull('gmail_oauth_state');

        if ($state === '' || $state !== (string) $request->query('state')) {
            return redirect()->route('gmail.settings')->withErrors(['gmail' => 'OAuth state mismatch. Please try connecting again.']);
        }

        if ($request->filled('error')) {
            return redirect()->route('gmail.settings')->withErrors(['gmail' => (string) $request->query('error_description', 'Google rejected the connection.')]);
        }

        $code = (string) $request->query('code');

        if ($code === '') {
            return redirect()->route('gmail.settings')->withErrors(['gmail' => 'Missing authorization code.']);
        }

        $result = $this->gmail->exchangeCode($code);

        if ($result['ok'] !== true) {
            return redirect()->route('gmail.settings')->withErrors(['gmail' => $result['error'] ?? 'Token exchange failed.']);
        }

        $tokens = $result['data'];
        $email = $this->resolveEmail($tokens);

        $account = GmailAccount::query()->updateOrCreate(
            ['email' => $email],
            [
                'user_id' => Auth::id(),
                'display_name' => Str::before($email, '@'),
                'access_token' => $tokens['access_token'] ?? null,
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600) - 60),
                'scope' => $tokens['scope'] ?? implode(' ', GmailService::SCOPES),
                'is_active' => true,
                'is_shared' => true,
                'sync_enabled' => true,
                'last_error' => null,
            ],
        );

        Activity::log('connected', 'Gmail account connected: '.$email, $account, [], 'email');

        return redirect()->route('email-inbox')->with('status', 'Gmail connected as '.$email.' — syncing your inbox now.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $account = GmailAccount::query()->where('is_active', true)->orderByDesc('id')->first();

        if ($account === null) {
            return back()->withErrors(['gmail' => 'Connect a Gmail account first.']);
        }

        $messages = $this->gmail->listMessages($account, 'in:inbox', 30);
        $imported = 0;

        foreach ($messages as $meta) {
            $id = $meta['id'] ?? null;

            if ($id === null || EmailMessage::query()->where('gmail_message_id', $id)->exists()) {
                continue;
            }

            $message = $this->gmail->getMessage($account, $id);

            if ($message === null) {
                continue;
            }

            $parsed = $this->gmail->parseMessage($message);

            EmailMessage::query()->create([
                'gmail_account_id' => $account->id,
                'provider' => 'gmail',
                'folder' => 'inbox',
                'gmail_message_id' => $parsed['gmail_message_id'],
                'thread_id' => $parsed['thread_id'],
                'from_name' => $parsed['from_name'] ?: 'Unknown sender',
                'from_email' => $parsed['from_email'],
                'to_email' => implode(', ', $parsed['to_recipients']),
                'to_recipients' => $parsed['to_recipients'],
                'cc_recipients' => $parsed['cc_recipients'],
                'subject' => $parsed['subject'] ?: '(no subject)',
                'body' => $parsed['body'],
                'html_body' => $parsed['html_body'],
                'snippet' => $parsed['snippet'],
                'teaser' => Str::limit((string) $parsed['snippet'], 90),
                'has_attachments' => $parsed['has_attachments'],
                'attachments' => $parsed['attachments'],
                'labels' => $parsed['labels'],
                'is_read' => $parsed['is_read'],
                'received_at' => $parsed['received_at'],
                'time_label' => $parsed['received_at']->format('H:i'),
                'date_label' => $parsed['received_at']->format('d M'),
            ]);

            $imported++;
        }

        $account->update(['last_synced_at' => now(), 'last_error' => null]);

        Activity::log('synced', $imported.' emails synced from Gmail.', $account, [], 'email');

        return back()->with('status', $imported.' new emails synced from '.$account->email.'.');
    }

    public function destroy(GmailAccount $account): RedirectResponse
    {
        Activity::log('disconnected', 'Gmail account disconnected: '.$account->email, null, [], 'email');
        $account->delete();

        return back()->with('status', 'Gmail account disconnected.');
    }

    /**
     * @param  array<string, mixed>  $tokens
     */
    private function resolveEmail(array $tokens): string
    {
        try {
            $response = Http::withToken((string) ($tokens['access_token'] ?? ''))
                ->timeout(20)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->successful() && filled($response->json('email'))) {
                return (string) $response->json('email');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return 'gmail-account-'.Str::random(6).'@gmail.com';
    }
}
