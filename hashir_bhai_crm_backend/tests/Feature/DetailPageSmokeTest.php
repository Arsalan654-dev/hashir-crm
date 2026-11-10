<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BotConversation;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\KitchenOrder;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\Organization;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Target;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class DetailPageSmokeTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_key_detail_pages_render_without_errors(): void
    {
        $admin = $this->superAdmin();

        $account = WhatsappAccount::query()->create([
            'name' => 'Detail Account',
            'provider' => 'whatchimp',
            'api_key' => 'token',
            'phone_number_id' => '111',
            'is_default' => true,
        ]);

        $contact = WhatsappContact::query()->create([
            'whatsapp_account_id' => $account->id,
            'wa_id' => '923001234567',
            'phone' => '923001234567',
            'name' => 'Detail Contact',
        ]);

        WhatsappMessage::query()->create([
            'whatsapp_account_id' => $account->id,
            'whatsapp_contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'text',
            'body' => 'Hello detail page',
            'status' => 'received',
            'sent_at' => now(),
        ]);

        $lead = Lead::query()->create([
            'lead_no' => 'LD-DETAIL-1',
            'name' => 'Detail Lead',
            'status' => 'new',
        ]);

        $sale = Sale::query()->create([
            'sale_no' => 'SL-DETAIL-1',
            'salesperson_id' => $admin->id,
            'customer_name' => 'Detail Sale Customer',
            'total' => 100,
            'sale_date' => today()->toDateString(),
        ]);

        $product = Product::query()->create([
            'name' => 'Detail Product',
            'price' => 100,
            'is_active' => true,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Detail Customer',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-DETAIL-1',
            'customer_id' => $customer->id,
            'billing_name' => 'Detail Customer',
            'invoice_date' => today()->toDateString(),
            'amount' => 100,
        ]);

        $organization = Organization::factory()->paid()->create();

        $kitchenOrder = KitchenOrder::query()->create([
            'order_no' => 'ORD-DETAIL-1',
            'customer_name' => 'Detail Order Customer',
            'customer_phone' => '923001234567',
            'total' => 200,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $conversation = BotConversation::query()->create([
            'external_id' => 'wa-detail',
            'contact_name' => 'Detail Conversation',
            'contact_phone' => '923001234567',
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);

        $payroll = Payroll::query()->create([
            'user_id' => $admin->id,
            'period_year' => now()->year,
            'period_month' => now()->month,
            'period_label' => now()->format('F Y'),
            'basic_salary' => 1000,
            'gross_salary' => 1000,
            'net_salary' => 1000,
            'status' => 'draft',
        ]);

        $target = Target::query()->create([
            'user_id' => $admin->id,
            'period_type' => 'monthly',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'target_amount' => 5000,
        ]);

        $leave = LeaveRequest::query()->create([
            'user_id' => $admin->id,
            'type' => 'casual',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'days' => 1,
            'status' => 'pending',
        ]);

        AttendanceRecord::query()->create([
            'user_id' => $admin->id,
            'date' => today()->toDateString(),
            'check_in' => '09:00:00',
            'status' => 'present',
            'marked_by' => $admin->id,
        ]);

        $pages = [
            '/leads/'.$lead->id,
            '/sales/'.$sale->id,
            '/products/'.$product->id,
            '/customers/'.$customer->id,
            '/invoices/'.$invoice->id,
            '/kitchen/'.$kitchenOrder->id,
            '/support/'.$conversation->id,
            '/organizations/'.$organization->id,
            '/users/'.$admin->id.'/edit',
            '/employees/'.$admin->id,
            '/payroll/'.$payroll->id,
            '/payroll/'.$payroll->id.'/payslip',
            '/whatsapp/'.$contact->id,
            '/bot/conversations/'.$conversation->id,
            '/invoices/'.$invoice->id.'/pdf',
            '/sales-teams',
            '/targets',
            '/targets/my',
            '/leaves',
            '/leaves/my',
            '/attendance',
            '/attendance/my',
            '/attendance/report',
            '/payroll/allowances',
            '/support',
            '/kitchen',
            '/kitchen?status=today',
            '/knowledge',
            '/knowledge?search=test',
            '/roles',
            '/permissions',
            '/activity-logs',
            '/settings',
            '/settings/email',
            '/reports',
            '/analytics',
            '/search?q=test',
            '/notifications',
        ];

        $failures = [];

        foreach ($pages as $uri) {
            $status = $this->actingAs($admin)->get($uri)->getStatusCode();

            if ($status >= 500) {
                $failures[$uri] = $status;
            }
        }

        $this->assertSame([], $failures, 'Server errors: '.json_encode($failures));
        $this->assertTrue($leave->exists() && $target->exists());
    }
}
