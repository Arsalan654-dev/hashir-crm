<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class BotSettings
{
    /**
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'bot.name' => 'Assistant',
        'bot.tone' => 'friendly-professional',
        'bot.languages' => 'en',
        'bot.system_instructions' => '',
        'bot.greeting' => 'Assalam-o-Alaikum! Welcome to our business. How can I help you today?',
        'bot.fallback_message' => 'Sorry, I could not understand that. Type "agent" anytime and a team member will help you.',
        'bot.handoff_number' => '',
        'bot.send_products_media' => '1',
        'bot.enabled' => '0',
        'bot.ai_enabled' => '0',
        'bot.ai_base_url' => 'https://api.openai.com/v1',
        'bot.ai_model' => 'gpt-4o-mini',
        'bot.escalation_keywords' => 'human,agent,support,representative,insan,band karo,talk to someone,help me',
        'bot.order_enabled' => '1',
        'kitchen.ask_payment' => '1',
        'kitchen.msg_confirmed' => 'Hi {name}! Your order {order_no} is confirmed. Total: {total}. We will start preparing it shortly. Thank you!',
        'kitchen.msg_preparing' => 'Good news {name}! Your order {order_no} is being prepared right now. We will update you once it is ready.',
        'kitchen.msg_ready' => 'Your order {order_no} is ready! It will be out for delivery shortly.',
        'kitchen.msg_dispatched' => 'Your order {order_no} is on the way! Our rider will reach you soon.',
        'kitchen.msg_delivered' => 'Your order {order_no} has been delivered. Thank you for ordering with us! Your invoice is attached below.',
        'kitchen.msg_cancelled' => 'Your order {order_no} has been cancelled. If this was a mistake, please message us.',
        'kitchen.msg_payment_cod' => 'Great — cash on delivery. Please keep {total} ready for our rider.',
        'kitchen.msg_payment_online' => 'You can pay online using this secure link: {payment_link}',
        'kitchen.msg_payment_question' => 'How would you like to pay? Reply CASH for cash on delivery, or ONLINE to get a payment link.',
    ];

    public static function get(?Organization $organization, string $key, mixed $default = null): mixed
    {
        if ($organization !== null) {
            $value = data_get($organization->settings, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $global = Setting::getValue($key);

        if ($global !== null && $global !== '') {
            return $global;
        }

        return $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function boolean(?Organization $organization, string $key, bool $default = false): bool
    {
        $value = static::get($organization, $key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function put(?Organization $organization, array $values): void
    {
        if (array_key_exists('bot.ai_api_key', $values) && filled($values['bot.ai_api_key'])) {
            $values['bot.ai_api_key'] = Crypt::encryptString((string) $values['bot.ai_api_key']);
        } elseif (array_key_exists('bot.ai_api_key', $values)) {
            unset($values['bot.ai_api_key']);
        }

        if ($organization !== null) {
            $settings = $organization->settings ?? [];

            foreach ($values as $key => $value) {
                data_set($settings, $key, $value);
            }

            $organization->update(['settings' => $settings]);

            return;
        }

        foreach ($values as $key => $value) {
            Setting::setValue($key, is_scalar($value) ? (string) $value : json_encode($value), 'bot');
        }
    }

    public static function aiApiKey(?Organization $organization): ?string
    {
        $raw = static::get($organization, 'bot.ai_api_key');

        if (blank($raw)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $raw);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function aiIsConfigured(?Organization $organization): bool
    {
        return static::boolean($organization, 'bot.ai_enabled') && filled(static::aiApiKey($organization));
    }

    /**
     * @return array<int, string>
     */
    public static function escalationKeywords(?Organization $organization): array
    {
        $raw = (string) static::get($organization, 'bot.escalation_keywords');

        return collect(explode(',', $raw))
            ->map(fn (string $word): string => mb_strtolower(trim($word)))
            ->filter()
            ->values()
            ->all();
    }
}
