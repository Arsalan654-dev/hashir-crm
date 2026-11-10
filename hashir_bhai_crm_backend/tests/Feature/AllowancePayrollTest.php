<?php

namespace Tests\Feature;

use App\Models\Payroll;
use App\Models\UserAllowance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class AllowancePayrollTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_monthly_allowances_and_deductions_feed_into_payroll(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->userWithPermissions(
            ['dashboard.view'],
            ['basic_salary' => 1000, 'salary_currency' => 'USD'],
        );

        UserAllowance::query()->create([
            'user_id' => $employee->id,
            'label' => 'Fuel Allowance',
            'type' => 'allowance',
            'amount' => 200,
            'recurrence' => 'monthly',
            'is_active' => true,
        ]);

        UserAllowance::query()->create([
            'user_id' => $employee->id,
            'label' => 'Loan Deduction',
            'type' => 'deduction',
            'amount' => 50,
            'recurrence' => 'monthly',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post('/payroll/generate', [
            'year' => 2026,
            'month' => 9,
        ])->assertRedirect();

        $payroll = Payroll::query()
            ->where('user_id', $employee->id)
            ->where('period_year', 2026)
            ->where('period_month', 9)
            ->first();

        $this->assertNotNull($payroll);
        $this->assertSame('200.00', $payroll->allowances);
        $this->assertSame('50.00', $payroll->deductions);
        $this->assertSame('1200.00', $payroll->gross_salary);
        $this->assertSame('1150.00', $payroll->net_salary);

        $this->assertDatabaseHas('payroll_items', [
            'payroll_id' => $payroll->id,
            'label' => 'Fuel Allowance',
            'type' => 'earning',
        ]);

        $this->assertDatabaseHas('payroll_items', [
            'payroll_id' => $payroll->id,
            'label' => 'Loan Deduction',
            'type' => 'deduction',
        ]);
    }

    public function test_inactive_allowances_are_ignored_in_payroll(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->userWithPermissions(['dashboard.view'], ['basic_salary' => 500]);

        UserAllowance::query()->create([
            'user_id' => $employee->id,
            'label' => 'Old Fuel Allowance',
            'type' => 'allowance',
            'amount' => 300,
            'recurrence' => 'monthly',
            'is_active' => false,
        ]);

        $this->actingAs($admin)->post('/payroll/generate', ['year' => 2026, 'month' => 8])->assertRedirect();

        $payroll = Payroll::query()
            ->where('user_id', $employee->id)
            ->where('period_year', 2026)
            ->where('period_month', 8)
            ->first();

        $this->assertNotNull($payroll);
        $this->assertSame('0.00', $payroll->allowances);
        $this->assertSame('500.00', $payroll->gross_salary);
    }

    public function test_allowance_page_requires_permission(): void
    {
        $outsider = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($outsider)->get('/payroll/allowances')->assertStatus(403);

        $hr = $this->userWithPermissions(['dashboard.view', 'allowances.view']);

        $this->actingAs($hr)->get('/payroll/allowances')->assertOk()->assertSee('Salary Allowances');
    }

    public function test_allowance_can_be_created_and_updated(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($admin)->post('/payroll/allowances', [
            'user_id' => $employee->id,
            'label' => 'Fuel',
            'type' => 'allowance',
            'amount' => 150,
            'recurrence' => 'monthly',
            'is_active' => 1,
        ])->assertRedirect();

        $allowance = UserAllowance::query()->where('user_id', $employee->id)->first();
        $this->assertNotNull($allowance);
        $this->assertSame('150.00', $allowance->amount);

        $this->actingAs($admin)->put(route('allowances.update', $allowance), [
            'user_id' => $employee->id,
            'label' => 'Fuel (updated)',
            'type' => 'allowance',
            'amount' => 175,
            'recurrence' => 'monthly',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('175.00', $allowance->refresh()->amount);
        $this->assertSame('Fuel (updated)', $allowance->label);
    }
}
