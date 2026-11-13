<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Payroll;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_payroll_is_generated_with_salary_commission_and_absence_deduction(): void
    {
        $admin = $this->userWithPermissions([
            'dashboard.view', 'payroll.view_all', 'payroll.manage', 'payroll.approve', 'payroll.pay',
        ]);

        $employee = $this->userWithPermissions(['dashboard.view'], [
            'basic_salary' => 2600,
            'salary_currency' => 'USD',
            'joining_date' => now()->subYear(),
        ]);

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        AttendanceRecord::query()->create([
            'user_id' => $employee->id,
            'date' => $start->toDateString(),
            'status' => 'present',
            'worked_hours' => 8,
        ]);
        AttendanceRecord::query()->create([
            'user_id' => $employee->id,
            'date' => $start->copy()->addDay()->toDateString(),
            'status' => 'absent',
            'worked_hours' => 0,
        ]);

        Sale::query()->create([
            'sale_no' => 'SL-PAY-0001',
            'salesperson_id' => $employee->id,
            'customer_name' => 'Commission Client',
            'subtotal' => 2000,
            'total' => 2000,
            'cost' => 1000,
            'commission_amount' => 200,
            'sale_date' => $start->copy()->addDays(3)->toDateString(),
            'status' => 'done',
        ]);

        $this->actingAs($admin)->post('/payroll/generate', [
            'year' => now()->year,
            'month' => now()->month,
        ])->assertRedirect();

        $payroll = Payroll::query()->where('user_id', $employee->id)->firstOrFail();

        $this->assertEquals(2600.0, (float) $payroll->basic_salary);
        $this->assertEquals(200.0, (float) $payroll->commission);
        $this->assertGreaterThan(0, (float) $payroll->absent_deduction);
        $this->assertEquals(
            (float) $payroll->basic_salary + (float) $payroll->commission - (float) $payroll->absent_deduction,
            (float) $payroll->net_salary
        );
        $this->assertSame('draft', $payroll->status);
        $this->assertGreaterThan(0, $payroll->items()->count());
    }

    public function test_payroll_can_be_approved_and_marked_paid(): void
    {
        $admin = $this->userWithPermissions([
            'dashboard.view', 'payroll.view_all', 'payroll.manage', 'payroll.approve', 'payroll.pay',
        ]);

        $employee = $this->userWithPermissions(['dashboard.view'], [
            'basic_salary' => 1500,
        ]);

        $this->actingAs($admin)->post('/payroll/generate', [
            'year' => now()->year,
            'month' => now()->month,
        ])->assertRedirect();

        $payroll = Payroll::query()->where('user_id', $employee->id)->firstOrFail();

        $this->actingAs($admin)->post('/payroll/'.$payroll->id.'/approve')->assertRedirect();
        $this->assertSame('approved', $payroll->fresh()->status);

        $this->actingAs($admin)->post('/payroll/'.$payroll->id.'/pay', ['payment_method' => 'bank_transfer'])->assertRedirect();

        $payroll->refresh();
        $this->assertSame('paid', $payroll->status);
        $this->assertNotNull($payroll->paid_on);
    }

    public function test_employee_can_only_view_their_own_payslip(): void
    {
        $employee = $this->userWithPermissions(['dashboard.view', 'payroll.view_own']);

        $other = $this->userWithPermissions(['dashboard.view'], ['basic_salary' => 1000]);

        $this->actingAs($this->userWithPermissions(['dashboard.view', 'payroll.manage']))
            ->post('/payroll/generate', ['year' => now()->year, 'month' => now()->month])
            ->assertRedirect();

        $own = Payroll::query()->where('user_id', $employee->id)->firstOrFail();
        $foreign = Payroll::query()->where('user_id', $other->id)->firstOrFail();

        $this->actingAs($employee)->get('/payroll/'.$own->id)->assertOk();
        $this->actingAs($employee)->get('/payroll/'.$foreign->id)->assertStatus(403);
    }

    public function test_payslip_can_be_downloaded_as_pdf(): void
    {
        $admin = $this->userWithPermissions(['dashboard.view', 'payroll.manage', 'payroll.view_all']);

        $this->userWithPermissions(['dashboard.view'], ['basic_salary' => 900]);

        $this->actingAs($admin)->post('/payroll/generate', [
            'year' => now()->year,
            'month' => now()->month,
        ])->assertRedirect();

        $payroll = Payroll::query()->firstOrFail();

        $response = $this->actingAs($admin)->get('/payroll/'.$payroll->id.'/payslip');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
