<?php

namespace App\Services\Bot;

use App\Models\BotConversation;
use App\Models\KitchenOrder;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Services\KitchenOrderService;
use App\Services\WhatsappService;
use App\Support\Activity;
use App\Support\BotSettings;
use App\Support\Notifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BotEngine
{
    public function __construct(
        private readonly WhatsappService $whatsapp,
        private readonly KitchenOrderService $kitchenOrders,
    ) {}

    public function isEnabled(WhatsappAccount $account): bool
    {
        if (! $account->bot_enabled) {
            return false;
        }

        $organization = $account->organization;

        if ($organization !== null && $organization->botIsBlocked()) {
            return false;
        }

        return true;
    }

    public function handleIncoming(WhatsappAccount $account, WhatsappContact $contact, WhatsappMessage $message): void
    {
        if (! $this->isEnabled($account)) {
            return;
        }

        $organization = $account->organization;
        $conversation = $this->conversationFor($account, $contact);

        $body = trim((string) ($message->body ?? ''));

        $this->appendTranscript($conversation, 'customer', $body !== '' ? $body : '['.$message->type.']');

        // A human agent is handling this conversation — keep the bot quiet.
        if ($conversation->status === 'escalated') {
            $this->notifySupportOfReply($account, $contact, $conversation, $body);

            return;
        }

        // A resolved conversation starts fresh with the bot.
        if ($conversation->status === 'resolved') {
            $conversation->update([
                'status' => 'active',
                'resolved_at' => null,
                'handled_by' => null,
                'ended_at' => null,
            ]);
        }

        if ($body === '' && $message->type === 'audio') {
            $transcript = $this->transcribeAudio($account, $message);

            if (filled($transcript)) {
                $body = trim((string) $transcript);
                $this->appendTranscript($conversation, 'customer', '🎤 '.$body);
            }
        }

        if ($body === '') {
            $this->reply(
                $account,
                $contact,
                $conversation,
                'Thanks for your message! Our team will review it shortly. Type your question if you would like an instant reply.',
            );

            return;
        }

        if (($conversation->order_data['awaiting'] ?? null) === 'payment_method') {
            $this->handlePaymentMethodReply($account, $contact, $conversation, $body);

            return;
        }

        $decision = $this->decide($organization, $contact, $body, $conversation);

        if (is_array($decision['order'] ?? null) && $decision['order'] !== []) {
            $kitchenOrder = $this->captureOrder($account, $contact, $conversation, $decision['order']);

            if ($kitchenOrder !== null && BotSettings::boolean($organization, 'kitchen.ask_payment', true)) {
                $conversation->update(['order_data' => array_merge($decision['order'], [
                    'awaiting' => 'payment_method',
                    'awaiting_attempts' => 0,
                    'kitchen_order_id' => $kitchenOrder->id,
                ])]);

                $reply = trim(
                    (string) $decision['reply']."\n\n".(string) BotSettings::get($organization, 'kitchen.msg_payment_question'),
                );

                $this->replyWithMedia($account, $contact, $conversation, $reply, $decision['media'] ?? null);

                return;
            }
        }

        if ($decision['escalate']) {
            $this->escalate($account, $contact, $conversation, $body);
        }

        if (filled($decision['reply']) || is_array($decision['media'] ?? null)) {
            $this->replyWithMedia(
                $account,
                $contact,
                $conversation,
                $decision['reply'] !== null ? (string) $decision['reply'] : null,
                $decision['media'] ?? null,
            );
        }
    }

    /**
     * @return array{reply: ?string, escalate: bool, order: ?array<string, mixed>}
     */
    private function decide(?Organization $organization, WhatsappContact $contact, string $body, BotConversation $conversation): array
    {
        $lower = mb_strtolower($body);

        foreach (BotSettings::escalationKeywords($organization) as $keyword) {
            if ($keyword !== '' && str_contains($lower, $keyword)) {
                return [
                    'reply' => 'Of course — I am connecting you with a team member. They will reply here shortly.',
                    'escalate' => true,
                    'order' => null,
                ];
            }
        }

        if (BotSettings::aiIsConfigured($organization)) {
            $ai = app(AiBrain::class)->respond($organization, $contact, $conversation, $body);

            if ($ai !== null) {
                return $ai;
            }
        }

        return app(RuleBrain::class)->respond($organization, $contact, $body, $conversation);
    }

    /**
     * @param  array<string, mixed>  $order
     */
    private function captureOrder(WhatsappAccount $account, WhatsappContact $contact, BotConversation $conversation, array $order): ?KitchenOrder
    {
        $organization = $account->organization;

        $items = collect($order['items'] ?? [])
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['name'] ?? null))
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        $total = (float) ($order['total'] ?? $items->sum(
            fn (array $item): float => (float) ($item['unit_price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1)),
        ));

        $summaryLines = $items
            ->map(fn (array $item): string => max(1, (int) ($item['quantity'] ?? 1)).' x '.$item['name'].' — '.number_format((float) ($item['unit_price'] ?? 0), 2))
            ->implode("\n");

        $lead = $contact->lead_id !== null ? Lead::query()->find($contact->lead_id) : null;

        if ($lead === null) {
            $lead = Lead::query()->create([
                'organization_id' => $organization?->id,
                'lead_no' => Lead::generateNumber(),
                'name' => $contact->displayName(),
                'phone' => $contact->phone ?? $contact->wa_id,
                'whatsapp_number' => $contact->phone ?? $contact->wa_id,
                'source' => 'whatsapp_bot',
                'status' => 'qualified',
                'estimated_value' => $total,
                'currency' => $organization?->currency ?? 'USD',
                'message' => "WhatsApp bot order:\n".$summaryLines,
                'whatsapp_contact_id' => $contact->id,
                'meta' => ['bot' => true, 'order' => $order],
            ]);

            $contact->update(['lead_id' => $lead->id]);
        } else {
            $lead->update([
                'status' => 'qualified',
                'estimated_value' => $total,
                'message' => trim(((string) $lead->message)."\n\nWhatsApp bot order:\n".$summaryLines),
                'last_contacted_at' => now(),
            ]);
        }

        $kitchenOrder = $this->kitchenOrders->createFromBot($account, $contact, $lead, $order);

        $conversation->update([
            'status' => 'converted',
            'lead_id' => $lead->id,
            'order_data' => $order,
            'summary' => $summaryLines,
        ]);

        Activity::log('bot_order', 'Bot captured an order from '.$contact->displayName(), $lead, ['total' => $total], 'whatsapp');

        return $kitchenOrder;
    }

    /**
     * The customer is answering the "cash or online?" question.
     */
    private function handlePaymentMethodReply(WhatsappAccount $account, WhatsappContact $contact, BotConversation $conversation, string $body): void
    {
        $organization = $account->organization;
        $orderData = $conversation->order_data ?? [];
        $order = isset($orderData['kitchen_order_id']) ? KitchenOrder::query()->find((int) $orderData['kitchen_order_id']) : null;

        if ($order === null) {
            $conversation->update(['order_data' => array_diff_key($orderData, ['awaiting' => ''])]);

            $this->reply($account, $contact, $conversation, (string) BotSettings::get($organization, 'bot.fallback_message'));

            return;
        }

        $lower = mb_strtolower($body);
        $isCash = preg_match('/\b(cash|cod|co d|counter)\b/i', $lower) === 1;
        $isOnline = preg_match('/\b(online|card|link|pay|payment|bank|transfer|stripe)\b/i', $lower) === 1;

        if ($isCash || $isOnline) {
            $method = $isCash ? 'cod' : 'online';
            $order->update(['payment_method' => $method]);

            $conversation->update([
                'order_data' => array_diff_key($orderData, ['awaiting' => '', 'awaiting_attempts' => '']),
                'ended_at' => now(),
            ]);

            $this->reply($account, $contact, $conversation, $order->messageFor('payment_'.$method));

            return;
        }

        $attempts = (int) ($orderData['awaiting_attempts'] ?? 0) + 1;

        if ($attempts >= 2) {
            $order->update(['payment_method' => 'cod']);

            $conversation->update([
                'order_data' => array_diff_key($orderData, ['awaiting' => '', 'awaiting_attempts' => '']),
                'ended_at' => now(),
            ]);

            $this->reply($account, $contact, $conversation, $order->messageFor('payment_cod'));

            return;
        }

        $conversation->update(['order_data' => array_merge($orderData, ['awaiting_attempts' => $attempts])]);

        $this->reply($account, $contact, $conversation, (string) BotSettings::get($organization, 'kitchen.msg_payment_question'));
    }

    private function escalate(WhatsappAccount $account, WhatsappContact $contact, BotConversation $conversation, string $body): void
    {
        $organization = $account->organization;

        $conversation->update([
            'status' => 'escalated',
            'escalated_at' => now(),
            'resolved_at' => null,
            'summary' => Str::limit($body, 300),
        ]);

        $lead = $contact->lead_id !== null ? Lead::query()->find($contact->lead_id) : null;

        if ($lead === null) {
            $lead = Lead::query()->create([
                'organization_id' => $organization?->id,
                'lead_no' => Lead::generateNumber(),
                'name' => $contact->displayName(),
                'phone' => $contact->phone ?? $contact->wa_id,
                'whatsapp_number' => $contact->phone ?? $contact->wa_id,
                'source' => 'whatsapp',
                'status' => 'new',
                'priority' => 'high',
                'message' => 'Customer requested a human agent: '.Str::limit($body, 400),
                'whatsapp_contact_id' => $contact->id,
            ]);

            $contact->update(['lead_id' => $lead->id]);
        } else {
            $lead->update(['priority' => 'high', 'last_contacted_at' => now()]);
        }

        $conversation->update(['lead_id' => $lead->id]);

        Activity::log('bot_escalated', 'Bot escalated conversation with '.$contact->displayName(), $lead, [], 'whatsapp');

        $this->notifyStaff(
            $organization,
            'Customer needs a human agent',
            $contact->displayName().' asked for a human on WhatsApp: '.Str::limit($body, 120),
            route('whatsapp.show', $contact),
            'bx bx-user-voice',
        );
    }

    private function reply(WhatsappAccount $account, WhatsappContact $contact, BotConversation $conversation, string $text): void
    {
        $this->whatsapp->for($account)->sendText($contact->wa_id, $text);

        $this->appendTranscript($conversation, 'bot', $text);
    }

    /**
     * @param  array<string, string>|null  $media
     */
    private function replyWithMedia(WhatsappAccount $account, WhatsappContact $contact, BotConversation $conversation, ?string $text, ?array $media): void
    {
        if (is_array($media) && filled($media['url'] ?? null)) {
            $this->whatsapp->for($account)->sendMedia(
                $contact->wa_id,
                (string) ($media['type'] ?? 'image'),
                (string) $media['url'],
                $text,
            );

            $this->appendTranscript($conversation, 'bot', trim(($text ?? '')."\n[".($media['type'] ?? 'image').' sent]'));

            return;
        }

        if (filled($text)) {
            $this->reply($account, $contact, $conversation, (string) $text);
        }
    }

    private function transcribeAudio(WhatsappAccount $account, WhatsappMessage $message): ?string
    {
        if (blank($message->media_url)) {
            return null;
        }

        return app(AiBrain::class)->transcribe($account->organization, (string) $message->media_url);
    }

    /**
     * Tell the support team the customer replied on an escalated conversation.
     */
    private function notifySupportOfReply(WhatsappAccount $account, WhatsappContact $contact, BotConversation $conversation, string $body): void
    {
        $conversation->update([
            'summary' => Str::limit($body !== '' ? $body : '[media message]', 300),
        ]);

        $this->notifyStaff(
            $account->organization,
            'Customer replied — '.$contact->displayName(),
            Str::limit($body !== '' ? $body : '['.$contact->displayName().' sent media]', 140),
            route('support.show', $conversation),
            'bx bx-message-square-dots',
        );
    }

    private function conversationFor(WhatsappAccount $account, WhatsappContact $contact): BotConversation
    {
        return BotConversation::query()->firstOrCreate(
            ['external_id' => 'wa-'.$contact->wa_id],
            [
                'organization_id' => $account->organization_id,
                'whatsapp_contact_id' => $contact->id,
                'lead_id' => $contact->lead_id,
                'contact_name' => $contact->displayName(),
                'contact_phone' => $contact->phone ?? $contact->wa_id,
                'status' => 'active',
                'started_at' => now(),
            ],
        );
    }

    private function appendTranscript(BotConversation $conversation, string $role, string $text): void
    {
        $transcript = $conversation->transcript ?? [];

        $transcript[] = [
            'role' => $role,
            'text' => $text,
            'at' => now()->toIso8601String(),
        ];

        if (count($transcript) > 60) {
            $transcript = array_slice($transcript, -60);
        }

        $conversation->update(['transcript' => $transcript]);
    }

    private function notifyStaff(?Organization $organization, string $title, string $message, string $url, string $icon): void
    {
        Notifier::send($this->staffRecipients($organization), $title, $message, $url, $icon);
    }

    /**
     * @return Collection<int, User>
     */
    private function staffRecipients(?Organization $organization): Collection
    {
        $supportAgents = User::query()
            ->where('is_active', true)
            ->whereHas('assignedRole', fn ($query) => $query->where('slug', 'support-agent'))
            ->get();

        return Notifier::admins()
            ->merge($supportAgents)
            ->merge($organization?->owner !== null ? [$organization->owner] : [])
            ->unique('id')
            ->values();
    }
}
