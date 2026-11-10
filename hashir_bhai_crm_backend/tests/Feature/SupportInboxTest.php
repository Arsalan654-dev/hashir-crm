<?php

namespace Tests\Feature;

use App\Models\BotConversation;
use App\Models\KnowledgeArticle;
use App\Models\Product;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Notifications\AppNotification;
use App\Services\Bot\BotEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class SupportInboxTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private function account(): WhatsappAccount
    {
        return WhatsappAccount::query()->create([
            'name' => 'Support Account',
            'provider' => 'whatchimp',
            'api_key' => 'test-api-token',
            'phone_number_id' => '777000111',
            'is_default' => true,
            'bot_enabled' => true,
            'status' => 'active',
        ]);
    }

    private function fakeWhatsapp(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
            ], 200),
        ]);
    }

    /**
     * @return array{contact: WhatsappContact, message: WhatsappMessage}
     */
    private function incoming(WhatsappAccount $account, string $body): array
    {
        $contact = WhatsappContact::query()->firstOrCreate(
            ['whatsapp_account_id' => $account->id, 'wa_id' => '923009998877'],
            [
                'phone' => '923009998877',
                'name' => 'Support Customer',
                'source' => 'whatsapp',
            ],
        );

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

    public function test_escalated_conversation_appears_in_support_inbox(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();
        $incoming = $this->incoming($account, 'I want to talk to a human');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        $conversation = BotConversation::query()->first();
        $this->assertSame('escalated', $conversation->status);
        $this->assertNotNull($conversation->escalated_at);

        $support = $this->userWithPermissions(['dashboard.view', 'support.view']);

        $this->actingAs($support)
            ->get('/support')
            ->assertOk()
            ->assertSee('Support Customer')
            ->assertSee('Waiting');
    }

    public function test_bot_stays_quiet_after_escalation_and_notifies_support(): void
    {
        Notification::fake();
        $this->fakeWhatsapp();

        $admin = $this->superAdmin();
        $account = $this->account();

        $first = $this->incoming($account, 'agent please');
        app(BotEngine::class)->handleIncoming($account, $first['contact'], $first['message']);

        Notification::fake();

        $requestsBefore = Http::recorded()->count();

        $second = $this->incoming($account, 'hello? are you there?');
        app(BotEngine::class)->handleIncoming($account, $second['contact'], $second['message']);

        // The bot must not send a reply once a human is handling the chat.
        $this->assertSame($requestsBefore, Http::recorded()->count());

        Notification::assertSentTo($admin, AppNotification::class);

        $conversation = BotConversation::query()->first();
        $this->assertSame('escalated', $conversation->status);
        $this->assertStringContainsString('hello?', (string) $conversation->summary);
    }

    public function test_resolving_conversation_lets_bot_resume_on_next_message(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();

        KnowledgeArticle::query()->create([
            'title' => 'Refund Policy',
            'category' => 'faq',
            'type' => 'faq',
            'content' => 'Refunds are processed within 3 working days.',
            'tags' => 'refund',
            'priority' => 5,
            'is_active' => true,
            'share_with_bot' => true,
        ]);

        $first = $this->incoming($account, 'agent please');
        app(BotEngine::class)->handleIncoming($account, $first['contact'], $first['message']);

        $conversation = BotConversation::query()->first();

        $support = $this->userWithPermissions(['dashboard.view', 'support.view', 'support.manage']);

        $this->actingAs($support)
            ->post(route('support.resolve', $conversation))
            ->assertRedirect();

        $this->assertSame('resolved', $conversation->refresh()->status);

        $second = $this->incoming($account, 'What is your refund policy?');
        app(BotEngine::class)->handleIncoming($account, $second['contact'], $second['message']);

        $conversation->refresh();
        $this->assertSame('active', $conversation->status);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], '3 working days'));
    }

    public function test_support_agent_can_reply_and_conversation_records_it(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();
        $first = $this->incoming($account, 'agent please');
        app(BotEngine::class)->handleIncoming($account, $first['contact'], $first['message']);

        $conversation = BotConversation::query()->first();

        $support = $this->userWithPermissions(['dashboard.view', 'support.view', 'support.manage']);

        $this->actingAs($support)
            ->post(route('support.reply', $conversation), ['body' => 'Hello, this is Sara from support.'])
            ->assertRedirect();

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'Sara from support'));

        $transcript = collect($conversation->refresh()->transcript ?? []);
        $this->assertTrue($transcript->contains(fn ($entry) => ($entry['role'] ?? null) === 'agent'));
    }

    public function test_support_actions_require_permission(): void
    {
        $this->account();

        $conversation = BotConversation::query()->create([
            'external_id' => 'wa-test-support',
            'contact_name' => 'No Perm Customer',
            'contact_phone' => '923000000000',
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);

        $viewer = $this->userWithPermissions(['dashboard.view', 'support.view']);

        $this->actingAs($viewer)
            ->post(route('support.resolve', $conversation))
            ->assertStatus(403);

        $this->assertSame('escalated', $conversation->refresh()->status);
    }

    public function test_support_show_page_renders_conversation(): void
    {
        $this->account();

        $conversation = BotConversation::query()->create([
            'external_id' => 'wa-test-show',
            'contact_name' => 'Show Customer',
            'contact_phone' => '923000000001',
            'status' => 'escalated',
            'escalated_at' => now(),
            'summary' => 'Wants a human agent',
        ]);

        $support = $this->userWithPermissions(['dashboard.view', 'support.view', 'support.manage']);

        $this->actingAs($support)
            ->get(route('support.show', $conversation))
            ->assertOk()
            ->assertSee('Show Customer')
            ->assertSee('Wants a human agent');
    }

    public function test_support_conversation_can_be_tagged_and_filtered(): void
    {
        $this->account();

        $conversation = BotConversation::query()->create([
            'external_id' => 'wa-tag-test',
            'contact_name' => 'Tag Customer',
            'contact_phone' => '923000000002',
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);

        $support = $this->userWithPermissions(['dashboard.view', 'support.view', 'support.manage']);

        $this->actingAs($support)
            ->post(route('support.tags', $conversation), ['tags' => 'complaint, urgent, complaint'])
            ->assertRedirect();

        $this->assertSame(['complaint', 'urgent'], $conversation->refresh()->tags);

        $this->actingAs($support)
            ->get('/support?status=all&tag=complaint')
            ->assertOk()
            ->assertSee('Tag Customer');

        $this->actingAs($support)
            ->get('/support?status=all&tag=feedback')
            ->assertOk()
            ->assertDontSee('Tag Customer');
    }

    public function test_kitchen_order_products_are_org_scoped_in_bot(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();

        Product::query()->create([
            'name' => 'Secret Item',
            'price' => 100,
            'is_active' => true,
            'show_to_bot' => true,
            'organization_id' => null,
        ]);

        $incoming = $this->incoming($account, 'Do you have Secret Item?');
        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'Secret Item'));
    }
}
