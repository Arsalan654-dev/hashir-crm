<?php

namespace Tests\Feature;

use App\Models\BotConversation;
use App\Models\KnowledgeArticle;
use App\Models\Lead;
use App\Models\Product;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Notifications\AppNotification;
use App\Services\Bot\BotEngine;
use App\Support\BotSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class BotEngineTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private function account(bool $botEnabled = true): WhatsappAccount
    {
        return WhatsappAccount::query()->create([
            'name' => 'Bot Account',
            'provider' => 'whatchimp',
            'api_key' => 'test-api-token',
            'phone_number_id' => '123456789',
            'is_default' => true,
            'bot_enabled' => $botEnabled,
            'status' => 'active',
        ]);
    }

    private function fakeWhatsapp(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
                'wa_message_id' => 'wamid.BOT1',
            ], 200),
        ]);
    }

    /**
     * @return array{contact: WhatsappContact, message: WhatsappMessage}
     */
    private function incoming(WhatsappAccount $account, string $body, string $waId = '923001234567'): array
    {
        $contact = WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'wa_id' => $waId,
            'phone' => $waId,
            'name' => 'Test Customer',
            'source' => 'whatsapp',
        ]);

        $message = WhatsappMessage::query()->create([
            'whatsapp_account_id' => $account->id,
            'whatsapp_contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'text',
            'body' => $body,
            'status' => 'received',
            'sent_at' => now(),
        ]);

        return ['contact' => $contact, 'message' => $message];
    }

    public function test_rule_brain_replies_from_knowledge_base(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();

        KnowledgeArticle::query()->create([
            'title' => 'Delivery Information',
            'category' => 'faq',
            'type' => 'faq',
            'content' => 'We deliver within 45 minutes across the city.',
            'tags' => 'delivery',
            'priority' => 10,
            'is_active' => true,
            'share_with_bot' => true,
        ]);

        $incoming = $this->incoming($account, 'What is your delivery time?');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/whatsapp/send')
            && str_contains((string) $request['message'], '45 minutes'));

        $conversation = BotConversation::query()->where('external_id', 'wa-923001234567')->first();
        $this->assertNotNull($conversation);
        $this->assertSame('active', $conversation->status);
    }

    public function test_rule_brain_replies_with_product_details(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();

        Product::query()->create([
            'name' => 'Chicken Burger',
            'price' => 450,
            'description' => 'Juicy grilled chicken burger.',
            'is_active' => true,
            'show_to_bot' => true,
        ]);

        $incoming = $this->incoming($account, 'Chicken burger price?');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/whatsapp/send')
            && str_contains((string) $request['message'], 'Chicken Burger')
            && str_contains((string) $request['message'], '450'));
    }

    public function test_rule_brain_captures_order_and_creates_lead(): void
    {
        Notification::fake();
        $this->fakeWhatsapp();

        $admin = $this->superAdmin();
        $account = $this->account();

        Product::query()->create([
            'name' => 'Zinger Burger',
            'price' => 500,
            'description' => 'Spicy zinger.',
            'is_active' => true,
            'show_to_bot' => true,
        ]);

        $incoming = $this->incoming($account, 'I want to order 2 Zinger Burger');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        $lead = Lead::query()->where('source', 'whatsapp_bot')->first();

        $this->assertNotNull($lead);
        $this->assertSame('qualified', $lead->status);
        $this->assertSame('1000.00', $lead->estimated_value);
        $this->assertStringContainsString('2 x Zinger Burger', (string) $lead->message);

        $conversation = BotConversation::query()->where('external_id', 'wa-923001234567')->first();
        $this->assertNotNull($conversation);
        $this->assertSame('converted', $conversation->status);
        $this->assertIsArray($conversation->order_data);

        Notification::assertSentTo($admin, AppNotification::class);
    }

    public function test_escalation_keyword_creates_high_priority_lead_and_notifies(): void
    {
        Notification::fake();
        $this->fakeWhatsapp();

        $admin = $this->superAdmin();
        $account = $this->account();

        $incoming = $this->incoming($account, 'I need a human agent please');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        $conversation = BotConversation::query()->where('external_id', 'wa-923001234567')->first();
        $this->assertNotNull($conversation);
        $this->assertSame('escalated', $conversation->status);

        $lead = Lead::query()->where('whatsapp_contact_id', $incoming['contact']->id)->first();
        $this->assertNotNull($lead);
        $this->assertSame('high', $lead->priority);

        Notification::assertSentTo($admin, AppNotification::class);
    }

    public function test_bot_disabled_does_not_reply(): void
    {
        Http::fake();
        $account = $this->account(botEnabled: false);

        $incoming = $this->incoming($account, 'Hello there');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        Http::assertNothingSent();
        $this->assertDatabaseMissing('bot_conversations', ['external_id' => 'wa-923001234567']);
    }

    public function test_ai_brain_is_used_when_configured(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'AI generated reply for you.']]],
            ], 200),
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
            ], 200),
        ]);

        BotSettings::put(null, [
            'bot.ai_enabled' => '1',
            'bot.ai_api_key' => 'sk-test-key',
            'bot.ai_base_url' => 'https://api.openai.com/v1',
            'bot.ai_model' => 'gpt-4o-mini',
        ]);

        $account = $this->account();
        $incoming = $this->incoming($account, 'Do you have anything special today?');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'api.openai.com')
            && $request['model'] === 'gpt-4o-mini');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/whatsapp/send')
            && str_contains((string) $request['message'], 'AI generated reply'));
    }

    public function test_ai_failure_falls_back_to_rule_brain(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => 'server down'], 500),
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
            ], 200),
        ]);

        BotSettings::put(null, [
            'bot.ai_enabled' => '1',
            'bot.ai_api_key' => 'sk-test-key',
        ]);

        $account = $this->account();

        KnowledgeArticle::query()->create([
            'title' => 'Opening Hours',
            'category' => 'faq',
            'type' => 'faq',
            'content' => 'We are open from 10am to 11pm.',
            'tags' => 'hours',
            'priority' => 5,
            'is_active' => true,
            'share_with_bot' => true,
        ]);

        $incoming = $this->incoming($account, 'What are your opening hours?');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/whatsapp/send')
            && str_contains((string) $request['message'], '10am to 11pm'));
    }
}
