<?php

namespace App\Services\Whatsapp;

use App\Models\WhatsappAccount;
use App\Services\Whatsapp\Contracts\WhatsappProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class MetaCloudProvider implements WhatsappProvider
{
    public function key(): string
    {
        return 'meta_cloud';
    }

    public function isConfigured(?WhatsappAccount $account): bool
    {
        return $account !== null
            && filled($account->phone_number_id)
            && filled($account->access_token);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendText(WhatsappAccount $account, string $to, string $body, bool $previewUrl = true): array
    {
        return $this->post($account, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalise($to),
            'type' => 'text',
            'text' => ['preview_url' => $previewUrl, 'body' => $body],
        ]);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendMedia(WhatsappAccount $account, string $to, string $type, string $url, ?string $caption = null, ?string $filename = null): array
    {
        $payload = match ($type) {
            'image' => ['link' => $url],
            'video' => ['link' => $url],
            'audio' => ['link' => $url],
            default => ['link' => $url, 'filename' => $filename ?? basename(parse_url($url, PHP_URL_PATH) ?: 'document')],
        };

        if ($caption !== null && in_array($type, ['image', 'video', 'document'], true)) {
            $payload['caption'] = $caption;
        }

        return $this->post($account, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalise($to),
            'type' => $type,
            $type => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $components
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendTemplate(WhatsappAccount $account, string $to, string $template, string $language = 'en_US', array $components = []): array
    {
        return $this->post($account, [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalise($to),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $language],
                'components' => $components,
            ],
        ]);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function testConnection(WhatsappAccount $account): array
    {
        if (! $this->isConfigured($account)) {
            return ['ok' => false, 'error' => 'Phone number ID and access token are required.'];
        }

        $response = $this->client($account)->get($account->phone_number_id, [
            'fields' => 'verified_name,display_phone_number,quality_rating,code_verification_status',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'error' => $response->json('error.message') ?? 'Connection failed with status '.$response->status()];
        }

        return ['ok' => true, 'data' => $response->json()];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    private function post(WhatsappAccount $account, array $payload): array
    {
        $response = $this->client($account)->post('messages', $payload);

        if ($response->failed()) {
            return [
                'ok' => false,
                'error' => $response->json('error.message') ?? 'Request failed with status '.$response->status(),
                'data' => $response->json() ?? [],
            ];
        }

        return ['ok' => true, 'data' => $response->json() ?? []];
    }

    private function client(WhatsappAccount $account): PendingRequest
    {
        return Http::baseUrl('https://graph.facebook.com/'.($account->api_version ?: 'v21.0').'/'.$account->phone_number_id)
            ->withToken((string) $account->access_token)
            ->acceptJson()
            ->timeout(30);
    }

    private function normalise(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number) ?? $number;
    }
}
