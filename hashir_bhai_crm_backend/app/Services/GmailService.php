<?php

namespace App\Services;

use App\Models\GmailAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const API_URL = 'https://gmail.googleapis.com/gmail/v1/users/me';

    public const SCOPES = [
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/gmail.send',
        'https://www.googleapis.com/auth/gmail.modify',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

    public function isConfigured(): bool
    {
        return filled($this->clientId()) && filled($this->clientSecret());
    }

    public function clientId(): ?string
    {
        return Setting::getValue('integrations.google_client_id');
    }

    public function clientSecret(): ?string
    {
        return Setting::getValue('integrations.google_client_secret');
    }

    public function redirectUri(): string
    {
        return (string) Setting::getValue('integrations.google_redirect_uri', url('/gmail/callback'));
    }

    public function authUrl(string $state): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->timeout(30)->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            Log::warning('Gmail token exchange failed', ['response' => $response->json()]);

            return ['ok' => false, 'error' => $response->json('error_description') ?? 'Token exchange failed.'];
        }

        return ['ok' => true, 'data' => $response->json()];
    }

    public function accessToken(GmailAccount $account): ?string
    {
        if (! $account->isExpired() && filled($account->access_token)) {
            return $account->access_token;
        }

        if (blank($account->refresh_token)) {
            return null;
        }

        $response = Http::asForm()->timeout(30)->post(self::TOKEN_URL, [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $account->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            Log::warning('Gmail token refresh failed', ['response' => $response->json()]);
            $account->update(['last_error' => $response->json('error_description') ?? 'Token refresh failed.']);

            return null;
        }

        $account->update([
            'access_token' => $response->json('access_token'),
            'expires_at' => now()->addSeconds((int) ($response->json('expires_in') ?? 3600) - 60),
            'last_error' => null,
        ]);

        return $account->access_token;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listMessages(GmailAccount $account, string $query = '', int $maxResults = 25): array
    {
        $token = $this->accessToken($account);

        if ($token === null) {
            return [];
        }

        $response = Http::withToken($token)->timeout(30)->get(self::API_URL.'/messages', [
            'maxResults' => $maxResults,
            'q' => $query,
        ]);

        if ($response->failed()) {
            Log::warning('Gmail list failed', ['response' => $response->json()]);

            return [];
        }

        return $response->json('messages') ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMessage(GmailAccount $account, string $id): ?array
    {
        $token = $this->accessToken($account);

        if ($token === null) {
            return null;
        }

        $response = Http::withToken($token)->timeout(30)->get(self::API_URL.'/messages/'.$id, ['format' => 'full']);

        if ($response->failed()) {
            Log::warning('Gmail get failed', ['id' => $id, 'response' => $response->json()]);

            return null;
        }

        return $response->json();
    }

    /**
     * @param  array<int, array{name: string, mime: string, data: string}>  $attachments
     * @return array{ok: bool, id?: string, error?: string}
     */
    public function sendMessage(
        GmailAccount $account,
        string $to,
        string $subject,
        string $body,
        ?string $cc = null,
        ?string $bcc = null,
        array $attachments = [],
        bool $html = false
    ): array {
        $token = $this->accessToken($account);

        if ($token === null) {
            return ['ok' => false, 'error' => 'Unable to refresh the Gmail access token.'];
        }

        $boundary = 'cds_'.bin2hex(random_bytes(8));

        $headers = [
            'To: '.$to,
            'From: '.($account->display_name ? '"'.$account->display_name.'" <'.$account->email.'>' : $account->email),
            'Subject: =?UTF-8?B?'.base64_encode($subject).'?=',
            'MIME-Version: 1.0',
        ];

        if (filled($cc)) {
            $headers[] = 'Cc: '.$cc;
        }

        if (filled($bcc)) {
            $headers[] = 'Bcc: '.$bcc;
        }

        if ($attachments === []) {
            $headers[] = 'Content-Type: '.($html ? 'text/html' : 'text/plain').'; charset="UTF-8"';
            $raw = implode("\r\n", $headers)."\r\n\r\n".$body;
        } else {
            $headers[] = 'Content-Type: multipart/mixed; boundary="'.$boundary.'"';
            $parts = [
                '--'.$boundary,
                'Content-Type: '.($html ? 'text/html' : 'text/plain').'; charset="UTF-8"',
                '',
                $body,
            ];

            foreach ($attachments as $attachment) {
                $parts[] = '--'.$boundary;
                $parts[] = 'Content-Type: '.$attachment['mime'].'; name="'.$attachment['name'].'"';
                $parts[] = 'Content-Transfer-Encoding: base64';
                $parts[] = 'Content-Disposition: attachment; filename="'.$attachment['name'].'"';
                $parts[] = '';
                $parts[] = chunk_split($attachment['data'], 76, "\r\n");
            }

            $parts[] = '--'.$boundary.'--';
            $parts[] = '';

            $raw = implode("\r\n", $headers)."\r\n\r\n".implode("\r\n", $parts);
        }

        $response = Http::withToken($token)->timeout(30)->post(self::API_URL.'/messages/send', [
            'raw' => rtrim(strtr(base64_encode($raw), '+/', '-_'), '='),
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'error' => $response->json('error.message') ?? 'Gmail send failed.'];
        }

        return ['ok' => true, 'id' => (string) $response->json('id')];
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(GmailAccount $account): array
    {
        $token = $this->accessToken($account);

        if ($token === null) {
            return [];
        }

        $response = Http::withToken($token)->timeout(20)->get(self::API_URL.'/profile');

        return $response->successful() ? ($response->json() ?? []) : [];
    }

    /**
     * Extract headers and body from a Gmail API message payload.
     *
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>
     */
    public function parseMessage(array $message): array
    {
        $payload = $message['payload'] ?? [];
        $headers = collect($payload['headers'] ?? [])->mapWithKeys(
            fn (array $header) => [strtolower($header['name']) => $header['value']]
        );

        [$plain, $html, $attachments] = $this->walkParts($payload);

        return [
            'gmail_message_id' => $message['id'] ?? null,
            'thread_id' => $message['threadId'] ?? null,
            'subject' => $headers->get('subject'),
            'from_name' => $this->nameFromHeader((string) $headers->get('from')),
            'from_email' => $this->emailFromHeader((string) $headers->get('from')),
            'to_recipients' => $this->emailsFromHeader((string) $headers->get('to')),
            'cc_recipients' => $this->emailsFromHeader((string) $headers->get('cc')),
            'bcc_recipients' => $this->emailsFromHeader((string) $headers->get('bcc')),
            'body' => $plain ?: strip_tags((string) $html),
            'html_body' => $html,
            'snippet' => $message['snippet'] ?? null,
            'labels' => $message['labelIds'] ?? [],
            'has_attachments' => $attachments !== [],
            'attachments' => $attachments,
            'is_read' => ! in_array('UNREAD', $message['labelIds'] ?? [], true),
            'received_at' => isset($message['internalDate']) ? now()->setTimestamp((int) ($message['internalDate'] / 1000)) : now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: string, 1: string, 2: array<int, array<string, mixed>>}
     */
    private function walkParts(array $payload, string $plain = '', string $html = '', array $attachments = []): array
    {
        $mimeType = $payload['mimeType'] ?? '';
        $body = $payload['body'] ?? [];
        $filename = $payload['filename'] ?? null;

        if (filled($filename) && filled($body['attachmentId'] ?? null)) {
            $attachments[] = [
                'filename' => $filename,
                'mime' => $mimeType,
                'size' => $body['size'] ?? 0,
                'attachment_id' => $body['attachmentId'],
            ];
        } elseif (filled($body['data'] ?? null)) {
            $decoded = $this->decode($body['data']);

            if ($mimeType === 'text/plain' && $plain === '') {
                $plain = $decoded;
            } elseif ($mimeType === 'text/html' && $html === '') {
                $html = $decoded;
            }
        }

        foreach ($payload['parts'] ?? [] as $part) {
            [$plain, $html, $attachments] = $this->walkParts($part, $plain, $html, $attachments);
        }

        return [$plain, $html, $attachments];
    }

    private function decode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'));
    }

    private function nameFromHeader(string $header): string
    {
        if (preg_match('/^(.*)<.*>$/', $header, $matches)) {
            return trim(trim($matches[1]), '"\' ');
        }

        return $this->emailFromHeader($header);
    }

    private function emailFromHeader(string $header): string
    {
        if (preg_match('/<([^>]+)>/', $header, $matches)) {
            return $matches[1];
        }

        return trim($header);
    }

    /**
     * @return array<int, string>
     */
    private function emailsFromHeader(string $header): array
    {
        if (trim($header) === '') {
            return [];
        }

        return collect(explode(',', $header))
            ->map(fn (string $part) => $this->emailFromHeader($part))
            ->filter()
            ->values()
            ->all();
    }
}
