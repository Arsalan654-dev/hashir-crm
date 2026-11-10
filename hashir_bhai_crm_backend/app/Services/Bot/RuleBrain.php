<?php

namespace App\Services\Bot;

use App\Models\BotConversation;
use App\Models\KnowledgeArticle;
use App\Models\Organization;
use App\Models\Product;
use App\Models\WhatsappContact;
use App\Support\BotSettings;

class RuleBrain
{
    /**
     * Simple keyword based brain used when no AI provider is configured.
     *
     * @return array{reply: ?string, escalate: bool, order: ?array<string, mixed>, media: ?array<string, string>}
     */
    public function respond(?Organization $organization, WhatsappContact $contact, string $body, BotConversation $conversation): array
    {
        $lower = mb_strtolower($body);

        if ($this->isGreeting($lower)) {
            $greeting = (string) BotSettings::get($organization, 'bot.greeting', '');

            return [
                'reply' => $greeting !== ''
                    ? $greeting
                    : 'Assalam-o-Alaikum! Welcome. How can I help you today? You can ask about our products, prices or place an order.',
                'escalate' => false,
                'order' => null,
                'media' => null,
            ];
        }

        $product = $this->findProduct($organization, $lower);

        if ($product !== null) {
            return $this->productReply($organization, $product, $lower);
        }

        $article = $this->findArticle($organization, $lower);

        if ($article !== null) {
            return [
                'reply' => trim($article->title.":\n".strip_tags((string) $article->content)),
                'escalate' => false,
                'order' => null,
                'media' => null,
            ];
        }

        if ($this->recentFallbackCount($conversation) >= 2) {
            return [
                'reply' => 'Let me connect you with a team member who can help you better. They will reply here shortly.',
                'escalate' => true,
                'order' => null,
                'media' => null,
            ];
        }

        return [
            'reply' => (string) BotSettings::get($organization, 'bot.fallback_message', 'Sorry, I could not understand that. Type "agent" and a team member will help you.'),
            'escalate' => false,
            'order' => null,
            'media' => null,
        ];
    }

    private function isGreeting(string $lower): bool
    {
        foreach (['hi', 'hello', 'hey', 'salam', 'assalam', 'aoa', 'good morning', 'good evening'] as $greeting) {
            if (preg_match('/\b'.preg_quote($greeting, '/').'\b/u', $lower) === 1) {
                return true;
            }
        }

        return false;
    }

    private function findProduct(?Organization $organization, string $lower): ?Product
    {
        $words = collect(preg_split('/[^a-z0-9]+/', $lower) ?: [])
            ->filter(fn (string $word): bool => mb_strlen($word) >= 3)
            ->reject(fn (string $word): bool => in_array($word, [
                'the', 'and', 'for', 'with', 'you', 'your', 'have', 'want', 'need', 'please',
                'price', 'cost', 'order', 'buy', 'give', 'show', 'what', 'how', 'much', 'this',
                'that', 'can', 'are', 'our', 'any', 'available', 'availability', 'kya', 'hai',
            ], true))
            ->values();

        if ($words->isEmpty()) {
            return null;
        }

        return Product::query()
            ->forOrganization($organization?->id)
            ->where('is_active', true)
            ->where(function ($query) use ($words): void {
                foreach ($words as $word) {
                    $query->orWhere('name', 'like', '%'.$word.'%')
                        ->orWhere('bot_description', 'like', '%'.$word.'%');
                }
            })
            ->orderByDesc('is_featured')
            ->first();
    }

    private function findArticle(?Organization $organization, string $lower): ?KnowledgeArticle
    {
        $words = collect(preg_split('/[^a-z0-9]+/', $lower) ?: [])
            ->filter(fn (string $word): bool => mb_strlen($word) >= 4)
            ->values();

        if ($words->isEmpty()) {
            return null;
        }

        return KnowledgeArticle::query()
            ->forOrganization($organization?->id)
            ->where('is_active', true)
            ->where('share_with_bot', true)
            ->where(function ($query) use ($words): void {
                foreach ($words as $word) {
                    $query->orWhere('title', 'like', '%'.$word.'%')
                        ->orWhere('tags', 'like', '%'.$word.'%')
                        ->orWhere('content', 'like', '%'.$word.'%');
                }
            })
            ->orderByDesc('priority')
            ->first();
    }

    /**
     * @return array{reply: ?string, escalate: bool, order: ?array<string, mixed>, media: ?array<string, string>}
     */
    private function productReply(?Organization $organization, Product $product, string $lower): array
    {
        $currency = $organization?->currency ?? 'USD';
        $price = number_format((float) $product->price, 2).' '.$currency;

        $reply = $product->name.' — '.$price."\n".trim((string) ($product->bot_description ?: $product->description));

        $media = BotSettings::boolean($organization, 'bot.send_products_media', true)
            ? ['type' => 'image', 'url' => $product->primaryImageUrl()]
            : null;

        if ($this->hasOrderIntent($lower) && BotSettings::boolean($organization, 'bot.order_enabled', true)) {
            $quantity = $this->detectQuantity($lower);

            $reply .= "\n\nOrder received: ".$quantity.' x '.$product->name.' — '.number_format((float) $product->price * $quantity, 2).' '.$currency
                ."\nOur team will confirm your order and payment shortly.";

            return [
                'reply' => $reply,
                'escalate' => false,
                'order' => [
                    'items' => [[
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => (float) $product->price,
                    ]],
                    'total' => (float) $product->price * $quantity,
                    'currency' => $currency,
                    'source' => 'rule_brain',
                ],
                'media' => $media,
            ];
        }

        return ['reply' => $reply, 'escalate' => false, 'order' => null, 'media' => $media];
    }

    private function hasOrderIntent(string $lower): bool
    {
        foreach (['order', 'buy', 'book', 'confirm', 'chahiye', 'chaiye', 'lena', 'place'] as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function detectQuantity(string $lower): int
    {
        if (preg_match('/(\d{1,3})\s*(x|pcs|pieces|units|plates|qty|pack|packs)?/', $lower, $matches) === 1) {
            return max(1, min(99, (int) $matches[1]));
        }

        return 1;
    }

    private function recentFallbackCount(BotConversation $conversation): int
    {
        $transcript = collect($conversation->transcript ?? []);

        return $transcript
            ->reverse()
            ->take(6)
            ->filter(fn (mixed $entry): bool => is_array($entry) && ($entry['role'] ?? null) === 'bot')
            ->filter(function (array $entry): bool {
                $text = mb_strtolower((string) ($entry['text'] ?? ''));

                return str_contains($text, 'could not understand')
                    || str_contains($text, 'type "agent"')
                    || str_contains($text, 'sorry');
            })
            ->count();
    }
}
