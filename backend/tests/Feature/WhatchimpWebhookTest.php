<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatchimpWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function account(bool $botEnabled = false): WhatsappAccount
    {
        return WhatsappAccount::query()->create([
            'name' => 'Webhook Account',
            'provider' => 'whatchimp',
            'api_key' => 'test-api-token',
            'phone_number_id' => '987654321',
            'is_default' => true,
            'bot_enabled' => $botEnabled,
            'status' => 'active',
        ]);
    }

    public function test_incoming_webhook_stores_contact_and_message(): void
    {
        $account = $this->account();

        $response = $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => $account->phone_number_id,
            'phone_number' => '923001234567',
            'name' => 'Ali Customer',
            'message' => 'Hello, do you deliver?',
            'message_type' => 'text',
            'timestamp' => now()->timestamp,
        ]);

        $response->assertOk()->assertJsonPath('status', 'ok');

        $this->assertDatabaseHas('whatsapp_contacts', [
            'whatsapp_account_id' => $account->id,
            'wa_id' => '923001234567',
            'name' => 'Ali Customer',
        ]);

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'direction' => 'in',
            'body' => 'Hello, do you deliver?',
        ]);
    }

    public function test_wrapped_payload_is_parsed(): void
    {
        $account = $this->account();

        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'data' => [
                'phone_number_id' => $account->phone_number_id,
                'phone_number' => '923009999999',
                'message' => 'Wrapped message',
                'message_type' => 'text',
            ],
        ])->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'body' => 'Wrapped message',
            'direction' => 'in',
        ]);
    }

    public function test_incoming_webhook_triggers_bot_when_enabled(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
            ], 200),
        ]);

        $account = $this->account(botEnabled: true);

        KnowledgeArticle::query()->create([
            'title' => 'Payment Methods',
            'category' => 'faq',
            'type' => 'faq',
            'content' => 'We accept cash on delivery and online cards.',
            'tags' => 'payment',
            'priority' => 10,
            'is_active' => true,
            'share_with_bot' => true,
        ]);

        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => $account->phone_number_id,
            'phone_number' => '923001234567',
            'message' => 'Which payment methods do you accept?',
            'message_type' => 'text',
        ])->assertOk();

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/whatsapp/send')
            && str_contains((string) $request['message'], 'cash on delivery'));

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'direction' => 'out',
        ]);
    }

    public function test_outgoing_webhook_is_recorded_without_bot_reply(): void
    {
        Http::fake();

        $account = $this->account(botEnabled: true);

        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => $account->phone_number_id,
            'phone_number' => '923001234567',
            'message' => 'This was sent by an agent',
            'message_type' => 'text',
            'direction' => 'out',
        ])->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'direction' => 'out',
            'body' => 'This was sent by an agent',
        ]);

        Http::assertNothingSent();
    }

    public function test_webhook_falls_back_to_the_default_account(): void
    {
        $account = $this->account();

        $response = $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => 'unknown-id',
            'phone_number' => '923001111222',
            'message' => 'hello from fallback',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'direction' => 'in',
            'body' => 'hello from fallback',
        ]);
    }

    public function test_media_message_is_stored_with_media_url(): void
    {
        $account = $this->account();

        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => $account->phone_number_id,
            'phone_number' => '923001234567',
            'message_type' => 'image',
            'media_url' => 'https://example.com/photo.jpg',
        ])->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'type' => 'image',
            'media_url' => 'https://example.com/photo.jpg',
        ]);

        $this->assertInstanceOf(WhatsappContact::class, WhatsappContact::query()->first());
        $this->assertInstanceOf(WhatsappMessage::class, WhatsappMessage::query()->first());
    }
}
