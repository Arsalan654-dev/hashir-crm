<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappContact;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWithOwner(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $organization = Organization::factory()->paid()->create();

        $owner = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::ORGANIZATION_ADMIN)->firstOrFail()->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $account = WhatsappAccount::query()->create([
            'name' => 'Broadcast Account',
            'provider' => 'whatchimp',
            'api_key' => 'broadcast-key',
            'phone_number_id' => 'BCAST-1',
            'is_default' => true,
            'organization_id' => $organization->id,
        ]);

        return [$organization, $owner, $account];
    }

    public function test_broadcast_sends_only_to_organization_contacts(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response(['status' => '1', 'message' => 'sent'], 200),
        ]);

        [$organization, $owner, $account] = $this->organizationWithOwner();

        WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'organization_id' => $organization->id,
            'wa_id' => '971500000001',
            'phone' => '971500000001',
            'name' => 'Org A Contact One',
        ]);

        WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'organization_id' => $organization->id,
            'wa_id' => '971500000002',
            'phone' => '971500000002',
            'name' => 'Org A Contact Two',
        ]);

        $otherOrganization = Organization::factory()->create();
        $otherAccount = WhatsappAccount::query()->create([
            'name' => 'Other Account',
            'provider' => 'whatchimp',
            'api_key' => 'other-key',
            'phone_number_id' => 'BCAST-2',
            'is_default' => false,
            'organization_id' => $otherOrganization->id,
        ]);

        WhatsappContact::query()->create([
            'whatsapp_account_id' => $otherAccount->id,
            'organization_id' => $otherOrganization->id,
            'wa_id' => '971500000099',
            'phone' => '971500000099',
            'name' => 'Org B Contact',
        ]);

        $this->actingAs($owner)->post(route('whatsapp.broadcast.send'), [
            'title' => 'Eid Offer',
            'message' => 'Special Eid deal today only!',
            'audience' => 'all',
            'limit' => 50,
        ])->assertRedirect();

        $broadcast = WhatsappBroadcast::query()->first();

        $this->assertNotNull($broadcast);
        $this->assertSame($organization->id, $broadcast->organization_id);
        $this->assertSame(2, $broadcast->recipients_count);
        $this->assertSame(2, $broadcast->sent_count);
        $this->assertSame(0, $broadcast->failed_count);
        $this->assertSame('completed', $broadcast->status);

        Http::assertSent(fn ($request): bool => $request['phone_number'] === '971500000001');
        Http::assertSent(fn ($request): bool => $request['phone_number'] === '971500000002');
        Http::assertNotSent(fn ($request): bool => $request['phone_number'] === '971500000099');
    }

    public function test_broadcast_audience_filter_for_leads_only(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response(['status' => '1', 'message' => 'sent'], 200),
        ]);

        [$organization, $owner, $account] = $this->organizationWithOwner();

        WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'organization_id' => $organization->id,
            'wa_id' => '971500000010',
            'phone' => '971500000010',
            'lead_id' => 123,
        ]);

        WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'organization_id' => $organization->id,
            'wa_id' => '971500000011',
            'phone' => '971500000011',
        ]);

        $this->actingAs($owner)->post(route('whatsapp.broadcast.send'), [
            'message' => 'Offer for leads only',
            'audience' => 'leads',
        ])->assertRedirect();

        $broadcast = WhatsappBroadcast::query()->first();

        $this->assertSame(1, $broadcast->recipients_count);
        Http::assertSent(fn ($request): bool => $request['phone_number'] === '971500000010');
        Http::assertNotSent(fn ($request): bool => $request['phone_number'] === '971500000011');
    }

    public function test_broadcast_fails_gracefully_when_whatsapp_not_configured(): void
    {
        Http::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $organization = Organization::factory()->paid()->create();
        $owner = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::ORGANIZATION_ADMIN)->firstOrFail()->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $this->actingAs($owner)->post(route('whatsapp.broadcast.send'), [
            'message' => 'Hello',
            'audience' => 'all',
        ])->assertSessionHasErrors('message');

        Http::assertNothingSent();
        $this->assertSame(0, WhatsappBroadcast::query()->count());
    }

    public function test_broadcast_page_requires_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $organization = Organization::factory()->paid()->create();
        $kitchenUser = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::KITCHEN_STAFF)->firstOrFail()->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $this->actingAs($kitchenUser)->get('/whatsapp/broadcast')->assertStatus(403);

        $owner = User::factory()->create([
            'role_id' => Role::query()->where('slug', Role::ORGANIZATION_ADMIN)->firstOrFail()->id,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $this->actingAs($owner)->get('/whatsapp/broadcast')->assertOk()->assertSee('New Broadcast');
    }
}
