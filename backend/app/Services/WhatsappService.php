<?php

namespace App\Services;

use App\Models\ChatChannel;
use App\Models\Message;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Services\Whatsapp\Contracts\WhatsappProvider;
use App\Services\Whatsapp\MetaCloudProvider;
use App\Services\Whatsapp\WhatchimpProvider;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function __construct(private ?WhatsappAccount $account = null) {}

    public function for(?WhatsappAccount $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function account(): ?WhatsappAccount
    {
        return $this->account ??= WhatsappAccount::default();
    }

    public function provider(): WhatsappProvider
    {
        $account = $this->account();

        return $account !== null && $account->isWhatchimp()
            ? app(WhatchimpProvider::class)
            : app(MetaCloudProvider::class);
    }

    public function isConfigured(): bool
    {
        return $this->provider()->isConfigured($this->account());
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendText(string $to, string $body, bool $previewUrl = true): array
    {
        $account = $this->account();

        if ($account === null) {
            return ['ok' => false, 'error' => 'No WhatsApp account configured.'];
        }

        $result = $this->provider()->sendText($account, $to, $body, $previewUrl);

        return $this->record($account, $to, 'text', $body, null, $result);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendMedia(string $to, string $type, string $url, ?string $caption = null, ?string $filename = null): array
    {
        $account = $this->account();

        if ($account === null) {
            return ['ok' => false, 'error' => 'No WhatsApp account configured.'];
        }

        $result = $this->provider()->sendMedia($account, $to, $type, $url, $caption, $filename);

        return $this->record($account, $to, $type, $caption, $url, $result);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendDocument(string $to, string $url, string $filename, ?string $caption = null): array
    {
        return $this->sendMedia($to, 'document', $url, $caption, $filename);
    }

    /**
     * @param  array<string, mixed>  $components
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function sendTemplate(string $to, string $template, string $language = 'en_US', array $components = []): array
    {
        $account = $this->account();

        if ($account === null) {
            return ['ok' => false, 'error' => 'No WhatsApp account configured.'];
        }

        $result = $this->provider()->sendTemplate($account, $to, $template, $language, $components);

        return $this->record($account, $to, 'template', $template, null, $result);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string}
     */
    public function testConnection(): array
    {
        $account = $this->account();

        if ($account === null || ! $this->provider()->isConfigured($account)) {
            return ['ok' => false, 'error' => 'WhatsApp account is not configured. Add your credentials in WhatsApp settings.'];
        }

        return $this->provider()->testConnection($account);
    }

    /**
     * @param  array{ok: bool, data?: array<string, mixed>, error?: string}  $result
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, message?: WhatsappMessage}
     */
    private function record(WhatsappAccount $account, string $to, string $type, ?string $body, ?string $mediaUrl, array $result): array
    {
        $waId = $this->normaliseNumber($to);

        $contact = WhatsappContact::query()->firstOrCreate(
            ['whatsapp_account_id' => $account->id, 'wa_id' => $waId],
            ['phone' => $waId, 'source' => 'whatsapp'],
        );

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];

        $message = WhatsappMessage::query()->create([
            'whatsapp_account_id' => $account->id,
            'whatsapp_contact_id' => $contact->id,
            'chat_channel_id' => $contact->chat_channel_id,
            'wa_message_id' => $data['wa_message_id'] ?? $data['messages'][0]['id'] ?? null,
            'direction' => 'out',
            'type' => $type,
            'body' => $body,
            'media_url' => $mediaUrl,
            'status' => $result['ok'] ? 'sent' : 'failed',
            'error_message' => $result['ok'] ? null : ($result['error'] ?? 'Request failed'),
            'raw' => $data === [] ? null : $data,
            'sent_at' => now(),
        ]);

        $contact->update(['last_message_at' => now()]);

        if ($contact->chat_channel_id) {
            Message::query()->create([
                'chat_channel_id' => $contact->chat_channel_id,
                'user_id' => auth()->id(),
                'type' => in_array($type, ['image', 'video', 'audio', 'document'], true) ? $type : 'text',
                'body' => $body,
                'attachment_path' => in_array($type, ['image', 'video'], true) ? $mediaUrl : null,
                'sent_at' => now(),
            ]);

            ChatChannel::query()->whereKey($contact->chat_channel_id)->update(['last_message_at' => now()]);
        }

        if (! $result['ok']) {
            Log::warning('WhatsApp send failed', [
                'provider' => $account->provider,
                'to' => $waId,
                'error' => $result['error'] ?? null,
            ]);
        }

        return $result + ['message' => $message];
    }

    public function normaliseNumber(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number) ?? $number;
    }
}
