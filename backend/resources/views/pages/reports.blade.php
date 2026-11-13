@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('page-actions')
    @can('reports.export')
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-download me-1"></i> Export CSV
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                @foreach (['sales', 'leads', 'payroll', 'attendance', 'customers'] as $type)
                    <a class="dropdown-item" href="{{ url('/reports/export') }}?type={{ $type }}&from={{ $from->toDateString() }}&to={{ $to->toDateString() }}">
                        <i class="bx bx-file me-1"></i> {{ ucfirst($type) }} report
                    </a>
                @endforeach
            </div>
        </div>
    @endcan
    <a href="{{ url('/analytics') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-pie-chart-alt-2 me-1"></i> Analytics</a>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ url('/reports') }}">
                <div class="col-md-3">
                    <label class="form-label mb-1">From</label>
                    <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">To</label>
                    <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Apply</button>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="text-muted font-size-13">{{ $from->format('d M Y') }} &rarr; {{ $to->format('d M Y') }}</span>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Total Sales</p>
                <h4 class="mb-0">{{ $money($kpis['sales_total']) }}</h4>
                <span class="font-size-12 text-muted">{{ number_format($kpis['orders_total']) }} orders all time</span>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">This Month</p>
                <h4 class="mb-0">{{ $money($kpis['sales_this_month']) }}</h4>
                <span class="font-size-12 {{ $kpis['sales_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bx {{ $kpis['sales_growth'] >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                    {{ \App\Support\Money::percent($kpis['sales_growth']) }} vs last month
                </span>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Average Order</p>
                <h4 class="mb-0">{{ $money($kpis['avg_sale_value']) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Profit</p>
                <h4 class="mb-0 text-success">{{ $money($kpis['profit_total']) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Conversion</p>
                <h4 class="mb-0">{{ \App\Support\Money::percent($kpis['conversion_rate']) }}</h4>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-target-lock"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Leads</p>
                        <h4 class="mb-0">{{ number_format($kpis['leads_total']) }}</h4>
                        <span class="font-size-12 text-muted">{{ number_format($kpis['leads_won']) }} won &middot; {{ number_format($kpis['leads_open']) }} open</span>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-user-voice"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Customers</p>
                        <h4 class="mb-0">{{ number_format($customers) }}</h4>
                        <span class="font-size-12 text-muted">{{ number_format($kpis['customers_new_month']) }} new this month</span>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-secondary-subtle text-secondary"><i class="bx bx-group"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Employees</p>
                        <h4 class="mb-0">{{ number_format($employees) }}</h4>
                        <span class="font-size-12 text-muted">{{ number_format($kpis['employees_active']) }} active</span>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-danger-subtle text-danger"><i class="bx bx-receipt"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Outstanding Invoices</p>
                        <h4 class="mb-0 text-danger">{{ $money($kpis['invoices_outstanding']) }}</h4>
                        <span class="font-size-12 text-muted">{{ number_format($kpis['invoices_overdue']) }} overdue</span>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Monthly Sales &amp; Orders</h5>
                    <div id="cds-report-monthly" style="min-height: 330px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Attendance Summary</h5>
                    <div id="cds-report-attendance" style="min-height: 330px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Salesperson Performance</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Salesperson</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-end">Cost</th>
                                    <th class="text-end">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesByUser as $row)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $row->salesperson?->avatarUrl() ?? asset('assets/images/users/avatar-1.jpg') }}" class="rounded-circle avatar-xs" alt="">
                                                <span class="ms-2">{{ $row->salesperson?->name ?? 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">{{ number_format((int) $row->orders) }}</td>
                                        <td class="text-end fw-semibold">{{ $money($row->total) }}</td>
                                        <td class="text-end text-warning">{{ $money($row->commission) }}</td>
                                        <td class="text-end">{{ $money($row->cost) }}</td>
                                        <td class="text-end text-success">{{ $money((float) $row->total - (float) $row->cost) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No sales in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Leads per User</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th class="text-end">Leads</th>
                                    <th class="text-end">Won</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leadsByUser as $row)
                                    <tr>
                                        <td>{{ $row->assignedUser?->name ?? 'Unassigned' }}</td>
                                        <td class="text-end">{{ number_format((int) $row->total) }}</td>
                                        <td class="text-end text-success">{{ number_format((int) $row->won) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No leads in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Payroll Summary</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th class="text-end">Net Salary</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-end">Bonus &amp; Incentive</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payrollSummary as $row)
                                    <tr>
                                        <td>{{ $row->period_label }}</td>
                                        <td class="text-end fw-semibold">{{ $money($row->net) }}</td>
                                        <td class="text-end text-warning">{{ $money($row->commission) }}</td>
                                        <td class="text-end">{{ $money($row->bonus) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No payroll in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Compensation Summary</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compensationSummary as $row)
                                    <tr>
                                        <td><span class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', (string) $row->type)) }}</span></td>
                                        <td class="text-end fw-semibold">{{ $money($row->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">No compensation in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <h5 class="card-title mb-0">Outstanding Invoices</h5>
                        <a href="{{ url('/invoices') }}" class="btn btn-sm btn-link">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Due</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Outstanding</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outstanding as $invoice)
                                    <tr>
                                        <td><a href="{{ url('/invoices/'.$invoice->id) }}" class="text-body fw-medium">{{ $invoice->invoice_no }}</a></td>
                                        <td>{{ $invoice->billing_name }}</td>
                                        <td class="text-muted font-size-13">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                                        <td class="text-end">{{ $money($invoice->amount) }}</td>
                                        <td class="text-end fw-semibold text-danger">{{ $money($invoice->outstanding()) }}</td>
                                        <td><span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }}">{{ ucfirst((string) $invoice->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">All invoices are settled.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Top Products</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Rating</th>
                                    <th class="text-end">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topProducts as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ url('/products/'.$product->id) }}" class="text-body fw-medium">{{ $product->name }}</a>
                                            <div class="text-muted font-size-12">{{ $product->sku ?? 'No SKU' }}</div>
                                        </td>
                                        <td class="text-end fw-semibold">{{ $money($product->price) }}</td>
                                        <td class="text-end">{{ number_format((float) $product->rating, 1) }}</td>
                                        <td class="text-end">{{ number_format((int) $product->stock) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No products found.</td></tr>
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
        document.addEventListener('DOMContentLoaded', function () {
            var currency = @json(\App\Support\Branding::currencySymbol());
            var monthly = @json($monthly);
            var attendance = @json($attendanceSummary);

            if (document.querySelector('#cds-report-monthly')) {
                new ApexCharts(document.querySelector('#cds-report-monthly'), {
                    chart: { type: 'line', height: 340, toolbar: { show: false }, zoom: { enabled: false } },
                    series: [
                        { name: 'Sales', type: 'column', data: monthly.totals },
                        { name: 'Orders', type: 'line', data: monthly.orders },
                    ],
                    xaxis: { categories: monthly.labels, labels: { rotate: -35, style: { fontSize: '11px' } } },
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
                }).render();
            }

            if (document.querySelector('#cds-report-attendance')) {
                var labels = Object.keys(attendance).map(function (key) { return String(key).replace(/_/g, ' '); });
                var values = Object.keys(attendance).map(function (key) { return attendance[key]; });

                new ApexCharts(document.querySelector('#cds-report-attendance'), {
                    chart: { type: 'donut', height: 330 },
                    series: values.length ? values : [1],
                    labels: labels.length ? labels : ['No data'],
                    colors: ['#34c38f', '#f1b44c', '#50a5f1', '#f46a6a', '#74788d', '#1e7df0'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                }).render();
            }
        });
    </script>
@endpush
