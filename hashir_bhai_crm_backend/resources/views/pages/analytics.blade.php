@extends('layouts.app')

@section('title', 'Analytics')
@section('page-title', 'Analytics')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Analytics</li>
@endsection

@section('page-actions')
    <a href="{{ url('/reports') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-file me-1"></i> Reports</a>
    @can('sales.view')
        <a href="{{ url('/sales') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-cart me-1"></i> Sales</a>
    @endcan
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-dollar-circle"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Total Sales ({{ $companyWide ? 'Company' : 'Mine' }})</p>
                            <h4 class="mb-0" data-kpi="sales_total" data-format="money">{{ $money($kpis['sales_total']) }}</h4>
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
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-calendar-check"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">This Month</p>
                            <h4 class="mb-0" data-kpi="sales_this_month" data-format="money">{{ $money($kpis['sales_this_month']) }}</h4>
                            <span class="font-size-12 text-muted">Last month {{ $money($kpis['sales_last_month']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-sun"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Today</p>
                            <h4 class="mb-0" data-kpi="sales_today" data-format="money">{{ $money($kpis['sales_today']) }}</h4>
                            <span class="font-size-12 text-muted">This week {{ $money($kpis['sales_this_week']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-receipt"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Orders</p>
                            <h4 class="mb-0" data-kpi="orders_total" data-format="number">{{ number_format($kpis['orders_total']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['orders_this_month']) }} this month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-purchase-tag"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Average Order Value</p>
                            <h4 class="mb-0" data-kpi="avg_sale_value" data-format="money">{{ $money($kpis['avg_sale_value']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['pending_sales']) }} pending &middot; {{ number_format($kpis['confirmed_sales']) }} confirmed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-line-chart"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Profit</p>
                            <h4 class="mb-0" data-kpi="profit_total" data-format="money">{{ $money($kpis['profit_total']) }}</h4>
                            <span class="font-size-12 text-muted">{{ $money($kpis['profit_this_month']) }} this month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-target-lock"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Leads</p>
                            <h4 class="mb-0" data-kpi="leads_total" data-format="number">{{ number_format($kpis['leads_total']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['leads_new']) }} new &middot; {{ number_format($kpis['leads_open']) }} open</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-trophy"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Conversion Rate</p>
                            <h4 class="mb-0" data-kpi="conversion_rate" data-format="percent">{{ \App\Support\Money::percent($kpis['conversion_rate']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['leads_won']) }} won &middot; {{ number_format($kpis['leads_lost']) }} lost</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-user-voice"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Customers</p>
                            <h4 class="mb-0" data-kpi="customers_total" data-format="number">{{ number_format($kpis['customers_total']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['customers_new_month']) }} new this month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-secondary-subtle text-secondary"><i class="bx bx-group"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Employees</p>
                            <h4 class="mb-0" data-kpi="employees_total" data-format="number">{{ number_format($kpis['employees_total']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['employees_active']) }} active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-wallet"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Payroll (Month)</p>
                            <h4 class="mb-0" data-kpi="payroll_this_month" data-format="compact">{{ \App\Support\Money::compact($kpis['payroll_this_month']) }}</h4>
                            <span class="font-size-12 text-muted">{{ \App\Support\Money::compact($kpis['compensation_this_month']) }} bonuses &amp; incentives</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-danger-subtle text-danger"><i class="bx bx-receipt"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Outstanding Invoices</p>
                            <h4 class="mb-0 text-danger" data-kpi="invoices_outstanding" data-format="compact">{{ \App\Support\Money::compact($kpis['invoices_outstanding']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['invoices_overdue']) }} overdue</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bxl-whatsapp"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">WhatsApp Contacts</p>
                            <h4 class="mb-0" data-kpi="whatsapp_contacts" data-format="number">{{ number_format($kpis['whatsapp_contacts']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['whatsapp_today']) }} active today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-bot"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Bot Chats</p>
                            <h4 class="mb-0" data-kpi="bot_conversations" data-format="number">{{ number_format($kpis['bot_conversations']) }}</h4>
                            <span class="font-size-12 text-muted">{{ number_format($kpis['bot_converted']) }} converted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Sales &amp; Orders Trend</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-range="monthly">Monthly</button>
                            <button type="button" class="btn btn-outline-primary" data-range="weekly">Weekly</button>
                            <button type="button" class="btn btn-outline-primary" data-range="daily">Daily</button>
                        </div>
                    </div>
                    <div id="cds-analytics-trend" style="min-height: 340px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Attendance Today</h5>
                    <div id="cds-analytics-attendance" style="min-height: 340px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Leads by Status</h5>
                    <div id="cds-analytics-lead-status" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Leads by Source</h5>
                    <div id="cds-analytics-lead-source" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Top Salespeople</h5>
                    <div id="cds-analytics-top-salespeople" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Payroll by Month</h5>
                    <div id="cds-analytics-payroll" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Payroll Trend (Net Salary)</h5>
                    <div id="cds-analytics-payroll-trend" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Top Products</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($breakdowns['top_products'] as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td class="text-end fw-semibold">{{ $money($product->total) }}</td>
                                <td class="text-end">{{ number_format((int) $product->qty) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No product sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var currency = @json(\App\Support\Branding::currencySymbol());
            var trends = @json($trends);
            var leadStatus = @json($breakdowns['leads_by_status']);
            var leadSource = @json($breakdowns['leads_by_source']);
            var salespeople = @json($breakdowns['top_salespeople']);
            var payroll = @json($breakdowns['payroll_by_month']);
            var payrollSeries = @json($payrollSeries);
            var attendance = @json($breakdowns['attendance_today']);

            function money(value) {
                return currency + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function compactMoney(value) {
                var amount = Number(value || 0);
                var absolute = Math.abs(amount);
                if (absolute >= 1000000000) { return currency + (amount / 1000000000).toFixed(2) + 'B'; }
                if (absolute >= 1000000) { return currency + (amount / 1000000).toFixed(2) + 'M'; }
                if (absolute >= 1000) { return currency + (amount / 1000).toFixed(1) + 'K'; }
                return currency + amount.toFixed(2);
            }

            function percent(value) {
                return Number(value || 0).toFixed(1) + '%';
            }

            var trendChart = null;

            function trendSeries(sales, orders) {
                return [
                    { name: 'Sales', data: sales },
                    { name: 'Orders', data: orders },
                ];
            }

            function rangePayload(range) {
                return trends[range] || trends.monthly;
            }

            if (document.querySelector('#cds-analytics-trend')) {
                var initial = rangePayload('monthly');

                trendChart = new ApexCharts(document.querySelector('#cds-analytics-trend'), {
                    chart: { type: 'line', height: 340, toolbar: { show: false }, zoom: { enabled: false } },
                    series: [
                        { name: 'Sales', type: 'column', data: initial.totals },
                        { name: 'Orders', type: 'line', data: initial.orders },
                    ],
                    xaxis: { categories: initial.labels, labels: { rotate: -35, style: { fontSize: '11px' } } },
                    yaxis: [
                        { title: { text: 'Sales' }, labels: { formatter: function (v) { return currency + Math.round(v); } } },
                        { opposite: true, title: { text: 'Orders' }, labels: { formatter: function (v) { return Math.round(v); } } },
                    ],
                    colors: ['#1e7df0', '#34c38f'],
                    stroke: { width: [0, 3], curve: 'smooth' },
                    plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' },
                    grid: { borderColor: '#eef2f7' },
                });

                trendChart.render();
            }

            document.querySelectorAll('[data-range]').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.querySelectorAll('[data-range]').forEach(function (other) { other.classList.remove('active'); });
                    button.classList.add('active');

                    var range = button.getAttribute('data-range');

                    fetch('{{ url('/analytics/data') }}?range=' + range, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (response) { return response.json(); })
                        .then(function (payload) {
                            if (trendChart) {
                                trendChart.updateOptions({ xaxis: { categories: payload.chart.categories } });
                                trendChart.updateSeries(trendSeries(payload.chart.sales, payload.chart.orders));
                            }

                            if (!payload.kpis) { return; }

                            document.querySelectorAll('[data-kpi]').forEach(function (element) {
                                var key = element.getAttribute('data-kpi');
                                if (payload.kpis[key] === undefined) { return; }

                                var format = element.getAttribute('data-format');
                                var value = payload.kpis[key];

                                if (format === 'money') { element.textContent = money(value); }
                                else if (format === 'compact') { element.textContent = compactMoney(value); }
                                else if (format === 'percent') { element.textContent = percent(value); }
                                else { element.textContent = Number(value).toLocaleString(); }
                            });
                        });
                });
            });

            function donut(selector, labels, values, colors) {
                if (!document.querySelector(selector)) { return; }

                new ApexCharts(document.querySelector(selector), {
                    chart: { type: 'donut', height: 300 },
                    series: values.length ? values : [1],
                    labels: labels.length ? labels : ['No data'],
                    colors: colors,
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                }).render();
            }

            var statusLabels = Object.keys(leadStatus);
            donut('#cds-analytics-lead-status', statusLabels, statusLabels.map(function (k) { return leadStatus[k]; }), ['#1e7df0', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1', '#74788d', '#343a40']);

            var sourceLabels = Object.keys(leadSource).map(function (k) { return String(k).replace(/_/g, ' '); });
            donut('#cds-analytics-lead-source', sourceLabels, Object.keys(leadSource).map(function (k) { return leadSource[k]; }), ['#1e7df0', '#34c38f', '#50a5f1', '#f1b44c', '#f46a6a', '#74788d', '#556ee6', '#2a9d8f']);

            var attendanceLabels = Object.keys(attendance).map(function (k) { return String(k).replace(/_/g, ' '); });
            donut('#cds-analytics-attendance', attendanceLabels, Object.keys(attendance).map(function (k) { return attendance[k]; }), ['#34c38f', '#f1b44c', '#50a5f1', '#f46a6a', '#74788d', '#1e7df0']);

            if (document.querySelector('#cds-analytics-top-salespeople')) {
                var people = salespeople || [];

                new ApexCharts(document.querySelector('#cds-analytics-top-salespeople'), {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [
                        { name: 'Sales', data: people.length ? people.map(function (row) { return Math.round(row.total); }) : [0] },
                        { name: 'Orders', data: people.length ? people.map(function (row) { return Number(row.orders); }) : [0] },
                    ],
                    xaxis: { categories: people.length ? people.map(function (row) { return row.salesperson ? row.salesperson.name : 'Unassigned'; }) : ['No data'] },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                    colors: ['#1e7df0', '#34c38f'],
                    dataLabels: { enabled: false },
                    legend: { position: 'top' },
                    grid: { borderColor: '#eef2f7' },
                }).render();
            }

            if (document.querySelector('#cds-analytics-payroll')) {
                var rows = payroll || [];

                new ApexCharts(document.querySelector('#cds-analytics-payroll'), {
                    chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
                    series: [
                        { name: 'Net Salary', data: rows.map(function (row) { return Math.round(row.total); }) },
                        { name: 'Commission', data: rows.map(function (row) { return Math.round(row.commission); }) },
                        { name: 'Bonus', data: rows.map(function (row) { return Math.round(row.bonus); }) },
                        { name: 'Incentive', data: rows.map(function (row) { return Math.round(row.incentive); }) },
                    ],
                    xaxis: { categories: rows.length ? rows.map(function (row) { return row.period_label; }) : ['No data'] },
                    colors: ['#1e7df0', '#34c38f', '#f1b44c', '#f46a6a'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom' },
                    grid: { borderColor: '#eef2f7' },
                }).render();
            }

            if (document.querySelector('#cds-analytics-payroll-trend')) {
                new ApexCharts(document.querySelector('#cds-analytics-payroll-trend'), {
                    chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                    series: [{ name: 'Net Salary', data: payrollSeries.map(function (row) { return Math.round(row.total); }) }],
                    xaxis: { categories: payrollSeries.map(function (row) { return row.name; }), labels: { rotate: -35, style: { fontSize: '11px' } } },
                    colors: ['#34c38f'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                    dataLabels: { enabled: false },
                    yaxis: { labels: { formatter: function (v) { return currency + Math.round(v); } } },
                    grid: { borderColor: '#eef2f7' },
                }).render();
            }
        });
    </script>
@endpush
