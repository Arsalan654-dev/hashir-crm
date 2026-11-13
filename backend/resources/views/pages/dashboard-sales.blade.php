@extends('layouts.app')

@section('title', 'Sales Dashboard')
@section('page-title', 'Sales Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('sales.create'))
        <a href="{{ url('/sales/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> New Sale Entry</a>
    @endif
    <a href="{{ url('/sales') }}" class="btn btn-outline-secondary btn-sm">All Sales</a>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Revenue This Month</p>
                    <h4 class="mb-1">{{ $money($kpis['sales_this_month']) }}</h4>
                    <span class="font-size-12 {{ $kpis['sales_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ \App\Support\Money::percent($kpis['sales_growth']) }} vs last month
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Average Sale Value</p>
                    <h4 class="mb-1">{{ $money($kpis['avg_sale_value']) }}</h4>
                    <span class="font-size-12 text-muted">{{ $kpis['orders_total'] }} completed sales</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Gross Profit</p>
                    <h4 class="mb-1">{{ $money($kpis['profit_total']) }}</h4>
                    <span class="font-size-12 text-muted">{{ $money($kpis['profit_this_month']) }} this month</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Lead Conversion</p>
                    <h4 class="mb-1">{{ \App\Support\Money::percent($kpis['conversion_rate']) }}</h4>
                    <span class="font-size-12 text-muted">{{ $kpis['leads_won'] }} won / {{ $kpis['leads_total'] }} leads</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Weekly Sales Trend</h5>
                    <div id="cds-weekly-sales" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Lead Sources</h5>
                    <div id="cds-lead-sources" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recent Sales Entries</h5>
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Sale</th><th>Salesperson</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ url('/sales/'.$sale->id) }}" class="text-body fw-semibold">{{ $sale->sale_no }}</a>
                                            <div class="text-muted font-size-12">{{ $sale->sale_date->format('d M Y') }}</div>
                                        </td>
                                        <td>{{ $sale->salesperson?->name }}</td>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td class="fw-semibold">{{ $money($sale->total) }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($sale->payment_status) }}</span></td>
                                        <td>@include('partials.sale-status', ['status' => $sale->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No sales recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Top Products</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Product</th><th>Units</th><th>Revenue</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($breakdowns['top_products'] as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ number_format((float) $product->qty) }}</td>
                                        <td class="fw-semibold">{{ $money($product->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No product sales yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <h6 class="mb-2">Catalogue Highlights</h6>
                    @foreach ($topProducts as $product)
                        <div class="d-flex align-items-center py-1">
                            <img src="{{ asset($product->image ?? 'assets/images/product/img-1.png') }}" class="rounded" width="34" height="34" alt="">
                            <div class="ms-2 flex-1">
                                <h6 class="mb-0 font-size-14">{{ \Illuminate\Support\Str::limit($product->name, 34) }}</h6>
                                <span class="text-muted font-size-12">{{ $money($product->price) }} · stock {{ $product->stock }}</span>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">{{ number_format((float) $product->rating, 1) }}★</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var weekly = @json($charts);
            var monthly = @json($monthly);
            var sources = @json($breakdowns['leads_by_source'] ?? []);

            if (document.querySelector('#cds-weekly-sales')) {
                new ApexCharts(document.querySelector('#cds-weekly-sales'), {
                    chart: { type: 'bar', height: 340, toolbar: { show: false } },
                    series: [
                        { name: 'Sales', type: 'column', data: weekly.sales },
                        { name: 'Monthly Trend', type: 'line', data: monthly.totals.slice(-12) },
                    ],
                    xaxis: { categories: weekly.categories, labels: { rotate: -35, style: { fontSize: '11px' } } },
                    colors: ['#1e7df0', '#34c38f'],
                    stroke: { width: [0, 3], curve: 'smooth' },
                    plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' },
                }).render();
            }

            if (document.querySelector('#cds-lead-sources')) {
                var labels = Object.keys(sources);
                var values = labels.map(function (k) { return sources[k]; });
                new ApexCharts(document.querySelector('#cds-lead-sources'), {
                    chart: { type: 'pie', height: 340 },
                    series: values.length ? values : [1],
                    labels: labels.length ? labels.map(function (l) { return l.replace(/_/g, ' '); }) : ['No data'],
                    legend: { position: 'bottom' },
                }).render();
            }
        });
    </script>
@endpush
