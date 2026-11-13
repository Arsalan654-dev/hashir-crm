@extends('layouts.app')

@section('title', 'Payslip — '.$payroll->period_label)
@section('page-title', 'Payslip — '.$payroll->period_label)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/payroll') }}">Payroll</a></li>
    <li class="breadcrumb-item active">{{ $payroll->period_label }}</li>
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $statusColor = ['draft' => 'warning', 'approved' => 'info', 'paid' => 'success'][$payroll->status] ?? 'secondary';
    $earnings = [
        'Basic Salary' => $payroll->basic_salary,
        'Allowances' => $payroll->allowances,
        'Sales Commission' => $payroll->commission,
        'Bonus' => $payroll->bonus,
        'Incentive' => $payroll->incentive,
    ];
    $deductions = [
        'Deductions' => $payroll->deductions,
        'Tax' => $payroll->tax,
        'Absence Deduction' => $payroll->absent_deduction,
    ];
@endphp

@section('page-actions')
    <a href="{{ url('/payroll/'.$payroll->id.'/payslip') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-download me-1"></i> Download Payslip</a>
    @if ($payroll->status === 'draft' && auth()->user()->can('payroll.approve'))
        <form action="{{ url('/payroll/'.$payroll->id.'/approve') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-success btn-sm"><i class="bx bx-check-circle me-1"></i> Approve</button>
        </form>
    @endif
    @if ($payroll->status !== 'paid' && auth()->user()->hasAnyPermission(['payroll.pay', 'payroll.manage']))
        <form action="{{ url('/payroll/'.$payroll->id.'/pay') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-primary btn-sm"><i class="bx bx-money me-1"></i> Mark as Paid</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <img src="{{ $payroll->user?->avatarUrl() }}" class="rounded-circle avatar-lg" alt="">
                <div class="ms-3 flex-1">
                    <h4 class="mb-1">{{ $payroll->user?->name ?? '—' }}</h4>
                    <p class="text-muted mb-0">
                        <i class="bx bx-id-card me-1"></i>{{ $payroll->user?->employee_code ?? '—' }}
                        <span class="mx-2">·</span>
                        <i class="bx bx-briefcase me-1"></i>{{ $payroll->user?->designation ?? $payroll->user?->designationRecord?->title ?? '—' }}
                        <span class="mx-2">·</span>
                        <i class="bx bx-buildings me-1"></i>{{ $payroll->user?->department?->name ?? 'Unassigned' }}
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} font-size-13 mb-2">{{ ucfirst($payroll->status) }}</span>
                    <div class="text-muted font-size-13">{{ $payroll->period_label }} · {{ $payroll->currency ?? 'USD' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h3 class="mb-1">{{ $payroll->working_days }}</h3>
                <p class="text-muted mb-0 font-size-13"><i class="bx bx-calendar me-1"></i>Working Days</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ $payroll->present_days }}</h3>
                <p class="text-muted mb-0 font-size-13"><i class="bx bx-check-square me-1"></i>Present</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ $payroll->absent_days }}</h3>
                <p class="text-muted mb-0 font-size-13"><i class="bx bx-x-square me-1"></i>Absent</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h3 class="mb-1 text-info">{{ $payroll->leave_days }}</h3>
                <p class="text-muted mb-0 font-size-13"><i class="bx bx-coffee me-1"></i>Leave / Holiday</p>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <div class="cds-kpi-icon bg-primary-subtle text-primary mx-auto mb-2"><i class="bx bx-trending-up"></i></div>
                    <p class="text-muted mb-1 font-size-13">Gross Salary</p>
                    <h4 class="mb-0">{{ $money($payroll->gross_salary) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <div class="cds-kpi-icon bg-danger-subtle text-danger mx-auto mb-2"><i class="bx bx-money-withdraw"></i></div>
                    <p class="text-muted mb-1 font-size-13">Total Deductions</p>
                    <h4 class="mb-0">{{ $money((float) $payroll->deductions + (float) $payroll->tax + (float) $payroll->absent_deduction) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <div class="cds-kpi-icon bg-success-subtle text-success mx-auto mb-2"><i class="bx bx-wallet"></i></div>
                    <p class="text-muted mb-1 font-size-13">Net Salary</p>
                    <h4 class="mb-0 text-success">{{ $money($payroll->net_salary) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Earnings</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Component</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($earnings as $label => $amount)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-end fw-semibold">{{ $money($amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="fw-semibold">Gross Salary</td>
                                    <td class="text-end fw-bold">{{ $money($payroll->gross_salary) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Deductions</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Component</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($deductions as $label => $amount)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-end fw-semibold text-danger">- {{ $money($amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="fw-semibold">Net Salary</td>
                                    <td class="text-end fw-bold text-success">{{ $money($payroll->net_salary) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Salary Components</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Component</th><th>Type</th><th>Source</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($payroll->items as $item)
                                    <tr>
                                        <td class="fw-medium">{{ $item->label }}</td>
                                        <td>
                                            @if ($item->type === 'earning')
                                                <span class="badge bg-success-subtle text-success">Earning</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Deduction</span>
                                            @endif
                                        </td>
                                        <td><span class="text-muted font-size-13">{{ $item->source ?? '—' }}</span></td>
                                        <td class="text-end fw-semibold {{ $item->type === 'deduction' ? 'text-danger' : 'text-success' }}">
                                            {{ $item->type === 'deduction' ? '- ' : '' }}{{ $money($item->amount) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No salary components recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Approval &amp; Payment</h5></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($payroll->status) }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Approved On</span>
                            <span>{{ $payroll->approved_at?->format('d M Y H:i') ?? '—' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Approved By</span>
                            <span>{{ $payroll->approver?->name ?? '—' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Paid On</span>
                            <span>{{ $payroll->paid_on?->format('d M Y') ?? '—' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Payment Method</span>
                            <span>{{ $payroll->payment_method ? str_replace('_', ' ', ucfirst($payroll->payment_method)) : '—' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Generated By</span>
                            <span>{{ $payroll->creator?->name ?? '—' }}</span>
                        </li>
                    </ul>
                    @if (filled($payroll->notes))
                        <hr>
                        <p class="text-muted font-size-13 mb-0">{{ $payroll->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
