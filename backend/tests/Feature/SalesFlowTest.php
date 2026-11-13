<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class SalesFlowTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private function salesperson(): User
    {
        return $this->userWithPermissions([
            'dashboard.view', 'sales.view', 'sales.create', 'sales.update',
        ], ['commission_rate' => 10]);
    }

    private function admin(): User
    {
        return $this->userWithPermissions([
            'dashboard.view', 'sales.view', 'sales.view_all', 'sales.confirm', 'sales.mark_done', 'sales.payments',
        ]);
    }

    public function test_salesperson_can_create_a_sale_entry_with_multiple_items(): void
    {
        $salesperson = $this->salesperson();
        $product = Product::factory()->create(['price' => 100, 'cost' => 40]);

        $response = $this->actingAs($salesperson)->post('/sales', [
            'customer_name' => 'Acme Holdings',
            'customer_phone' => '+35799000111',
            'sale_date' => now()->toDateString(),
            'source' => 'manual',
            'discount' => 10,
            'tax' => 0,
            'items' => [
                ['product_id' => $product->id, 'name' => $product->name, 'quantity' => 2, 'unit_price' => 100, 'cost' => 40],
                ['name' => 'Setup fee', 'quantity' => 1, 'unit_price' => 50, 'cost' => 0],
            ],
        ]);

        $sale = Sale::query()->firstOrFail();

        $response->assertRedirect(route('sales.my'));
        $this->assertSame($salesperson->id, $sale->salesperson_id);
        $this->assertSame('pending', $sale->status);
        $this->assertEquals(240.0, (float) $sale->total);
        $this->assertEquals(80.0, (float) $sale->cost);
        $this->assertSame(2, $sale->items()->count());
    }

    public function test_admin_can_confirm_a_sale_and_commission_is_calculated(): void
    {
        $salesperson = $this->salesperson();
        $admin = $this->admin();

        $sale = $this->createSale($salesperson, 500);

        $this->actingAs($admin)
            ->post('/sales/'.$sale->id.'/confirm')
            ->assertRedirect();

        $sale->refresh();

        $this->assertSame('confirmed', $sale->status);
        $this->assertSame($admin->id, $sale->confirmed_by);
        $this->assertNotNull($sale->confirmed_at);
        $this->assertEquals(50.0, (float) $sale->commission_amount);
    }

    public function test_admin_marking_a_sale_done_finalises_it_for_the_salesperson(): void
    {
        $salesperson = $this->salesperson();
        $admin = $this->admin();

        $sale = $this->createSale($salesperson, 1000);

        $this->actingAs($admin)->post('/sales/'.$sale->id.'/done')->assertRedirect();

        $sale->refresh();

        $this->assertSame('done', $sale->status);
        $this->assertSame($admin->id, $sale->done_by);
        $this->assertNotNull($sale->done_at);
        $this->assertSame($salesperson->id, $sale->salesperson_id);
    }

    public function test_salesperson_cannot_open_another_salespersons_sale(): void
    {
        $owner = $this->salesperson();
        $other = $this->salesperson();

        $sale = $this->createSale($owner, 250);

        $this->actingAs($other)->get('/sales/'.$sale->id)->assertStatus(403);
    }

    public function test_payments_update_the_payment_status(): void
    {
        $admin = $this->admin();
        $sale = $this->createSale($this->salesperson(), 400);

        $this->actingAs($admin)->post('/sales/'.$sale->id.'/payments', [
            'amount' => 150,
            'method' => 'bank_transfer',
            'paid_on' => now()->toDateString(),
        ])->assertRedirect();

        $sale->refresh();
        $this->assertSame('partial', $sale->payment_status);
        $this->assertEquals(150.0, (float) $sale->paid_amount);

        $this->actingAs($admin)->post('/sales/'.$sale->id.'/payments', [
            'amount' => 250,
            'method' => 'cash',
            'paid_on' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame('paid', $sale->fresh()->payment_status);
    }

    public function test_confirming_a_linked_lead_marks_it_as_won(): void
    {
        $salesperson = $this->salesperson();
        $admin = $this->admin();

        $lead = Lead::query()->create([
            'lead_no' => 'LD-TEST-0001',
            'name' => 'Bot Lead',
            'phone' => '+35799000222',
            'source' => 'whatsapp_bot',
            'status' => 'qualified',
            'assigned_to' => $salesperson->id,
        ]);

        $sale = Sale::query()->create([
            'sale_no' => 'SL-TEST-0001',
            'salesperson_id' => $salesperson->id,
            'lead_id' => $lead->id,
            'customer_name' => 'Bot Lead',
            'total' => 300,
            'sale_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post('/sales/'.$sale->id.'/confirm')->assertRedirect();

        $lead->refresh();

        $this->assertSame('won', $lead->status);
        $this->assertNotNull($lead->converted_at);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'status_change']);
    }

    public function test_lead_can_be_converted_into_a_customer(): void
    {
        $salesperson = $this->userWithPermissions(['dashboard.view', 'leads.view', 'leads.convert']);

        $lead = Lead::query()->create([
            'lead_no' => 'LD-TEST-0002',
            'name' => 'Prospect Ltd',
            'phone' => '+35799000333',
            'email' => 'prospect@example.com',
            'source' => 'manual',
            'status' => 'qualified',
            'assigned_to' => $salesperson->id,
        ]);

        $this->actingAs($salesperson)
            ->post('/leads/'.$lead->id.'/convert')
            ->assertRedirect(route('leads.show', $lead));

        $lead->refresh();

        $this->assertSame('won', $lead->status);
        $this->assertNotNull($lead->customer_id);
        $this->assertDatabaseHas('customers', ['id' => $lead->customer_id, 'name' => 'Prospect Ltd']);
    }

    private function createSale(User $salesperson, float $total): Sale
    {
        $sale = Sale::query()->create([
            'sale_no' => 'SL-TEST-'.random_int(1000, 9999),
            'salesperson_id' => $salesperson->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '+35799000444',
            'subtotal' => $total,
            'total' => $total,
            'cost' => $total / 2,
            'sale_date' => now()->toDateString(),
            'status' => 'pending',
            'source' => 'manual',
        ]);

        $sale->items()->create([
            'name' => 'Test service',
            'quantity' => 1,
            'unit_price' => $total,
            'cost' => $total / 2,
            'total' => $total,
        ]);

        return $sale;
    }
}
