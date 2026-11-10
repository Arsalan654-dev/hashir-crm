<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use App\Models\Product;
use App\Models\Setting;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class WhatsappWebhookTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private function account(): WhatsappAccount
    {
        return WhatsappAccount::query()->create([
            'name' => 'Cyprus WhatsApp',
            'phone_number' => '+35799000000',
            'phone_number_id' => '1234567890',
            'access_token' => 'test-token',
            'verify_token' => 'verify-me',
            'api_version' => 'v21.0',
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    public function test_webhook_verification_returns_the_challenge(): void
    {
        $this->account();

        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=verify-me&hub.challenge=987654321')
            ->assertOk()
            ->assertSee('987654321');
    }

    public function test_webhook_verification_fails_with_a_wrong_token(): void
    {
        $this->account();

        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=nope&hub.challenge=987654321')
            ->assertStatus(403);
    }

    public function test_incoming_message_creates_contact_message_and_lead(): void
    {
        $this->account();

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'waba-id',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['display_phone_number' => '+35799000000', 'phone_number_id' => '1234567890'],
                        'contacts' => [['profile' => ['name' => 'Nadia Buyer'], 'wa_id' => '35798111222']],
                        'messages' => [[
                            'from' => '35798111222',
                            'id' => 'wamid.TEST123',
                            'timestamp' => (string) now()->timestamp,
                            'type' => 'text',
                            'text' => ['body' => 'Hello, I want the ERP package'],
                        ]],
                    ],
                ]],
            ]],
        ];

        $this->postJson('/webhooks/whatsapp', $payload)->assertOk()->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('whatsapp_contacts', ['wa_id' => '35798111222', 'name' => 'Nadia Buyer']);
        $this->assertDatabaseHas('whatsapp_messages', ['wa_message_id' => 'wamid.TEST123', 'direction' => 'in']);

        $contact = WhatsappContact::query()->where('wa_id', '35798111222')->firstOrFail();

        $this->assertNotNull($contact->lead_id);
        $this->assertNotNull($contact->chat_channel_id);
        $this->assertDatabaseHas('leads', ['id' => $contact->lead_id, 'source' => 'whatsapp', 'name' => 'Nadia Buyer']);
        $this->assertDatabaseHas('messages', ['chat_channel_id' => $contact->chat_channel_id]);
    }

    public function test_bot_endpoint_creates_a_lead_and_conversation(): void
    {
        Setting::setValue('integrations.bot_shared_secret', 'bot-secret', 'integrations');

        $response = $this->postJson('/api/bot/lead', [
            'name' => 'Ahmed from Dubai',
            'phone' => '+971501234567',
            'email' => 'ahmed@example.com',
            'message' => 'Needs a CRM with WhatsApp integration',
            'estimated_value' => 4500,
            'external_id' => 'n8n-conv-1',
            'summary' => 'Customer asked about CRM pricing.',
            'order_confirmed' => false,
        ], ['X-Bot-Secret' => 'bot-secret']);

        $response->assertOk()->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('leads', ['name' => 'Ahmed from Dubai', 'source' => 'whatsapp_bot']);
        $this->assertDatabaseHas('bot_conversations', ['external_id' => 'n8n-conv-1', 'contact_phone' => '+971501234567']);
    }

    public function test_bot_endpoint_rejects_invalid_secret(): void
    {
        Setting::setValue('integrations.bot_shared_secret', 'bot-secret', 'integrations');

        $this->postJson('/api/bot/lead', [
            'name' => 'Intruder',
            'phone' => '+971500000000',
        ], ['X-Bot-Secret' => 'wrong'])->assertStatus(401);
    }

    public function test_knowledge_endpoint_returns_bot_configuration_and_products(): void
    {
        Setting::setValue('integrations.bot_shared_secret', 'bot-secret', 'integrations');
        Setting::setValue('bot.name', 'Cyprus Assistant', 'bot');

        KnowledgeArticle::query()->create([
            'title' => 'Pricing',
            'category' => 'pricing',
            'type' => 'policy',
            'content' => 'Starting from $500.',
            'is_active' => true,
            'share_with_bot' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'CRM Starter',
            'price' => 999,
            'is_active' => true,
            'show_to_bot' => true,
            'bot_description' => 'Great for small teams.',
        ]);

        $response = $this->getJson('/api/bot/knowledge', ['X-Bot-Secret' => 'bot-secret']);

        $response->assertOk()
            ->assertJsonPath('bot.name', 'Cyprus Assistant')
            ->assertJsonPath('knowledge.0.title', 'Pricing')
            ->assertJsonFragment(['name' => 'CRM Starter']);
    }

    public function test_outgoing_message_is_stored_when_the_api_accepts_it(): void
    {
        $account = $this->account();

        $user = $this->userWithPermissions(['dashboard.view', 'whatsapp.view', 'whatsapp.send']);

        $contact = WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'wa_id' => '35798111222',
            'phone' => '35798111222',
            'name' => 'Test Contact',
        ]);

        Http::fake([
            '*' => Http::response(['messages' => [['id' => 'wamid.OUT1']]], 200),
        ]);

        $this->actingAs($user)->post('/whatsapp/'.$contact->id.'/send', [
            'body' => 'Thanks for reaching out!',
        ])->assertRedirect();

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_contact_id' => $contact->id,
            'direction' => 'out',
            'body' => 'Thanks for reaching out!',
            'status' => 'sent',
        ]);
    }

    public function test_sending_fails_cleanly_without_configuration(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'whatsapp.view', 'whatsapp.send']);

        $contact = WhatsappContact::query()->create([
            'wa_id' => '35798111333',
            'phone' => '35798111333',
            'name' => 'No Config Contact',
        ]);

        $this->actingAs($user)
            ->post('/whatsapp/'.$contact->id.'/send', ['body' => 'Hello?'])
            ->assertSessionHasErrors('whatsapp');
    }
}
