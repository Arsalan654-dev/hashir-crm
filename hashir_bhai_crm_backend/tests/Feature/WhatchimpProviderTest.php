<?php

namespace Tests\Feature;

use App\Models\WhatsappAccount;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatchimpProviderTest extends TestCase
{
    use RefreshDatabase;

    private function whatchimpAccount(array $attributes = []): WhatsappAccount
    {
        return WhatsappAccount::query()->create($attributes + [
            'name' => 'WhatChimp Account',
            'provider' => 'whatchimp',
            'api_key' => 'test-api-token',
            'phone_number_id' => '123456789',
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    public function test_whatchimp_send_text_posts_form_data_and_records_message(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response([
                'status' => '1',
                'message' => 'Message sent successfully.',
                'wa_message_id' => 'wamid.TEST123',
            ], 200),
        ]);

        $account = $this->whatchimpAccount();

        $result = app(WhatsappService::class)->for($account)->sendText('923001234567', 'Hello from tests');

        $this->assertTrue($result['ok']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/whatsapp/send')
                && $request['apiToken'] === 'test-api-token'
                && $request['phone_number_id'] === '123456789'
                && $request['phone_number'] === '923001234567'
                && $request['message'] === 'Hello from tests';
        });

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'direction' => 'out',
            'body' => 'Hello from tests',
            'status' => 'sent',
        ]);
    }

    public function test_whatchimp_failure_response_is_recorded_as_failed(): void
    {
        Http::fake([
            'app.whatchimp.com/*' => Http::response([
                'status' => '0',
                'message' => 'Subscriber limit has been exceeded.',
            ], 200),
        ]);

        $account = $this->whatchimpAccount();

        $result = app(WhatsappService::class)->for($account)->sendText('923001234567', 'Will fail');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Subscriber limit', (string) $result['error']);

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'status' => 'failed',
        ]);
    }

    public function test_whatchimp_test_connection_reports_success_and_failure(): void
    {
        $fail = false;

        Http::fake([
            'app.whatchimp.com/*' => function () use (&$fail) {
                return $fail
                    ? Http::response(['status' => '0', 'message' => 'Invalid API token.'], 200)
                    : Http::response(['status' => '1', 'message' => []], 200);
            },
        ]);

        $account = $this->whatchimpAccount();

        $result = app(WhatsappService::class)->for($account)->testConnection();
        $this->assertTrue($result['ok']);

        $fail = true;

        $result = app(WhatsappService::class)->for($account)->testConnection();
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Invalid API token', (string) $result['error']);
    }

    public function test_meta_cloud_provider_is_used_for_meta_accounts(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.META1']]], 200),
        ]);

        $account = WhatsappAccount::query()->create([
            'name' => 'Meta Account',
            'provider' => 'meta_cloud',
            'phone_number_id' => 'meta-phone-id',
            'access_token' => 'meta-access-token',
            'is_default' => true,
        ]);

        $result = app(WhatsappService::class)->for($account)->sendText('923001234567', 'Meta hello');

        $this->assertTrue($result['ok']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'graph.facebook.com')
            && $request['to'] === '923001234567'
            && $request['text']['body'] === 'Meta hello');

        $this->assertDatabaseHas('whatsapp_messages', [
            'whatsapp_account_id' => $account->id,
            'wa_message_id' => 'wamid.META1',
            'direction' => 'out',
        ]);
    }

    public function test_account_api_key_is_encrypted_at_rest(): void
    {
        $account = $this->whatchimpAccount(['api_key' => 'super-secret-key']);

        $raw = DB::table('whatsapp_accounts')->where('id', $account->id)->value('api_key');

        $this->assertNotSame('super-secret-key', $raw);
        $this->assertSame('super-secret-key', $account->fresh()->api_key);
    }
}
