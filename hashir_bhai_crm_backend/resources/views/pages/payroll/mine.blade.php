@extends('layouts.app')

@section('title', 'My Payslips')
@section('page-title', 'My Payslips')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/payroll') }}">Payroll</a></li>
    <li class="breadcrumb-item active">My Payslips</li>
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $statusColors = ['draft' => 'warning', 'approved' => 'info', 'paid' => 'success'];
    $chartRows = $payrolls->getCollection()
        ->sortBy(fn ($row) => $row->period_year * 100 + $row->period_month)
        ->map(fn ($row) => ['label' => $row->period_label, 'net' => (float) $row->net_salary])
        ->values();
@endphp

@section('page-actions')
    <a href="{{ url('/payroll/reports') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-bar-chart-alt-2 me-1"></i> Reports</a>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-4 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-wallet"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Lifetime Earnings</p>
                            <h4 class="mb-0">{{ $money($lifetimeEarnings) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-line-chart"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Lifetime Commission</p>
                            <h4 class="mb-0">{{ $money($lifetimeCommission) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-gift"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Lifetime Bonus</p>
                            <h4 class="mb-0">{{ $money($lifetimeBonus) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Monthly Net Salary Trend</h5>
            <div id="cds-my-payroll-trend" style="min-height: 320px;"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">My Payslips</h5>
            <span class="text-muted font-size-13">{{ number_format($payrolls->total()) }} entries</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Period</th>
                            <th class="text-end">Basic</th>
                            <th class="text-end">Commission</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Net Salary</th>
                            <th>Status</th>
                            <th>Paid On</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            @php($statusColor = $statusColors[$payroll->status] ?? 'secondary')
                            <tr>
                                <td>
                                    <a href="{{ url('/payroll/'.$payroll->id) }}" class="text-body fw-medium">{{ $payroll->period_label }}</a>
                                    <div class="text-muted font-size-12">{{ $payroll->currency ?? 'USD' }}</div>
                                </td>
                                <td class="text-end">{{ $money($payroll->basic_salary) }}</td>
                                <td class="text-end">{{ $money($payroll->commission) }}</td>
                                <td class="text-end">{{ $money($payroll->bonus) }}</td>
                                <td class="text-end fw-semibold">{{ $money($payroll->net_salary) }}</td>
                                <td><span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($payroll->status) }}</span></td>
                                <td>
                                    <span class="text-muted font-size-13">{{ $payroll->paid_on?->format('d M Y') ?? '—' }}</span>
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/payroll/'.$payroll->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ url('/payroll/'.$payroll->id.'/payslip') }}" class="btn btn-sm btn-outline-secondary" title="Download payslip"><i class="bx bx-download"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No payslips generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $payrolls->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var rows = @json($chartRows);
            var el = document.querySelector('#cds-my-payroll-trend');
            if (!el) { return; }
            new ApexCharts(el, {
                chart: { type: 'area', height: 330, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Net Salary', data: rows.map(function (r) { return Math.round(r.net * 100) / 100; }) }],
                xaxis: { categories: rows.map(function (r) { return r.label; }), labels: { rotate: -35, style: { fontSize: '11px' } } },
                colors: ['#34c38f'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#eef2f7' },
                yaxis: { labels: { formatter: function (v) { return '{{ \App\Support\Branding::currencySymbol() }}' + v.toLocaleString(); } } },
                noData: { text: 'No payroll data yet' },
            }).render();
        })();
    </script>
@endpush
