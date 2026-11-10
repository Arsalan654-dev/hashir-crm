@extends('layouts.app')

@section('title', 'Sales Reports')
@section('page-title', 'Sales Reports')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/sales') }}">Sales</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('page-actions')
    <a href="{{ url('/sales/export') }}?from={{ $from->toDateString() }}&to={{ $to->toDateString() }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-download me-1"></i> Export CSV</a>
    <a href="{{ url('/sales') }}" class="btn btn-outline-primary btn-sm">All Sales</a>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ url('/sales/reports') }}">
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
            <div class="card mb-0"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Revenue</p>
                <h4 class="mb-0">{{ $money($totals['revenue']) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card mb-0"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Cost</p>
                <h4 class="mb-0">{{ $money($totals['cost']) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card mb-0"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Commission</p>
                <h4 class="mb-0 text-warning">{{ $money($totals['commission']) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card mb-0"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Orders</p>
                <h4 class="mb-0">{{ number_format($totals['orders']) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0"><div class="card-body">
                <p class="text-muted mb-1 font-size-13">Average Sale</p>
                <h4 class="mb-0 text-primary">{{ $money($totals['average']) }}</h4>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Daily Revenue &amp; Orders</h5>
                    <div id="cds-sales-daily" style="min-height: 330px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Sales by Source</h5>
                    <div id="cds-sales-source" style="min-height: 330px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Performance by Salesperson</h5>
                    <div id="cds-salespeople" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Status Breakdown</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Status</th><th class="text-end">Sales</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($byStatus as $status => $count)
                                    <tr>
                                        <td>@include('partials.sale-status', ['status' => $status])</td>
                                        <td class="text-end fw-semibold">{{ number_format((int) $count) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">No completed sales in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <h6 class="mb-2">Sources</h6>
                    @forelse ($bySource as $source)
                        <div class="d-flex align-items-center justify-content-between py-1">
                            <span>{{ ucwords(str_replace('_', ' ', (string) $source->source)) }}</span>
                            <span class="text-muted font-size-13">{{ number_format((int) $source->total) }} &middot; {{ $money($source->amount) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No source data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Salesperson Summary</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Salesperson</th>
                            <th class="text-end">Orders</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bySalesperson as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $row->salesperson?->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                        <span class="ms-2">{{ $row->salesperson?->name ?? 'Unassigned' }}</span>
                                    </div>
                                </td>
                                <td class="text-end">{{ number_format((int) $row->orders) }}</td>
                                <td class="text-end fw-semibold">{{ $money($row->total) }}</td>
                                <td class="text-end text-warning">{{ $money($row->commission) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No sales in this period.</td></tr>
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
            var byDay = @json($byDay);
            var bySalesperson = @json($bySalesperson);
            var bySource = @json($bySource);
            var currency = @json(\App\Support\Branding::currencySymbol());

            if (document.querySelector('#cds-sales-daily')) {
                new ApexCharts(document.querySelector('#cds-sales-daily'), {
                    chart: { type: 'line', height: 340, toolbar: { show: false } },
                    series: [
                        { name: 'Revenue', type: 'column', data: byDay.map(function (row) { return Math.round(row.total); }) },
                        { name: 'Orders', type: 'line', data: byDay.map(function (row) { return Number(row.orders); }) },
                    ],
                    xaxis: { categories: byDay.map(function (row) { return row.bucket; }), labels: { rotate: -35, style: { fontSize: '11px' } } },
                    yaxis: [
                        { title: { text: 'Revenue' }, labels: { formatter: function (v) { return currency + Math.round(v); } } },
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

            if (document.querySelector('#cds-salespeople')) {
                new ApexCharts(document.querySelector('#cds-salespeople'), {
                    chart: { type: 'bar', height: 330, toolbar: { show: false } },
                    series: [
                        { name: 'Revenue', data: bySalesperson.map(function (row) { return Math.round(row.total); }) },
                        { name: 'Commission', data: bySalesperson.map(function (row) { return Math.round(row.commission); }) },
                    ],
                    xaxis: { categories: bySalesperson.map(function (row) { return row.salesperson ? row.salesperson.name : 'Unassigned'; }), labels: { rotate: -35, style: { fontSize: '11px' } } },
                    colors: ['#1e7df0', '#f1b44c'],
                    plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' },
                    grid: { borderColor: '#eef2f7' },
                }).render();
            }

            if (document.querySelector('#cds-sales-source')) {
                var sources = bySource.map(function (row) { return row.source ? row.source.replace(/_/g, ' ') : 'unknown'; });
                var amounts = bySource.map(function (row) { return Math.round(row.amount); });

                new ApexCharts(document.querySelector('#cds-sales-source'), {
                    chart: { type: 'donut', height: 330 },
                    series: amounts.length ? amounts : [1],
                    labels: sources.length ? sources : ['No data'],
                    colors: ['#1e7df0', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1', '#74788d'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                }).render();
            }
        });
    </script>
@endpush
