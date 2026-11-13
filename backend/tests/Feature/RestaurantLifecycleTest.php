<?php

namespace Tests\Feature;

use App\Mail\OrganizationCredentialsMail;
use App\Models\BotConversation;
use App\Models\KitchenOrder;
use App\Models\KnowledgeArticle;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsappAccount;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RestaurantLifecycleTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_full_restaurant_organization_lifecycle(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake('public');

        $this->seed(RolesAndPermissionsSeeder::class);

        Http::fake([
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
                'wa_message_id' => 'wamid.REST1',
            ], 200),
        ]);

        // ---------------------------------------------------------------
        // 1. Super admin creates the restaurant organization for a client
        // ---------------------------------------------------------------
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)->post('/organizations', [
            'name' => 'Spice Villa Restaurant',
            'business_type' => 'restaurant',
            'email' => 'info@spicevilla.test',
            'phone' => '+971500000000',
            'currency' => 'AED',
            'billing_cycle' => 'monthly',
            'plan_name' => 'Restaurant Starter',
            'plan_amount' => 299,
            'owner_name' => 'Owner Ahmed',
            'owner_email' => 'owner@spicevilla.test',
            'owner_password' => 'OwnerPass123',
        ])->assertRedirect();

        $organization = Organization::query()->where('name', 'Spice Villa Restaurant')->first();

        $this->assertNotNull($organization);
        $this->assertSame('restaurant', $organization->business_type);

        $owner = $organization->owner;

        $this->assertNotNull($owner);
        $this->assertSame($organization->id, $owner->organization_id);

        Mail::assertSent(
            OrganizationCredentialsMail::class,
            fn (OrganizationCredentialsMail $mail): bool => $mail->hasTo('owner@spicevilla.test'),
        );

        // ---------------------------------------------------------------
        // 2. Owner logs in (fresh session) — Not Paid badge + premium locked
        // ---------------------------------------------------------------
        $this->app['auth']->forgetGuards();

        $this->post('/auth-login', [
            'username' => $owner->username,
            'password' => 'OwnerPass123',
        ])->assertRedirect();

        auth()->guard('web')->forgetUser();

        $this->get('/dashboard')->assertOk()->assertSee('Not Paid');
        $this->get('/billing')->assertOk()->assertSee('Submit a Payment');
        $this->get('/whatsapp/settings')->assertRedirect(route('billing.index'));
        $this->get('/products/create')->assertRedirect(route('billing.index'));
        $this->get('/bot/settings')->assertRedirect(route('billing.index'));

        // ---------------------------------------------------------------
        // 3. Owner submits payment proof, super admin approves
        // ---------------------------------------------------------------
        $this->post('/billing/submit', [
            'amount' => 299,
            'method' => 'bank_transfer',
            'reference' => 'TXN-001',
        ])->assertRedirect();

        $payment = $organization->payments()->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);

        $this->actingAs($superAdmin)
            ->post(route('organizations.payments.approve', [$organization, $payment]))
            ->assertRedirect();

        $this->assertTrue($organization->refresh()->isPaid());

        // ---------------------------------------------------------------
        // 4. Owner sets up WhatsApp (WhatChimp), bot, product and knowledge
        // ---------------------------------------------------------------
        $owner = $owner->fresh();

        $this->actingAs($owner)->post('/whatsapp/settings', [
            'name' => 'Spice Villa WhatsApp',
            'provider' => 'whatchimp',
            'phone_number_id' => 'SPICE-111',
            'phone_number' => '+971501112222',
            'api_key' => 'spice-api-key',
            'bot_enabled' => '1',
            'is_default' => '1',
        ])->assertRedirect();

        $account = WhatsappAccount::query()->where('organization_id', $organization->id)->first();
        $this->assertNotNull($account);
        $this->assertTrue($account->bot_enabled);
        $this->assertSame('whatchimp', $account->provider);

        $this->actingAs($owner)->post('/products', [
            'name' => 'Chicken Biryani',
            'type' => 'product',
            'price' => 45,
            'bot_description' => 'Fragrant basmati rice with spicy chicken.',
            'is_active' => '1',
            'show_to_bot' => '1',
        ])->assertRedirect();

        $product = Product::query()
            ->where('organization_id', $organization->id)
            ->where('name', 'Chicken Biryani')
            ->first();

        $this->assertNotNull($product);

        $this->actingAs($owner)->post('/knowledge', [
            'title' => 'Delivery Times',
            'category' => 'faq',
            'type' => 'faq',
            'content' => 'We deliver within 40 minutes across Dubai.',
            'priority' => 5,
            'is_active' => '1',
            'share_with_bot' => '1',
        ])->assertRedirect();

        $this->assertSame(1, KnowledgeArticle::query()->where('organization_id', $organization->id)->count());

        // ---------------------------------------------------------------
        // 5. Customer asks about a product — bot replies with image + text
        // ---------------------------------------------------------------
        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => 'SPICE-111',
            'phone_number' => '971509998877',
            'name' => 'Customer Khalid',
            'message' => 'Do you have Chicken Biryani?',
            'message_type' => 'text',
        ])->assertOk();

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'Chicken Biryani')
            && (string) ($request['media_type'] ?? '') === 'image');

        // ---------------------------------------------------------------
        // 6. Order flow — order captured, kitchen order created, COD chosen
        // ---------------------------------------------------------------
        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => 'SPICE-111',
            'phone_number' => '971509998877',
            'message' => 'I want to order 2 Chicken Biryani',
            'message_type' => 'text',
        ])->assertOk();

        $kitchenOrder = KitchenOrder::query()->where('organization_id', $organization->id)->first();

        $this->assertNotNull($kitchenOrder);
        $this->assertSame('pending', $kitchenOrder->status);
        $this->assertSame('90.00', $kitchenOrder->total);
        $this->assertSame('AED', $kitchenOrder->currency);

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'CASH'));

        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => 'SPICE-111',
            'phone_number' => '971509998877',
            'message' => 'cash',
            'message_type' => 'text',
        ])->assertOk();

        $this->assertSame('cod', $kitchenOrder->refresh()->payment_method);

        // ---------------------------------------------------------------
        // 7. Kitchen staff fulfils the order — customer notified at each step
        // ---------------------------------------------------------------
        $kitchenUser = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::KITCHEN_STAFF)->firstOrFail()->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $this->actingAs($kitchenUser)
            ->get('/kitchen')
            ->assertOk()
            ->assertSee($kitchenOrder->order_no);

        foreach (['confirmed', 'preparing', 'ready', 'dispatched', 'delivered'] as $status) {
            $this->actingAs($kitchenUser)
                ->post(route('kitchen.status', $kitchenOrder), ['status' => $status])
                ->assertRedirect();
        }

        $kitchenOrder->refresh();

        $this->assertSame('delivered', $kitchenOrder->status);
        $this->assertNotNull($kitchenOrder->delivered_at);
        Storage::disk('public')->assertExists('invoices/'.$kitchenOrder->order_no.'.pdf');

        Http::assertSent(fn ($request): bool => (string) ($request->data()['media_type'] ?? '') === 'document'
            && str_contains((string) ($request->data()['media_url'] ?? ''), 'invoices/'.$kitchenOrder->order_no.'.pdf'));

        // ---------------------------------------------------------------
        // 8. Support escalation — human handover with full context
        // ---------------------------------------------------------------
        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => 'SPICE-111',
            'phone_number' => '971509998877',
            'message' => 'I need to talk to a human agent',
            'message_type' => 'text',
        ])->assertOk();

        $supportUser = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::SUPPORT_AGENT)->firstOrFail()->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $conversation = BotConversation::query()->where('organization_id', $organization->id)->first();
        $this->assertNotNull($conversation);
        $this->assertSame('escalated', $conversation->status);

        $this->actingAs($supportUser)
            ->get('/support')
            ->assertOk()
            ->assertSee('Customer Khalid');

        $this->actingAs($supportUser)
            ->post(route('support.reply', $conversation), ['body' => 'Hello Khalid, this is support.'])
            ->assertRedirect();

        Http::assertSent(fn ($request): bool => str_contains((string) $request['message'], 'this is support'));

        $this->actingAs($supportUser)
            ->post(route('support.resolve', $conversation))
            ->assertRedirect();

        $this->assertSame('resolved', $conversation->refresh()->status);

        // ---------------------------------------------------------------
        // 9. Isolation — a second restaurant sees nothing of the first
        // ---------------------------------------------------------------
        $otherOrganization = Organization::factory()->paid()->create(['business_type' => 'restaurant']);

        $otherOwner = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::ORGANIZATION_ADMIN)->firstOrFail()->id,
            'organization_id' => $otherOrganization->id,
            'is_active' => true,
        ]);

        $this->actingAs($otherOwner)->get('/kitchen')->assertOk()->assertDontSee($kitchenOrder->order_no);
        $this->actingAs($otherOwner)->get('/support')->assertOk()->assertDontSee('Customer Khalid');
        $this->actingAs($otherOwner)->get('/products')->assertOk()->assertDontSee('Chicken Biryani');

        $this->assertFalse(Product::query()->where('name', 'Chicken Biryani')->exists());

        // ---------------------------------------------------------------
        // 10. Super admin blocks CRM + bot for the restaurant
        // ---------------------------------------------------------------
        $this->actingAs($superAdmin)->post(route('organizations.toggle-crm', $organization))->assertRedirect();

        $this->actingAs($owner->fresh())->get('/dashboard')->assertRedirect(route('billing.index'));

        $this->actingAs($superAdmin)->post(route('organizations.toggle-bot', $organization))->assertRedirect();

        $requestsBefore = Http::recorded()->count();

        $this->postJson('/webhooks/whatsapp/whatchimp', [
            'phone_number_id' => 'SPICE-111',
            'phone_number' => '971509998877',
            'message' => 'are you there?',
            'message_type' => 'text',
        ])->assertOk();

        $this->assertSame($requestsBefore, Http::recorded()->count(), 'Blocked bot must not reply.');

        // Unblock for cleanliness
        $this->actingAs($superAdmin)->post(route('organizations.toggle-crm', $organization))->assertRedirect();
        $this->assertFalse($organization->refresh()->crm_blocked);
    }
}
