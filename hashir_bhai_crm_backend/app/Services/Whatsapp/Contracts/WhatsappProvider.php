<?php

namespace App\Services\Whatsapp\Contracts;

use App\Models\WhatsappAccount;

interface WhatsappProvider
{
    public function key(): string;

    public function isConfigured(?WhatsappAccount $account): bool;

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendText(WhatsappAccount $account, string $to, string $body, bool $previewUrl = true): array;

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendMedia(WhatsappAccount $account, string $to, string $type, string $url, ?string $caption = null, ?string $filename = null): array;

    /**
     * @param  array<string, mixed>  $components
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendTemplate(WhatsappAccount $account, string $to, string $template, string $language = 'en_US', array $components = []): array;

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function testConnection(WhatsappAccount $account): array;
}
