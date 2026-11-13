<?php

namespace App\Services\Whatsapp;

use App\Models\WhatsappAccount;
use App\Services\Whatsapp\Contracts\WhatsappProvider;
use Illuminate\Support\Facades\Http;

class WhatchimpProvider implements WhatsappProvider
{
    public const DEFAULT_BASE_URL = 'https://app.whatchimp.com/api/v1';

    public function key(): string
    {
        return 'whatchimp';
    }

    public function isConfigured(?WhatsappAccount $account): bool
    {
        return $account !== null
            && filled($account->api_key)
            && filled($account->phone_number_id);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendText(WhatsappAccount $account, string $to, string $body, bool $previewUrl = true): array
    {
        return $this->post($account, '/whatsapp/send', [
            'message' => $body,
            'phone_number' => $this->normalise($to),
        ]);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendMedia(WhatsappAccount $account, string $to, string $type, string $url, ?string $caption = null, ?string $filename = null): array
    {
        return $this->post($account, '/whatsapp/send', [
            'message' => $caption ?? '',
            'phone_number' => $this->normalise($to),
            'media_url' => $url,
            'media_type' => $type,
            'filename' => $filename,
        ]);
    }

    /**
     * @param  array<string, mixed>  $components
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendTemplate(WhatsappAccount $account, string $to, string $template, string $language = 'en_US', array $components = []): array
    {
        return $this->post($account, '/whatsapp/send/template', [
            'phone_number' => $this->normalise($to),
            'template_id' => $template,
            ...$components,
        ]);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function testConnection(WhatsappAccount $account): array
    {
        if (! $this->isConfigured($account)) {
            return ['ok' => false, 'error' => 'API key and phone number ID are required.'];
        }

        $response = Http::timeout(20)->acceptJson()->get($this->baseUrl($account).'/whatsapp/get/post-back-list', [
            'apiToken' => (string) $account->api_key,
            'phone_number_id' => (string) $account->phone_number_id,
        ]);

        $json = $response->json();
        $json = is_array($json) ? $json : [];

        if (! $response->successful() || ! array_key_exists('status', $json)) {
            return [
                'ok' => false,
                'error' => $json['message'] ?? 'WhatChimp connection failed with status '.$response->status(),
                'data' => $json,
            ];
        }

        if ((string) $json['status'] !== '1') {
            return [
                'ok' => false,
                'error' => (string) ($json['message'] ?? 'WhatChimp rejected the credentials.'),
                'data' => $json,
            ];
        }

        return [
            'ok' => true,
            'data' => [
                'verified_name' => $account->name,
                'display_phone_number' => $account->phone_number,
                'postbacks' => $json['message'] ?? [],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    private function post(WhatsappAccount $account, string $path, array $params): array
    {
        if (! $this->isConfigured($account)) {
            return ['ok' => false, 'error' => 'WhatChimp API key and phone number ID are required in WhatsApp settings.'];
        }

        $params = array_filter($params, fn (mixed $value): bool => $value !== null && $value !== '');

        $response = Http::asForm()
            ->timeout(30)
            ->acceptJson()
            ->post($this->baseUrl($account).$path, [
                'apiToken' => (string) $account->api_key,
                'phone_number_id' => (string) $account->phone_number_id,
                ...$params,
            ]);

        $json = $response->json();
        $json = is_array($json) ? $json : [];

        $ok = $response->successful() && (string) ($json['status'] ?? '0') === '1';

        if (! $ok) {
            return [
                'ok' => false,
                'error' => (string) ($json['message'] ?? 'WhatChimp request failed with status '.$response->status()),
                'data' => $json,
            ];
        }

        return ['ok' => true, 'data' => $json];
    }

    private function baseUrl(WhatsappAccount $account): string
    {
        return rtrim($account->base_url ?: self::DEFAULT_BASE_URL, '/');
    }

    private function normalise(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number) ?? $number;
    }
}
