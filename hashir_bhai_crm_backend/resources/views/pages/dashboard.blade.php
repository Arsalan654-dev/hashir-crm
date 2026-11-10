@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Overview</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('sales.create'))
        <a href="{{ url('/sales/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> New Sale</a>
    @endif
    @if (auth()->user()->can('leads.create'))
        <a href="{{ url('/leads/create') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-target-lock me-1"></i> Add Lead</a>
    @endif
    @if (auth()->user()->can('analytics.view_all', 'analytics.view_own'))
        <a href="{{ url('/analytics') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-pie-chart-alt-2 me-1"></i> Analytics</a>
    @endif
@endsection

@php
    $user = auth()->user();
    $money = fn ($value) => \App\Support\Money::format($value);
    $canAny = fn (string ...$permissions): bool => $user->hasAnyPermission($permissions);
@endphp

@section('content')
    {{-- KPI cards --}}
    <div class="row g-3 mb-3">
        @if ($canAny('sales.view'))
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-dollar-circle"></i></div>
                            <div class="ms-3 flex-1">
                                <p class="text-muted mb-1 font-size-13">Total Sales ({{ $companyWide ? 'Company' : 'Mine' }})</p>
                                <h4 class="mb-0">{{ $money($kpis['sales_total']) }}</h4>
                                <span class="font-size-12 {{ $kpis['sales_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bx {{ $kpis['sales_growth'] >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                    {{ \App\Support\Money::percent($kpis['sales_growth']) }} vs last month
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-calendar-check"></i></div>
                            <div class="ms-3 flex-1">
                                <p class="text-muted mb-1 font-size-13">This Month / Today</p>
                                <h4 class="mb-0">{{ $money($kpis['sales_this_month']) }}</h4>
                                <span class="font-size-12 text-muted">{{ $money($kpis['sales_today']) }} today · {{ $kpis['orders_this_month'] }} orders</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-target-lock"></i></div>
                            <div class="ms-3 flex-1">
                                <p class="text-muted mb-1 font-size-13">Leads</p>
                                <h4 class="mb-0">{{ number_format($kpis['leads_total']) }}</h4>
                                <span class="font-size-12 text-muted">{{ $kpis['leads_new'] }} new · {{ \App\Support\Money::percent($kpis['conversion_rate']) }} won</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-time-five"></i></div>
                            <div class="ms-3 flex-1">
                                <p class="text-muted mb-1 font-size-13">Pending Approvals</p>
                                <h4 class="mb-0">{{ number_format($kpis['pending_sales']) }}</h4>
                                <span class="font-size-12 text-muted">{{ $kpis['confirmed_sales'] }} confirmed sales</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3 mb-3">
        @if ($canAny('employees.view'))
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $kpis['employees_total'] }}</h3>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-group me-1"></i>Employees</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($canAny('customers.view'))
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $kpis['customers_total'] }}</h3>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-user-voice me-1"></i>Customers</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($canAny('whatsapp.view'))
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $kpis['whatsapp_contacts'] }}</h3>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bxl-whatsapp me-1"></i>WhatsApp Leads</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($canAny('bot.view'))
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $kpis['bot_conversations'] }}</h3>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-bot me-1"></i>Bot Chats</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($canAny('payroll.view_all', 'payroll.manage'))
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ \App\Support\Money::compact($kpis['payroll_this_month']) }}</h4>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-wallet me-1"></i>Payroll (month)</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($canAny('invoices.view'))
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    <div class="card-body text-center">
                        <h4 class="mb-1 text-danger">{{ \App\Support\Money::compact($kpis['invoices_outstanding']) }}</h4>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-receipt me-1"></i>Outstanding</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Charts --}}
    @if ($canAny('sales.view', 'analytics.view_all', 'analytics.view_own'))
        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Sales Performance</h5>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-trend="monthly">Monthly</button>
                                <button type="button" class="btn btn-outline-primary" data-trend="weekly">Weekly</button>
                                <button type="button" class="btn btn-outline-primary" data-trend="daily">Daily</button>
                            </div>
                        </div>
                        <div id="cds-sales-trend" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Lead Pipeline</h5>
                        <div id="cds-lead-status" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-3">
        @if ($companyWide && $canAny('sales.view_all'))
            <div class="col-xl-6">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Top Sales Performers</h5>
                        <div id="cds-top-salespeople" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        @endif
        @if ($canAny('payroll.view_all', 'payroll.manage', 'compensations.view'))
            <div class="col-xl-6">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Payroll &amp; Commissions</h5>
                        <div id="cds-payroll-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3">
        @isset($recentSales)
            <div class="col-xl-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Recent Sales</h5>
                            <a href="{{ url('/sales') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="table-responsive cds-scroll">
                            <table class="table table-sm table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Sale</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentSales as $sale)
                                        <tr>
                                            <td>
                                                <a href="{{ url('/sales/'.$sale->id) }}" class="text-body fw-medium">{{ $sale->sale_no }}</a>
                                                <div class="text-muted font-size-12">{{ $sale->sale_date->format('d M Y') }} · {{ $sale->salesperson?->name }}</div>
                                            </td>
                                            <td>{{ $sale->customer_name }}</td>
                                            <td class="fw-semibold">{{ $money($sale->total) }}</td>
                                            <td>@include('partials.sale-status', ['status' => $sale->status])</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No sales recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endisset

        @isset($recentLeads)
            <div class="col-xl-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Latest Leads</h5>
                            <a href="{{ url('/leads') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="table-responsive cds-scroll">
                            <table class="table table-sm table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Lead</th><th>Source</th><th>Value</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentLeads as $lead)
                                        <tr>
                                            <td>
                                                <a href="{{ url('/leads/'.$lead->id) }}" class="text-body fw-medium">{{ $lead->name }}</a>
                                                <div class="text-muted font-size-12">{{ $lead->assignedUser?->name ?? 'Unassigned' }} · {{ $lead->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td><span class="badge bg-light text-dark">{{ str_replace('_', ' ', ucfirst($lead->source)) }}</span></td>
                                            <td class="fw-semibold">{{ $money($lead->estimated_value) }}</td>
                                            <td>@include('partials.lead-status', ['status' => $lead->status])</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No leads yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endisset

        @isset($pendingLeaves)
            <div class="col-xl-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Pending Leave Requests</h5>
                            <a href="{{ url('/leaves') }}" class="btn btn-sm btn-link">Manage</a>
                        </div>
                        @forelse ($pendingLeaves as $leave)
                            <div class="d-flex align-items-center border-bottom py-2">
                                <img src="{{ $leave->user?->avatarUrl() }}" class="avatar-xs rounded-circle me-2" alt="">
                                <div class="flex-1">
                                    <h6 class="mb-0 font-size-14">{{ $leave->user?->name }}</h6>
                                    <span class="text-muted font-size-12">{{ ucfirst($leave->type) }} · {{ $leave->start_date->format('d M') }} → {{ $leave->end_date->format('d M') }}</span>
                                </div>
                                <form action="{{ url('/leaves/'.$leave->id.'/approve') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Approve</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4 mb-0">No pending requests.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endisset

        @isset($outstandingInvoices)
            <div class="col-xl-6">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Outstanding Invoices</h5>
                            <a href="{{ url('/invoices') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        @forelse ($outstandingInvoices as $invoice)
                            <div class="d-flex align-items-center border-bottom py-2">
                                <div class="flex-1">
                                    <h6 class="mb-0 font-size-14">{{ $invoice->invoice_no }} — {{ $invoice->billing_name }}</h6>
                                    <span class="text-muted font-size-12">Due {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</span>
                                </div>
                                <span class="fw-semibold text-danger">{{ $money((float) $invoice->amount - (float) $invoice->paid_amount) }}</span>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4 mb-0">All invoices are settled.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endisset
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var trends = @json($daily);
            var weekly = @json($charts);
            var monthly = @json($daily);
            var monthlyTrend = @json($breakdowns['top_salespeople'] ?? []);
            var leadStatus = @json($breakdowns['leads_by_status'] ?? []);
            var payroll = @json($breakdowns['payroll_by_month'] ?? []);
            var trendsAll = @json($daily);

            function areaChart(el, labels, series, name) {
                if (!document.querySelector(el)) { return null; }
                return new ApexCharts(document.querySelector(el), {
                    chart: { type: 'area', height: 330, toolbar: { show: false }, zoom: { enabled: false } },
                    series: [{ name: name, data: series }],
                    xaxis: { categories: labels, labels: { rotate: -35, style: { fontSize: '11px' } } },
                    colors: ['#1e7df0'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                    dataLabels: { enabled: false },
                    grid: { borderColor: '#eef2f7' },
                    yaxis: { labels: { formatter: function (v) { return '{{ \App\Support\Branding::currencySymbol() }}' + Math.round(v); } } },
                });
            }

            var trendChart = areaChart('#cds-sales-trend', trends.labels, trends.totals, 'Sales');

            document.querySelectorAll('[data-trend]').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.querySelectorAll('[data-trend]').forEach(function (b) { b.classList.remove('active'); });
                    button.classList.add('active');
                    var range = button.getAttribute('data-trend');
                    fetch('{{ url('/analytics/data') }}?range=' + range, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (r) { return r.json(); })
                        .then(function (payload) {
                            if (trendChart) {
                                trendChart.updateOptions({ xaxis: { categories: payload.chart.categories } });
                                trendChart.updateSeries([{ name: 'Sales', data: payload.chart.sales }]);
                            }
                        });
                });
            });

            if (document.querySelector('#cds-lead-status')) {
                var statusLabels = Object.keys(leadStatus);
                var statusValues = statusLabels.map(function (k) { return leadStatus[k]; });
                new ApexCharts(document.querySelector('#cds-lead-status'), {
                    chart: { type: 'donut', height: 330 },
                    series: statusValues.length ? statusValues : [1],
                    labels: statusLabels.length ? statusLabels : ['No leads'],
                    colors: ['#1e7df0', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1', '#74788d', '#343a40'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                }).render();
            }

            if (document.querySelector('#cds-top-salespeople') && monthlyTrend.length) {
                new ApexCharts(document.querySelector('#cds-top-salespeople'), {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Sales', data: monthlyTrend.map(function (r) { return Math.round(r.total); }) }],
                    xaxis: { categories: monthlyTrend.map(function (r) { return r.salesperson ? r.salesperson.name : 'N/A'; }) },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true } },
                    colors: ['#1e7df0', '#34c38f', '#50a5f1', '#f1b44c', '#f46a6a', '#74788d', '#556ee6', '#2a9d8f'],
                    dataLabels: { enabled: true, formatter: function (v) { return '{{ \App\Support\Branding::currencySymbol() }}' + v; } },
                    legend: { show: false },
                }).render();
            }

            if (document.querySelector('#cds-payroll-chart') && payroll.length) {
                new ApexCharts(document.querySelector('#cds-payroll-chart'), {
                    chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
                    series: [
                        { name: 'Net Salary', data: payroll.map(function (r) { return Math.round(r.total); }) },
                        { name: 'Commission', data: payroll.map(function (r) { return Math.round(r.commission); }) },
                        { name: 'Bonus', data: payroll.map(function (r) { return Math.round(r.bonus); }) },
                        { name: 'Incentive', data: payroll.map(function (r) { return Math.round(r.incentive); }) },
                    ],
                    xaxis: { categories: payroll.map(function (r) { return r.period_label; }) },
                    colors: ['#1e7df0', '#34c38f', '#f1b44c', '#f46a6a'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                    legend: { position: 'bottom' },
                }).render();
            }
        })();
    </script>
@endpush
