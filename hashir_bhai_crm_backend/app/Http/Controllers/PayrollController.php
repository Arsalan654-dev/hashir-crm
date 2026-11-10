<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Compensation;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAllowance;
use App\Support\Activity;
use App\Support\Branding;
use App\Support\SimplePdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $payrolls = Payroll::query()
            ->with('user:id,name,avatar,employee_code,department_id')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->orderByDesc('net_salary')
            ->get();

        return view('pages.payroll.index', [
            'payrolls' => $payrolls,
            'year' => $year,
            'month' => $month,
            'periodLabel' => Carbon::create($year, $month, 1)->format('F Y'),
            'summary' => [
                'employees' => $payrolls->count(),
                'gross' => (float) $payrolls->sum('gross_salary'),
                'net' => (float) $payrolls->sum('net_salary'),
                'commission' => (float) $payrolls->sum('commission'),
                'bonus' => (float) $payrolls->sum('bonus') + (float) $payrolls->sum('incentive'),
                'deductions' => (float) $payrolls->sum('deductions') + (float) $payrolls->sum('absent_deduction'),
                'paid' => $payrolls->where('status', 'paid')->count(),
                'approved' => $payrolls->where('status', 'approved')->count(),
                'draft' => $payrolls->where('status', 'draft')->count(),
            ],
            'employees' => User::query()->whereNotNull('role_id')->orderBy('name')->get(['id', 'name', 'basic_salary', 'salary_currency']),
        ]);
    }

    public function my(Request $request): View
    {
        $payrolls = Payroll::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(12);

        return view('pages.payroll.mine', [
            'payrolls' => $payrolls,
            'lifetimeEarnings' => (float) Payroll::query()->where('user_id', $request->user()->id)->sum('net_salary'),
            'lifetimeCommission' => (float) Payroll::query()->where('user_id', $request->user()->id)->sum('commission'),
            'lifetimeBonus' => (float) Payroll::query()->where('user_id', $request->user()->id)->sum('bonus'),
        ]);
    }

    public function show(Payroll $payroll): View
    {
        $this->authorizeView($payroll);

        $payroll->load(['user:id,name,avatar,employee_code,department_id,designation', 'items', 'approver:id,name', 'creator:id,name']);

        return view('pages.payroll.show', [
            'payroll' => $payroll,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'user_id' => ['nullable', 'exists:users,id'],
            'force' => ['nullable', 'boolean'],
        ]);

        $employees = User::query()
            ->whereNotNull('role_id')
            ->where('is_active', true)
            ->when(filled($data['user_id'] ?? null), fn ($query) => $query->whereKey($data['user_id']))
            ->get();

        $generated = 0;

        DB::transaction(function () use ($employees, $data, $request, &$generated) {
            foreach ($employees as $employee) {
                $existing = Payroll::query()
                    ->where('user_id', $employee->id)
                    ->where('period_year', $data['year'])
                    ->where('period_month', $data['month'])
                    ->first();

                if ($existing !== null && $existing->status === 'paid' && ! $request->boolean('force')) {
                    continue;
                }

                $this->buildPayroll($employee, (int) $data['year'], (int) $data['month'], $request->user()->id, $existing);
                $generated++;
            }
        });

        Activity::log('generated', "Payroll generated for {$data['month']}/{$data['year']} ({$generated} employees)", null, $data, 'payroll');

        return back()->with('status', "Payroll generated for {$generated} employees.");
    }

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'string', 'max:30'],
        ]);

        $payroll->fill($data);
        $payroll->gross_salary = (float) $payroll->basic_salary + (float) $payroll->allowances
            + (float) $payroll->commission + (float) $payroll->bonus + (float) $payroll->incentive;
        $payroll->net_salary = (float) $payroll->gross_salary - (float) $payroll->deductions - (float) $payroll->tax - (float) $payroll->absent_deduction;
        $payroll->save();

        Activity::log('updated', 'Payroll updated for '.$payroll->user?->name, $payroll, [], 'payroll');

        return back()->with('status', 'Payroll updated.');
    }

    public function approve(Request $request, Payroll $payroll): RedirectResponse
    {
        $payroll->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        Activity::log('approved', 'Payroll approved for '.$payroll->user?->name, $payroll, [], 'payroll');

        return back()->with('status', 'Payroll approved.');
    }

    public function pay(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:30'],
        ]);

        $payroll->update([
            'status' => 'paid',
            'paid_on' => now()->toDateString(),
            'payment_method' => $data['payment_method'] ?? $payroll->payment_method ?? 'bank_transfer',
        ]);

        Activity::log('paid', 'Payroll marked as paid for '.$payroll->user?->name, $payroll, [], 'payroll');

        return back()->with('status', 'Payroll marked as paid.');
    }

    public function destroy(Payroll $payroll): RedirectResponse
    {
        if ($payroll->status === 'paid') {
            return back()->withErrors(['payroll' => 'Paid payroll cannot be deleted.']);
        }

        $payroll->delete();

        return back()->with('status', 'Payroll entry deleted.');
    }

    public function payslip(Payroll $payroll): Response
    {
        $this->authorizeView($payroll);

        $payroll->load(['user.department', 'user.designationRecord', 'items']);

        $companyName = (string) Setting::getValue('company.name', Branding::NAME);
        $currency = $payroll->currency ?? 'USD';
        $money = fn (float|string|null $value) => $currency.' '.number_format((float) $value, 2);

        $earnings = $payroll->items->where('type', 'earning')->map(fn ($item) => [
            $item->label,
            $money($item->amount),
        ])->values()->all();

        $deductions = $payroll->items->where('type', 'deduction')->map(fn ($item) => [
            $item->label,
            '- '.$money($item->amount),
        ])->values()->all();

        $pdf = app(SimplePdf::class)->render([
            ['type' => 'title', 'text' => $companyName],
            ['type' => 'subtitle', 'text' => 'Payslip — '.$payroll->period_label],
            ['type' => 'hr'],
            ['type' => 'kv', 'columns' => 2, 'rows' => [
                ['Employee', (string) $payroll->user?->name],
                ['Employee Code', (string) ($payroll->user?->employee_code ?? '-')],
                ['Department', (string) ($payroll->user?->department?->name ?? '-')],
                ['Designation', (string) ($payroll->user?->designation ?? $payroll->user?->designationRecord?->title ?? '-')],
                ['Working Days', (string) $payroll->working_days],
                ['Present / Absent / Leave', $payroll->present_days.' / '.$payroll->absent_days.' / '.$payroll->leave_days],
                ['Status', ucfirst($payroll->status)],
                ['Paid On', $payroll->paid_on?->toDateString() ?? '-'],
            ]],
            ['type' => 'spacer', 'height' => 6],
            ['type' => 'heading', 'text' => 'Earnings'],
            ['type' => 'table', 'headers' => ['Component', 'Amount'], 'widths' => [3, 1], 'rows' => $earnings ?: [['—', $money(0)]], 'align' => 'left'],
            ['type' => 'spacer', 'height' => 6],
            ['type' => 'heading', 'text' => 'Deductions'],
            ['type' => 'table', 'headers' => ['Component', 'Amount'], 'widths' => [3, 1], 'rows' => $deductions ?: [['—', $money(0)]], 'align' => 'left'],
            ['type' => 'spacer', 'height' => 10],
            ['type' => 'total', 'label' => 'Net Salary', 'value' => $money($payroll->net_salary)],
            ['type' => 'spacer', 'height' => 16],
            ['type' => 'text', 'text' => 'This is a computer generated payslip and does not require a signature.'],
        ]);

        return app(SimplePdf::class)->respond($pdf, 'payslip-'.$payroll->period_label.'-'.$payroll->id.'.pdf');
    }

    public function reports(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $year = max(2020, min(2100, $year));

        $months = Payroll::query()
            ->where('period_year', $year)
            ->selectRaw('period_month, sum(net_salary) as net, sum(commission) as commission, sum(bonus) as bonus, sum(incentive) as incentive, sum(absent_deduction) as absent_deduction, sum(tax) as tax, count(*) as employees')
            ->groupBy('period_month')
            ->orderBy('period_month')
            ->get();

        $departments = DB::table('payrolls')
            ->join('users', 'users.id', '=', 'payrolls.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->where('payrolls.period_year', $year)
            ->selectRaw("coalesce(departments.name, 'Unassigned') as department, sum(payrolls.net_salary) as net, count(*) as employees")
            ->groupBy('department')
            ->get();

        return view('pages.payroll.reports', [
            'year' => $year,
            'months' => $months,
            'departments' => $departments,
            'topEarners' => Payroll::query()->with('user:id,name,avatar')
                ->where('period_year', $year)
                ->orderByDesc('net_salary')->limit(10)->get(),
        ]);
    }

    private function buildPayroll(User $employee, int $year, int $month, int $creatorId, ?Payroll $existing): Payroll
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $periodLabel = $start->format('F Y');

        $workingDays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (! $date->isWeekend()) {
                $workingDays++;
            }
        }

        $holidayCount = Holiday::query()->whereBetween('date', [$start, $end])->count();
        $workingDays = max(0, $workingDays - $holidayCount);

        $attendance = AttendanceRecord::query()
            ->where('user_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays = $attendance->whereIn('status', ['present', 'late', 'remote', 'half-day'])->count();
        $absentDays = $attendance->where('status', 'absent')->count();
        $leaveDays = $attendance->whereIn('status', ['leave', 'holiday'])->count();

        $basic = (float) $employee->basic_salary;
        $perDay = $workingDays > 0 ? $basic / $workingDays : 0;
        $absentDeduction = round($perDay * $absentDays, 2);

        $commission = (float) Sale::query()
            ->where('salesperson_id', $employee->id)
            ->whereIn('status', ['confirmed', 'done'])
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->sum('commission_amount');

        $compensations = Compensation::query()
            ->where('user_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere('period_month', $start->format('Y-m'));
            })
            ->get();

        $bonus = (float) $compensations->whereIn('type', ['bonus', 'appreciation'])->sum('amount');
        $incentive = (float) $compensations->where('type', 'incentive')->sum('amount');
        $otherDeductions = (float) $compensations->whereIn('type', ['penalty', 'deduction', 'advance'])->sum('amount');

        $monthlyAllowances = UserAllowance::query()
            ->where('user_id', $employee->id)
            ->where('is_active', true)
            ->where('recurrence', 'monthly')
            ->where('type', 'allowance')
            ->get();

        $monthlyDeductions = UserAllowance::query()
            ->where('user_id', $employee->id)
            ->where('is_active', true)
            ->where('recurrence', 'monthly')
            ->where('type', 'deduction')
            ->get();

        $allowanceTotal = (float) $monthlyAllowances->sum('amount');
        $allowanceDeductionTotal = (float) $monthlyDeductions->sum('amount');

        $gross = $basic + $allowanceTotal + $commission + $bonus + $incentive;
        $net = $gross - $absentDeduction - $otherDeductions - $allowanceDeductionTotal;

        $payroll = $existing ?? new Payroll;
        $payroll->fill([
            'user_id' => $employee->id,
            'period_year' => $year,
            'period_month' => $month,
            'period_label' => $periodLabel,
            'currency' => $employee->salary_currency ?? 'USD',
            'basic_salary' => $basic,
            'allowances' => $allowanceTotal,
            'commission' => $commission,
            'bonus' => $bonus,
            'incentive' => $incentive,
            'deductions' => $otherDeductions + $allowanceDeductionTotal,
            'tax' => 0,
            'absent_deduction' => $absentDeduction,
            'gross_salary' => $gross,
            'net_salary' => $net,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'working_days' => $workingDays,
            'status' => $existing?->status === 'approved' ? 'approved' : 'draft',
            'notes' => $existing?->notes,
            'created_by' => $existing?->created_by ?? $creatorId,
            'approved_at' => $existing?->approved_at,
            'approved_by' => $existing?->approved_by,
        ]);
        $payroll->save();

        $payroll->items()->delete();

        $items = [
            ['earning', 'Basic Salary', $basic, 'basic'],
            ['earning', 'Sales Commission', $commission, 'commission'],
            ['earning', 'Bonus', $bonus, 'bonus'],
            ['earning', 'Incentive', $incentive, 'incentive'],
            ['deduction', 'Absence Deduction', $absentDeduction, 'absence'],
            ['deduction', 'Other Deductions', $otherDeductions, 'other'],
        ];

        foreach ($monthlyAllowances as $allowance) {
            $items[] = ['earning', $allowance->label, (float) $allowance->amount, 'allowance'];
        }

        foreach ($monthlyDeductions as $allowance) {
            $items[] = ['deduction', $allowance->label, (float) $allowance->amount, 'allowance'];
        }

        foreach ($items as [$type, $label, $amount, $source]) {
            if ($amount <= 0) {
                continue;
            }

            PayrollItem::query()->create([
                'payroll_id' => $payroll->id,
                'type' => $type,
                'label' => $label,
                'amount' => $amount,
                'source' => $source,
            ]);
        }

        $compensations->each(fn (Compensation $compensation) => $compensation->update(['payroll_id' => $payroll->id]));

        return $payroll;
    }

    private function authorizeView(Payroll $payroll): void
    {
        $user = auth()->user();

        if ($user === null) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->can('payroll.view_all') || $payroll->user_id === $user->id) {
            return;
        }

        abort(403, '403 — You cannot access this.');
    }
}
