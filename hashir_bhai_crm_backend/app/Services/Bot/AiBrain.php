<?php

namespace App\Services\Bot;

use App\Models\BotConversation;
use App\Models\KnowledgeArticle;
use App\Models\Organization;
use App\Models\Product;
use App\Models\WhatsappContact;
use App\Support\BotSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiBrain
{
    /**
     * Ask the configured AI provider (OpenAI-compatible) for a reply.
     *
     * @return array{reply: ?string, escalate: bool, order: ?array<string, mixed>}|null
     */
    public function respond(?Organization $organization, WhatsappContact $contact, BotConversation $conversation, string $body): ?array
    {
        $apiKey = BotSettings::aiApiKey($organization);

        if (blank($apiKey)) {
            return null;
        }

        $baseUrl = rtrim((string) BotSettings::get($organization, 'bot.ai_base_url', 'https://api.openai.com/v1'), '/');
        $model = (string) BotSettings::get($organization, 'bot.ai_model', 'gpt-4o-mini');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->acceptJson()
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.4,
                    'messages' => $this->messages($organization, $conversation, $body),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Bot AI request failed', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Bot AI response failed', ['status' => $response->status()]);

            return null;
        }

        $content = (string) $response->json('choices.0.message.content', '');

        if (trim($content) === '') {
            return null;
        }

        return $this->parse($content);
    }

    /**
     * Transcribe a voice note with an OpenAI-compatible audio endpoint (Whisper).
     */
    public function transcribe(?Organization $organization, string $mediaPath): ?string
    {
        $apiKey = BotSettings::aiApiKey($organization);

        if (blank($apiKey)) {
            return null;
        }

        try {
            if (str_starts_with($mediaPath, 'http')) {
                $download = Http::timeout(30)->get($mediaPath);

                if ($download->failed()) {
                    return null;
                }

                $contents = $download->body();
            } else {
                $contents = Storage::disk('public')->get($mediaPath);
            }
        } catch (\Throwable $e) {
            Log::warning('Bot voice download failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (blank($contents)) {
            return null;
        }

        $baseUrl = rtrim((string) BotSettings::get($organization, 'bot.ai_base_url', 'https://api.openai.com/v1'), '/');
        $model = (string) BotSettings::get($organization, 'bot.ai_transcribe_model', 'whisper-1');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->attach('file', $contents, 'voice.ogg')
                ->post($baseUrl.'/audio/transcriptions', [
                    'model' => $model,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Bot transcription request failed', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Bot transcription response failed', ['status' => $response->status()]);

            return null;
        }

        $text = (string) $response->json('text', '');

        return trim($text) !== '' ? trim($text) : null;
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function messages(?Organization $organization, BotConversation $conversation, string $body): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($organization)],
        ];

        $history = collect($conversation->transcript ?? [])
            ->take(-12)
            ->filter(fn (mixed $entry): bool => is_array($entry) && filled($entry['text'] ?? null))
            ->map(fn (array $entry): array => [
                'role' => ($entry['role'] ?? '') === 'customer' ? 'user' : 'assistant',
                'content' => (string) $entry['text'],
            ])
            ->values()
            ->all();

        // Drop the last entry (the current message is appended separately).
        if (count($history) > 0 && end($history)['content'] === $body) {
            array_pop($history);
        }

        return [...$messages, ...$history, ['role' => 'user', 'content' => $body]];
    }

    private function systemPrompt(?Organization $organization): string
    {
        $currency = $organization?->currency ?? 'USD';
        $business = $organization?->name ?? (string) config('app.name');

        $products = Product::query()
            ->forOrganization($organization?->id)
            ->where('is_active', true)
            ->where('show_to_bot', true)
            ->limit(60)
            ->get();

        $articles = KnowledgeArticle::query()
            ->forOrganization($organization?->id)
            ->where('is_active', true)
            ->where('share_with_bot', true)
            ->orderByDesc('priority')
            ->limit(40)
            ->get();

        $catalog = $products
            ->map(fn (Product $product): string => '- '.$product->name.' | '.number_format((float) $product->price, 2).' '.$currency.' | '.Str::limit(strip_tags((string) ($product->bot_description ?: $product->description)), 160))
            ->implode("\n");

        $knowledge = $articles
            ->map(fn (KnowledgeArticle $article): string => '### '.$article->title."\n".Str::limit(strip_tags((string) $article->content), 500))
            ->implode("\n\n");

        $name = (string) BotSettings::get($organization, 'bot.name', 'Assistant');
        $tone = (string) BotSettings::get($organization, 'bot.tone', 'friendly-professional');
        $languages = (string) BotSettings::get($organization, 'bot.languages', 'en');
        $instructions = (string) BotSettings::get($organization, 'bot.system_instructions', '');
        $orderEnabled = BotSettings::boolean($organization, 'bot.order_enabled', true);

        $prompt = "You are {$name}, the WhatsApp assistant for {$business}.\n"
            ."Tone: {$tone}. Always reply in the customer's language (supported: {$languages}).\n"
            ."Rules:\n"
            ."- Use ONLY the catalog and knowledge below. Never invent products, prices or promises.\n"
            ."- Keep replies short (2-4 sentences) and WhatsApp friendly. No markdown headers.\n"
            ."- If asked for a human, or you cannot help, add a line containing exactly [[ESCALATE]].\n";

        if ($orderEnabled) {
            $prompt .= "- When the customer confirms an order, confirm the items, quantity and total, then add on its own line exactly:\n"
                ."[[ORDER]] {\"items\":[{\"name\":\"Product\",\"quantity\":1,\"unit_price\":0}],\"total\":0} [[/ORDER]]\n";
        }

        if ($instructions !== '') {
            $prompt .= "\nBusiness instructions:\n{$instructions}\n";
        }

        if ($catalog !== '') {
            $prompt .= "\nCatalog (name | price | description):\n{$catalog}\n";
        }

        if ($knowledge !== '') {
            $prompt .= "\nKnowledge base:\n{$knowledge}\n";
        }

        return $prompt;
    }

    /**
     * @return array{reply: ?string, escalate: bool, order: ?array<string, mixed>}|null
     */
    private function parse(string $content): ?array
    {
        $escalate = str_contains($content, '[[ESCALATE]]');
        $order = null;

        if (preg_match('/\[\[ORDER\]\](.*?)\[\[\/ORDER\]\]/s', $content, $matches) === 1) {
            $decoded = json_decode(trim($matches[1]), true);

            if (is_array($decoded)) {
                $order = $decoded;
            }

            $content = str_replace($matches[0], '', $content);
        }

        $content = trim(str_replace('[[ESCALATE]]', '', $content));

        if ($content === '' && $order === null && ! $escalate) {
            return null;
        }

        return [
            'reply' => $content !== '' ? $content : null,
            'escalate' => $escalate,
            'order' => $order,
            'media' => null,
        ];
    }
}
