<?php

namespace App\Http\Controllers;

use App\Models\BotConversation;
use App\Models\ChatChannel;
use App\Models\KnowledgeArticle;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Services\Bot\BotEngine;
use App\Services\WhatsappMediaService;
use App\Services\WhatsappService;
use App\Support\Activity;
use App\Support\Branding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WhatsappWebhookController extends Controller
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    public function verify(Request $request, ?WhatsappAccount $account = null): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));
        $account ??= WhatsappAccount::default();

        if ($mode === 'subscribe' && filled($token) && $account !== null && hash_equals((string) $account->verify_token, (string) $token)) {
            Log::info('WhatsApp webhook verified.');

            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed.', ['token' => $token]);

        return response('Forbidden', 403);
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $account = $this->resolveAccount($payload);

        if ($account === null) {
            Log::warning('WhatsApp webhook received without a configured account.', ['payload' => $payload]);

            return response()->json(['status' => 'ignored']);
        }

        if (! $this->signatureIsValid($request, $account)) {
            Log::warning('WhatsApp webhook signature mismatch.');

            return response()->json(['status' => 'invalid-signature'], 403);
        }

        foreach (($payload['entry'] ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];

                foreach (($value['messages'] ?? []) as $message) {
                    $stored = $this->storeIncomingMessage($account, $value, $message);

                    if ($stored !== null) {
                        $this->runBot($account, $stored['contact'], $stored['message']);
                    }
                }

                foreach (($value['statuses'] ?? []) as $status) {
                    $this->updateStatus($status);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Webhook endpoint for WhatChimp (Bot Manager → Configuration → Webhook URL).
     */
    public function handleWhatchimp(Request $request): JsonResponse
    {
        $payload = $request->all();
        $data = $this->unwrapWhatchimpPayload($payload);

        $account = $this->resolveWhatchimpAccount($data);

        if ($account === null) {
            Log::warning('WhatChimp webhook received without a configured account.', ['payload' => $payload]);

            return response()->json(['status' => 'ignored']);
        }

        $waId = $this->whatsapp->normaliseNumber((string) $this->firstFilled($data, [
            'phone_number', 'phone', 'wa_id', 'from', 'subscriber_phone', 'contact_number', 'sender',
        ]));

        if ($waId === '') {
            Log::info('WhatChimp webhook without a phone number.', ['payload' => $payload]);

            return response()->json(['status' => 'ignored']);
        }

        $name = $this->firstFilled($data, ['name', 'subscriber_name', 'profile_name', 'contact_name']);
        $type = mb_strtolower((string) ($this->firstFilled($data, ['message_type', 'type', 'media_type']) ?? 'text'));
        $body = $this->firstFilled($data, ['message', 'body', 'text', 'message_text', 'caption', 'content']);
        $mediaUrl = $this->firstFilled($data, ['media_url', 'file_url', 'url', 'media', 'document_url', 'image_url', 'video_url', 'audio_url']);

        $direction = mb_strtolower((string) ($this->firstFilled($data, ['direction', 'message_direction']) ?? ''));
        $isOutgoing = in_array($direction, ['out', 'outgoing', 'sent'], true)
            || filter_var($this->firstFilled($data, ['is_outgoing', 'from_me', 'outgoing']), FILTER_VALIDATE_BOOLEAN)
            || in_array(mb_strtolower((string) $this->firstFilled($data, ['sent_by'])), ['bot', 'agent', 'system', 'team'], true);

        $stored = $this->storeWhatchimpMessage(
            $account,
            $waId,
            $name,
            $isOutgoing ? 'out' : 'in',
            $type !== '' ? $type : 'text',
            is_string($body) ? $body : null,
            is_string($mediaUrl) ? $mediaUrl : null,
        );

        if (! $isOutgoing) {
            $this->runBot($account, $stored['contact'], $stored['message']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function botLead(Request $request): JsonResponse
    {
        $secret = (string) Setting::getValue('integrations.bot_shared_secret', '');

        if ($secret !== '' && ! hash_equals($secret, (string) $request->header('X-Bot-Secret'))) {
            return response()->json(['message' => 'Invalid bot secret.'], 401);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'product_interest' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:30'],
            'external_id' => ['nullable', 'string', 'max:150'],
            'language' => ['nullable', 'string', 'max:10'],
            'summary' => ['nullable', 'string', 'max:20000'],
            'transcript' => ['nullable', 'array'],
            'order_data' => ['nullable', 'array'],
            'order_confirmed' => ['nullable', 'boolean'],
        ]);

        $waId = $this->whatsapp->normaliseNumber($data['phone']);
        $account = WhatsappAccount::default();

        $contact = WhatsappContact::query()->updateOrCreate(
            ['whatsapp_account_id' => $account?->id, 'wa_id' => $waId],
            [
                'phone' => $data['phone'],
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'source' => 'whatsapp_bot',
                'last_message_at' => now(),
            ],
        );

        $lead = $contact->lead_id !== null ? Lead::query()->find($contact->lead_id) : null;

        if ($lead === null) {
            $lead = Lead::query()->create([
                'lead_no' => Lead::generateNumber(),
                'name' => $data['name'] ?? $contact->displayName(),
                'company' => $data['company'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'whatsapp_number' => $data['phone'],
                'source' => 'whatsapp_bot',
                'status' => $data['order_confirmed'] ?? false ? 'won' : ($data['status'] ?? 'qualified'),
                'estimated_value' => $data['estimated_value'] ?? 0,
                'message' => $data['message'] ?? null,
                'product_interest' => $data['product_interest'] ?? null,
                'whatsapp_contact_id' => $contact->id,
                'meta' => ['bot' => true],
            ]);

            $contact->update(['lead_id' => $lead->id]);
        } else {
            $lead->update([
                'status' => ($data['order_confirmed'] ?? false) ? 'won' : ($data['status'] ?? $lead->status),
                'estimated_value' => $data['estimated_value'] ?? $lead->estimated_value,
                'last_contacted_at' => now(),
            ]);
        }

        $conversation = BotConversation::query()->updateOrCreate(
            ['external_id' => $data['external_id'] ?? 'wa-'.$waId],
            [
                'whatsapp_contact_id' => $contact->id,
                'lead_id' => $lead->id,
                'contact_name' => $data['name'] ?? $contact->displayName(),
                'contact_phone' => $data['phone'],
                'status' => ($data['order_confirmed'] ?? false) ? 'converted' : 'active',
                'language' => $data['language'] ?? null,
                'summary' => $data['summary'] ?? null,
                'transcript' => $data['transcript'] ?? null,
                'order_data' => $data['order_data'] ?? null,
                'started_at' => now(),
                'ended_at' => ($data['order_confirmed'] ?? false) ? now() : null,
            ],
        );

        $lead->update(['bot_conversation_id' => $conversation->id]);

        Activity::log('bot_lead', 'Bot pushed a lead: '.$lead->name, $lead, [], 'whatsapp');

        return response()->json([
            'status' => 'ok',
            'lead_id' => $lead->id,
            'lead_no' => $lead->lead_no,
            'conversation_id' => $conversation->id,
        ]);
    }

    public function knowledge(Request $request): JsonResponse
    {
        $secret = (string) Setting::getValue('integrations.bot_shared_secret', '');

        if ($secret !== '' && ! hash_equals($secret, (string) $request->header('X-Bot-Secret'))) {
            return response()->json(['message' => 'Invalid bot secret.'], 401);
        }

        $articles = KnowledgeArticle::query()
            ->where('is_active', true)
            ->where('share_with_bot', true)
            ->orderByDesc('priority')
            ->get(['title', 'category', 'type', 'content', 'tags']);

        $products = Product::query()
            ->where('is_active', true)
            ->where('show_to_bot', true)
            ->with('media')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'type' => $product->type,
                'price' => (float) $product->price,
                'currency' => Setting::getValue('company.currency', 'USD'),
                'description' => $product->bot_description ?: $product->description,
                'specs' => $product->specs,
                'warranty' => $product->warranty,
                'stock' => $product->stock,
                'images' => $product->media->where('type', 'image')->map->url()->values(),
                'videos' => $product->media->where('type', 'video')->map->url()->values(),
            ]);

        return response()->json([
            'bot' => [
                'name' => Setting::getValue('bot.name', 'Cyprus Assistant'),
                'tone' => Setting::getValue('bot.tone', 'friendly-professional'),
                'languages' => Setting::getValue('bot.languages', 'en'),
                'instructions' => Setting::getValue('bot.system_instructions', ''),
                'greeting' => Setting::getValue('bot.greeting', ''),
                'fallback_message' => Setting::getValue('bot.fallback_message', ''),
                'handoff_number' => Setting::getValue('bot.handoff_number', ''),
                'send_products_media' => Setting::boolean('bot.send_products_media', true),
            ],
            'knowledge' => $articles,
            'products' => $products,
            'company' => [
                'name' => Setting::getValue('company.name', Branding::NAME),
                'currency' => Setting::getValue('company.currency', 'USD'),
                'email' => Setting::getValue('company.email', ''),
                'phone' => Setting::getValue('company.phone', ''),
            ],
            'lead_endpoint' => url('/api/bot/lead'),
        ]);
    }

    private function resolveAccount(array $payload): ?WhatsappAccount
    {
        $phoneNumberId = data_get($payload, 'entry.0.changes.0.value.metadata.phone_number_id');

        if (filled($phoneNumberId)) {
            $account = WhatsappAccount::query()->where('phone_number_id', $phoneNumberId)->first();

            if ($account !== null) {
                return $account;
            }
        }

        return WhatsappAccount::default();
    }

    private function signatureIsValid(Request $request, WhatsappAccount $account): bool
    {
        if (blank($account->app_secret)) {
            return true;
        }

        $signature = (string) $request->header('X-Hub-Signature-256');

        if ($signature === '') {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), (string) $account->app_secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $value
     * @param  array<string, mixed>  $message
     * @return array{contact: WhatsappContact, message: WhatsappMessage}|null
     */
    private function storeIncomingMessage(WhatsappAccount $account, array $value, array $message): ?array
    {
        $waId = (string) ($message['from'] ?? '');

        if ($waId === '') {
            return null;
        }

        $profileName = data_get($value, 'contacts.0.profile.name');
        $type = (string) ($message['type'] ?? 'text');
        $body = data_get($message, 'text.body')
            ?? data_get($message, 'button.text')
            ?? data_get($message, 'interactive.button_reply.title')
            ?? data_get($message, 'interactive.list_reply.title')
            ?? data_get($message, 'image.caption')
            ?? data_get($message, 'video.caption')
            ?? null;
        $caption = data_get($message, 'image.caption') ?? data_get($message, 'video.caption') ?? data_get($message, 'document.caption');
        $mediaId = data_get($message, 'image.id')
            ?? data_get($message, 'video.id')
            ?? data_get($message, 'audio.id')
            ?? data_get($message, 'document.id')
            ?? data_get($message, 'sticker.id');

        $contact = WhatsappContact::query()->updateOrCreate(
            ['whatsapp_account_id' => $account->id, 'wa_id' => $waId],
            [
                'phone' => $waId,
                'profile_name' => $profileName,
                'last_message_at' => now(),
            ],
        );

        if ($contact->name === null && filled($profileName)) {
            $contact->update(['name' => $profileName]);
        }

        $channel = $this->channelFor($contact, $account);

        $mediaPath = null;

        if (filled($mediaId) && in_array($type, ['image', 'video', 'audio', 'document'], true)) {
            $mediaPath = app(WhatsappMediaService::class)->download($account, (string) $mediaId, $type);
        }

        $whatsappMessage = WhatsappMessage::query()->create([
            'whatsapp_account_id' => $account->id,
            'whatsapp_contact_id' => $contact->id,
            'chat_channel_id' => $channel->id,
            'wa_message_id' => data_get($message, 'id'),
            'direction' => 'in',
            'type' => $type,
            'body' => $body,
            'caption' => $caption,
            'media_id' => $mediaId,
            'media_url' => $mediaPath,
            'mime_type' => data_get($message, $type.'.mime_type'),
            'filename' => data_get($message, 'document.filename'),
            'status' => 'received',
            'raw' => $message,
            'sent_at' => now(),
        ]);

        Message::query()->create([
            'chat_channel_id' => $channel->id,
            'user_id' => null,
            'type' => in_array($type, ['image', 'video', 'audio', 'document'], true) ? $type : 'text',
            'body' => $body ?? '[Media]',
            'attachment_path' => $mediaPath,
            'attachment_name' => data_get($message, 'document.filename'),
            'meta' => ['whatsapp' => true, 'wa_id' => $waId],
            'sent_at' => now(),
        ]);

        $channel->update(['last_message_at' => now()]);

        if (Setting::boolean('whatsapp.auto_create_lead', true) && $contact->lead_id === null) {
            $lead = Lead::query()->create([
                'lead_no' => Lead::generateNumber(),
                'name' => $contact->displayName(),
                'phone' => $contact->phone,
                'whatsapp_number' => $contact->phone ?? $waId,
                'source' => 'whatsapp',
                'status' => 'new',
                'whatsapp_contact_id' => $contact->id,
                'message' => Str::limit((string) $body, 500),
            ]);

            $contact->update(['lead_id' => $lead->id]);
        }

        return ['contact' => $contact, 'message' => $whatsappMessage];
    }

    private function channelFor(WhatsappContact $contact, WhatsappAccount $account): ChatChannel
    {
        if ($contact->chat_channel_id !== null) {
            $channel = ChatChannel::query()->find($contact->chat_channel_id);

            if ($channel !== null) {
                return $channel;
            }
        }

        $channel = ChatChannel::query()->create([
            'type' => ChatChannel::TYPE_WHATSAPP,
            'name' => $contact->displayName(),
            'whatsapp_contact_id' => $contact->id,
            'last_message_at' => now(),
            'meta' => ['wa_id' => $contact->wa_id, 'account_id' => $account->id],
        ]);

        $contact->update(['chat_channel_id' => $channel->id]);

        if ($account->is_default) {
            User::query()
                ->where('is_super_admin', true)
                ->get()
                ->each(fn (User $admin) => $channel->memberRecords()->firstOrCreate(
                    ['user_id' => $admin->id],
                    ['role' => 'admin', 'joined_at' => now()],
                ));
        }

        return $channel;
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function updateStatus(array $status): void
    {
        $message = WhatsappMessage::query()->where('wa_message_id', $status['id'] ?? '')->first();

        $message?->update(['status' => (string) ($status['status'] ?? 'sent')]);
    }

    private function runBot(WhatsappAccount $account, WhatsappContact $contact, WhatsappMessage $message): void
    {
        try {
            app(BotEngine::class)->handleIncoming($account, $contact, $message);
        } catch (\Throwable $e) {
            Log::error('Bot engine failed', [
                'account_id' => $account->id,
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * WhatChimp payloads may arrive flat or wrapped in "data" / "payload".
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function unwrapWhatchimpPayload(array $payload): array
    {
        foreach (['data', 'payload', 'message_data'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_merge($payload, $payload[$key]);
            }
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveWhatchimpAccount(array $data): ?WhatsappAccount
    {
        $phoneNumberId = $this->firstFilled($data, ['phone_number_id', 'phone_id']);

        if (filled($phoneNumberId)) {
            $account = WhatsappAccount::query()->where('phone_number_id', (string) $phoneNumberId)->first();

            if ($account !== null) {
                return $account;
            }
        }

        return WhatsappAccount::default();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return null;
    }

    /**
     * @return array{contact: WhatsappContact, message: WhatsappMessage}
     */
    private function storeWhatchimpMessage(
        WhatsappAccount $account,
        string $waId,
        ?string $name,
        string $direction,
        string $type,
        ?string $body,
        ?string $mediaUrl,
    ): array {
        $contact = WhatsappContact::query()->updateOrCreate(
            ['whatsapp_account_id' => $account->id, 'wa_id' => $waId],
            [
                'phone' => $waId,
                'profile_name' => $name,
                'last_message_at' => now(),
            ],
        );

        if ($contact->name === null && filled($name)) {
            $contact->update(['name' => $name]);
        }

        $channel = $this->channelFor($contact, $account);

        $message = WhatsappMessage::query()->create([
            'whatsapp_account_id' => $account->id,
            'whatsapp_contact_id' => $contact->id,
            'chat_channel_id' => $channel->id,
            'direction' => $direction,
            'type' => $type,
            'body' => $body,
            'media_url' => $mediaUrl,
            'status' => $direction === 'in' ? 'received' : 'sent',
            'sent_at' => now(),
        ]);

        Message::query()->create([
            'chat_channel_id' => $channel->id,
            'user_id' => null,
            'type' => in_array($type, ['image', 'video', 'audio', 'document'], true) ? $type : 'text',
            'body' => $body ?? '[Media]',
            'attachment_path' => $mediaUrl,
            'meta' => ['whatsapp' => true, 'wa_id' => $waId, 'provider' => 'whatchimp'],
            'sent_at' => now(),
        ]);

        $channel->update(['last_message_at' => now()]);

        if ($direction === 'in' && Setting::boolean('whatsapp.auto_create_lead', true) && $contact->lead_id === null) {
            $lead = Lead::query()->create([
                'lead_no' => Lead::generateNumber(),
                'name' => $contact->displayName(),
                'phone' => $contact->phone,
                'whatsapp_number' => $contact->phone ?? $waId,
                'source' => 'whatsapp',
                'status' => 'new',
                'whatsapp_contact_id' => $contact->id,
                'message' => Str::limit((string) $body, 500),
            ]);

            $contact->update(['lead_id' => $lead->id]);
        }

        return ['contact' => $contact, 'message' => $message];
    }
}
