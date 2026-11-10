<?php

namespace Tests\Feature;

use App\Models\BotConversation;
use App\Models\KitchenOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Notifications\AppNotification;
use App\Services\Bot\BotEngine;
use App\Services\KitchenOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class KitchenOrderTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private function account(bool $botEnabled = true): WhatsappAccount
    {
        return WhatsappAccount::query()->create([
            'name' => 'Kitchen Account',
            'provider' => 'whatchimp',
            'api_key' => 'test-api-token',
            'phone_number_id' => '555000111',
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
                'wa_message_id' => 'wamid.KITCHEN1',
            ], 200),
        ]);
    }

    /**
     * @return array{contact: WhatsappContact, message: WhatsappMessage}
     */
    private function incoming(WhatsappAccount $account, string $body): array
    {
        $contact = WhatsappContact::query()->firstOrCreate(
            ['whatsapp_account_id' => $account->id, 'wa_id' => '923001234567'],
            [
                'phone' => '923001234567',
                'name' => 'Burger Lover',
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

    private function seedProduct(): void
    {
        Product::query()->create([
            'name' => 'Zinger Burger',
            'price' => 500,
            'description' => 'Spicy zinger burger.',
            'is_active' => true,
            'show_to_bot' => true,
        ]);
    }

    public function test_bot_order_creates_kitchen_order_and_asks_for_payment_method(): void
    {
        Notification::fake();
        $this->fakeWhatsapp();

        $account = $this->account();
        $this->seedProduct();

        $incoming = $this->incoming($account, 'I want to order 2 Zinger Burger');

        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        $order = KitchenOrder::query()->first();

        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame('1000.00', $order->total);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame('2.00', $order->items()->first()->quantity);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'CASH'));

        $conversation = BotConversation::query()->first();
        $this->assertSame('payment_method', $conversation->order_data['awaiting'] ?? null);
        $this->assertSame($order->id, $conversation->order_data['kitchen_order_id'] ?? null);
    }

    public function test_customer_replying_cash_selects_cod(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();
        $this->seedProduct();

        $incoming = $this->incoming($account, 'I want to order 1 Zinger Burger');
        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        $reply = $this->incoming($account, 'cash please');
        app(BotEngine::class)->handleIncoming($account, $reply['contact'], $reply['message']);

        $order = KitchenOrder::query()->first();
        $this->assertSame('cod', $order->payment_method);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'cash on delivery'));

        $conversation = BotConversation::query()->first();
        $this->assertNull($conversation->order_data['awaiting'] ?? null);
    }

    public function test_customer_replying_online_receives_payment_link(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account();
        $this->seedProduct();

        $incoming = $this->incoming($account, 'I want to order 1 Zinger Burger');
        app(BotEngine::class)->handleIncoming($account, $incoming['contact'], $incoming['message']);

        $reply = $this->incoming($account, 'online');
        app(BotEngine::class)->handleIncoming($account, $reply['contact'], $reply['message']);

        $order = KitchenOrder::query()->first();
        $this->assertSame('online', $order->payment_method);
        $this->assertNotNull($order->payment_token);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], '/pay/'.$order->order_no));
    }

    public function test_status_update_notifies_customer_on_whatsapp(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account(botEnabled: false);
        $contact = WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'wa_id' => '923001234567',
            'phone' => '923001234567',
            'name' => 'Burger Lover',
        ]);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-1',
            'whatsapp_contact_id' => $contact->id,
            'customer_name' => 'Burger Lover',
            'customer_phone' => '923001234567',
            'total' => 500,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        app(KitchenOrderService::class)->updateStatus($order, 'confirmed');

        $this->assertSame('confirmed', $order->refresh()->status);
        $this->assertNotNull($order->confirmed_at);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/whatsapp/send')
            && str_contains((string) $request['message'], 'ORD-TEST-1'));
    }

    public function test_delivered_order_sends_invoice_pdf(): void
    {
        Storage::fake('public');
        $this->fakeWhatsapp();

        $account = $this->account(botEnabled: false);
        $contact = WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'wa_id' => '923001234567',
            'phone' => '923001234567',
            'name' => 'Burger Lover',
        ]);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-2',
            'whatsapp_contact_id' => $contact->id,
            'customer_name' => 'Burger Lover',
            'customer_phone' => '923001234567',
            'total' => 500,
            'currency' => 'USD',
            'status' => 'dispatched',
        ]);

        $order->items()->create(['name' => 'Zinger Burger', 'quantity' => 1, 'unit_price' => 500, 'total' => 500]);

        app(KitchenOrderService::class)->updateStatus($order, 'delivered');

        Storage::disk('public')->assertExists('invoices/ORD-TEST-2.pdf');

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return str_contains($request->url(), '/whatsapp/send')
                && str_contains((string) ($data['media_url'] ?? ''), 'invoices/ORD-TEST-2.pdf')
                && ($data['media_type'] ?? '') === 'document';
        });
    }

    public function test_kitchen_dashboard_lists_orders_and_feed_reports_new_ones(): void
    {
        $user = $this->userWithPermissions(['dashboard.view', 'orders.view']);
        $this->account(botEnabled: false);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-3',
            'customer_name' => 'Dashboard Customer',
            'customer_phone' => '923001234567',
            'total' => 750,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->actingAs($user)->get('/kitchen')->assertOk()->assertSee('ORD-TEST-3');

        $this->actingAs($user)->getJson('/kitchen/feed?after=0')
            ->assertOk()
            ->assertJsonPath('orders.0.order_no', 'ORD-TEST-3')
            ->assertJsonPath('pending_count', 1);
    }

    public function test_status_update_requires_manage_permission(): void
    {
        $viewer = $this->userWithPermissions(['dashboard.view', 'orders.view']);
        $this->account(botEnabled: false);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-4',
            'customer_name' => 'Viewer Customer',
            'total' => 100,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)
            ->post(route('kitchen.status', $order), ['status' => 'confirmed'])
            ->assertStatus(403);

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_payment_page_requires_valid_token(): void
    {
        $this->account(botEnabled: false);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-5',
            'customer_name' => 'Pay Customer',
            'customer_phone' => '923001234567',
            'total' => 300,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->get('/pay/ORD-TEST-5')->assertNotFound();

        $token = $order->ensurePaymentToken();

        $this->get('/pay/ORD-TEST-5?t='.$token)
            ->assertOk()
            ->assertSee('ORD-TEST-5')
            ->assertSee('I have paid');
    }

    public function test_customer_can_submit_payment_proof_and_staff_is_notified(): void
    {
        Notification::fake();

        $admin = $this->superAdmin();
        $this->account(botEnabled: false);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-6',
            'customer_name' => 'Pay Customer',
            'customer_phone' => '923001234567',
            'total' => 300,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $token = $order->ensurePaymentToken();

        $this->post('/pay/ORD-TEST-6', [
            't' => $token,
            'reference' => 'TXN-998877',
        ])->assertRedirect();

        $order->refresh();

        $this->assertSame('pending_verification', $order->payment_status);
        $this->assertSame('TXN-998877', $order->payment_reference);

        Notification::assertSentTo($admin, AppNotification::class);
    }

    public function test_cancelled_order_notifies_customer(): void
    {
        $this->fakeWhatsapp();

        $account = $this->account(botEnabled: false);
        $contact = WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'wa_id' => '923001234567',
            'phone' => '923001234567',
            'name' => 'Cancel Customer',
        ]);

        $order = KitchenOrder::query()->create([
            'order_no' => 'ORD-TEST-7',
            'whatsapp_contact_id' => $contact->id,
            'customer_name' => 'Cancel Customer',
            'customer_phone' => '923001234567',
            'total' => 200,
            'currency' => 'USD',
            'status' => 'confirmed',
        ]);

        app(KitchenOrderService::class)->cancel($order);

        $this->assertSame('cancelled', $order->refresh()->status);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'cancelled'));
    }

    public function test_kitchen_staff_role_can_see_dashboard(): void
    {
        $staff = $this->userWithPermissions(['dashboard.view', 'orders.view', 'orders.manage']);

        $this->assertInstanceOf(User::class, $staff);

        $this->actingAs($staff)->get('/kitchen')->assertOk();
    }
}
