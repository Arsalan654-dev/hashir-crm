@extends('layouts.app')

@section('title', 'Payroll Reports')
@section('page-title', 'Payroll Reports — '.$year)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/payroll') }}">Payroll</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $chartRows = $months->map(fn ($row) => [
        'label' => \Illuminate\Support\Carbon::create($year, (int) $row->period_month, 1)->format('M'),
        'net' => (float) $row->net,
        'commission' => (float) $row->commission,
        'bonus' => (float) $row->bonus,
        'incentive' => (float) $row->incentive,
    ])->values();
    $totals = [
        'net' => (float) $months->sum('net'),
        'commission' => (float) $months->sum('commission'),
        'bonus' => (float) $months->sum('bonus') + (float) $months->sum('incentive'),
        'employees' => (int) $months->sum('employees'),
    ];
@endphp

@section('page-actions')
    <a href="{{ url('/payroll') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-list-ul me-1"></i> Payroll Register</a>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/payroll/reports') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Year</label>
                    <select name="year" class="form-select">
                        @foreach (range(now()->year - 4, now()->year + 1) as $option)
                            <option value="{{ $option }}" @selected($year === (int) $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h4 class="mb-1 text-success">{{ \App\Support\Money::compact($totals['net']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Net Payroll</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h4 class="mb-1 text-info">{{ \App\Support\Money::compact($totals['commission']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Commission</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h4 class="mb-1 text-warning">{{ \App\Support\Money::compact($totals['bonus']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Bonus &amp; Incentive</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body text-center">
                <h4 class="mb-1">{{ number_format($totals['employees']) }}</h4>
                <p class="text-muted mb-0 font-size-13">Payroll Entries</p>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Monthly Payroll Breakdown</h5>
            <div id="cds-payroll-reports-chart" style="min-height: 340px;"></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Department Totals</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Department</th><th class="text-end">Employees</th><th class="text-end">Net Payroll</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    <tr>
                                        <td class="fw-medium">{{ $department->department }}</td>
                                        <td class="text-end">{{ number_format((int) $department->employees) }}</td>
                                        <td class="text-end fw-semibold">{{ $money($department->net) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No payroll data for {{ $year }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Top Earners</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>Employee</th><th>Period</th><th class="text-end">Net Salary</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($topEarners as $index => $earner)
                                    <tr>
                                        <td class="text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $earner->user?->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                                <div class="ms-2">
                                                    <a href="{{ url('/payroll/'.$earner->id) }}" class="text-body fw-medium">{{ $earner->user?->name ?? '—' }}</a>
                                                    <div class="text-muted font-size-12">{{ $earner->user?->employee_code ?? '—' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-muted font-size-13">{{ $earner->period_label }}</span></td>
                                        <td class="text-end fw-semibold">{{ $money($earner->net_salary) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No payroll data for {{ $year }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var rows = @json($chartRows);
            var el = document.querySelector('#cds-payroll-reports-chart');
            if (!el) { return; }
            new ApexCharts(el, {
                chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: false } },
                series: [
                    { name: 'Net Salary', data: rows.map(function (r) { return Math.round(r.net); }) },
                    { name: 'Commission', data: rows.map(function (r) { return Math.round(r.commission); }) },
                    { name: 'Bonus', data: rows.map(function (r) { return Math.round(r.bonus); }) },
                    { name: 'Incentive', data: rows.map(function (r) { return Math.round(r.incentive); }) },
                ],
                xaxis: { categories: rows.map(function (r) { return r.label; }) },
                colors: ['#1e7df0', '#34c38f', '#f1b44c', '#f46a6a'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                yaxis: { labels: { formatter: function (v) { return '{{ \App\Support\Branding::currencySymbol() }}' + Math.round(v).toLocaleString(); } } },
                noData: { text: 'No payroll data for this year' },
            }).render();
        })();
    </script>
@endpush
