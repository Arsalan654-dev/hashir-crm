@extends('layouts.app')

@section('title', 'Monthly Payroll')
@section('page-title', 'Monthly Payroll')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payroll</li>
@endsection

@section('page-actions')
    <a href="{{ url('/payroll/reports') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-pie-chart-alt-2 me-1"></i> Payroll Summary</a>
    <a href="{{ url('/payroll/my') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-receipt me-1"></i> My Payslips</a>
@endsection

@php
    $statusColors = [
        'draft' => 'secondary',
        'approved' => 'primary',
        'paid' => 'success',
    ];
@endphp

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/payroll') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Month</label>
                    <select name="month" class="form-select">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month === $m)>{{ \Illuminate\Support\Carbon::create(null, $m, 1)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Year</label>
                    <select name="year" class="form-select">
                        @foreach (range(now()->year - 4, now()->year + 1) as $optionYear)
                            <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> View</button>
                </div>
                <div class="col-md-5 text-md-end">
                    <span class="text-muted font-size-13">Payroll period: {{ $periodLabel }}</span>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-group"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Employees</p>
                        <h4 class="mb-0">{{ number_format($summary['employees']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-wallet"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Gross</p>
                        <h4 class="mb-0">{{ \App\Support\Money::compact($summary['gross']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-check-shield"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Net Payable</p>
                        <h4 class="mb-0">{{ \App\Support\Money::compact($summary['net']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-danger-subtle text-danger"><i class="bx bx-minus-circle"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Deductions</p>
                        <h4 class="mb-0">{{ \App\Support\Money::compact($summary['deductions']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h4 class="mb-1">{{ \App\Support\Money::compact($summary['commission']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Commission</p>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h4 class="mb-1">{{ \App\Support\Money::compact($summary['bonus']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Bonus &amp; Incentive</p>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h4 class="mb-1 text-warning">{{ number_format($summary['draft']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Draft</p>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h4 class="mb-1 text-success">{{ number_format($summary['paid']) }} / {{ number_format($summary['approved']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Paid / Approved</p>
            </div></div>
        </div>
    </div>

    @can('payroll.manage')
        <div class="card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Generate Payroll — {{ $periodLabel }}</h5></div>
            <div class="card-body">
                <form action="{{ url('/payroll/generate') }}" method="POST" class="row g-2 align-items-end" onsubmit="return confirm('Generate payroll for the selected period?');">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div class="col-md-5">
                        <label class="form-label mb-1">Employee</label>
                        <select name="user_id" class="form-select">
                            <option value="">All active employees</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}@if ($employee->basic_salary) — {{ \App\Support\Money::format($employee->basic_salary) }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="force" value="1" id="cds-force">
                            <label class="form-check-label" for="cds-force">Force regenerate paid entries</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100"><i class="bx bx-refresh me-1"></i> Generate Payroll</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{ $periodLabel }} Payroll Entries</h5>
            <span class="text-muted font-size-13">{{ $payrolls->count() }} entries</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th class="text-end">Basic</th>
                            <th class="text-end">Allowances</th>
                            <th class="text-end">Commission</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end">Net</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $payroll->user?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $payroll->user?->name ?? 'Unknown' }}</h6>
                                            <span class="text-muted font-size-12">{{ $payroll->user?->employee_code ?? $payroll->user?->department?->name ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">{{ \App\Support\Money::format($payroll->basic_salary) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($payroll->allowances) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($payroll->commission) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format((float) $payroll->bonus + (float) $payroll->incentive) }}</td>
                                <td class="text-end text-danger">{{ \App\Support\Money::format((float) $payroll->deductions + (float) $payroll->tax + (float) $payroll->absent_deduction) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($payroll->gross_salary) }}</td>
                                <td class="text-end fw-semibold">{{ \App\Support\Money::format($payroll->net_salary) }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$payroll->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$payroll->status] ?? 'secondary' }}">{{ ucfirst($payroll->status) }}</span>
                                    @if ($payroll->paid_on)
                                        <div class="text-muted font-size-12">Paid {{ $payroll->paid_on->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/payroll/'.$payroll->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bx bx-show"></i></a>
                                    <a href="{{ url('/payroll/'.$payroll->id.'/payslip') }}" class="btn btn-sm btn-outline-info" title="Payslip PDF"><i class="bx bx-file"></i></a>
                                    @can('payroll.approve')
                                        @if ($payroll->status === 'draft')
                                            <form action="{{ url('/payroll/'.$payroll->id.'/approve') }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary" title="Approve"><i class="bx bx-check"></i></button>
                                            </form>
                                        @endif
                                    @endcan
                                    @can('payroll.pay', 'payroll.manage')
                                        @if ($payroll->status === 'approved')
                                            <form action="{{ url('/payroll/'.$payroll->id.'/pay') }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" title="Mark paid"><i class="bx bx-money"></i></button>
                                            </form>
                                        @endif
                                    @endcan
                                    @can('payroll.manage')
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#cds-payroll-edit"
                                            data-action="{{ url('/payroll/'.$payroll->id) }}"
                                            data-name="{{ $payroll->user?->name }}"
                                            data-allowances="{{ $payroll->allowances }}"
                                            data-deductions="{{ $payroll->deductions }}"
                                            data-tax="{{ $payroll->tax }}"
                                            data-payment="{{ $payroll->payment_method }}"
                                            data-notes="{{ $payroll->notes }}">Edit</button>
                                        @if ($payroll->status !== 'paid')
                                            <form action="{{ url('/payroll/'.$payroll->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payroll entry?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No payroll entries for {{ $periodLabel }}. Use the generate form above.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('payroll.manage')
        <div class="modal fade" id="cds-payroll-edit" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="cds-payroll-edit-form" action="{{ url('/payroll') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Payroll — <span id="cds-payroll-edit-name"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Allowances</label>
                                    <input type="number" name="allowances" step="0.01" min="0" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Deductions</label>
                                    <input type="number" name="deductions" step="0.01" min="0" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tax</label>
                                    <input type="number" name="tax" step="0.01" min="0" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">Keep current</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="online">Online</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Payroll</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script>
        (function () {
            var editModal = document.getElementById('cds-payroll-edit');

            if (!editModal) { return; }

            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var form = document.getElementById('cds-payroll-edit-form');
                form.setAttribute('action', button.getAttribute('data-action'));
                document.getElementById('cds-payroll-edit-name').textContent = button.getAttribute('data-name') || '';
                form.querySelector('[name="allowances"]').value = button.getAttribute('data-allowances') || 0;
                form.querySelector('[name="deductions"]').value = button.getAttribute('data-deductions') || 0;
                form.querySelector('[name="tax"]').value = button.getAttribute('data-tax') || 0;
                form.querySelector('[name="payment_method"]').value = button.getAttribute('data-payment') || '';
                form.querySelector('[name="notes"]').value = button.getAttribute('data-notes') || '';
            });
        })();
    </script>
@endpush
