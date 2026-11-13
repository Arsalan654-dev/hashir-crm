@extends('layouts.app')

@section('title', 'Sales')
@section('page-title', 'Sales Records')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('sales.create'))
        <a href="{{ url('/sales/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> New Sale</a>
    @endif
    <a href="{{ url('/sales/export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-download me-1"></i> Export</a>
    @if (auth()->user()->can('reports.view'))
        <a href="{{ url('/sales/reports') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-bar-chart-alt-2 me-1"></i> Reports</a>
    @endif
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1">{{ number_format($totals['count']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Matching Sales</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ \App\Support\Money::compact($totals['total']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Total Value</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ \App\Support\Money::compact($totals['paid']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Received</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-warning">{{ \App\Support\Money::compact($totals['commission']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Commission</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ url('/sales') }}" class="btn btn-sm btn-outline-secondary">All {{ $totals['count'] }}</a>
                <a href="{{ url('/sales') }}?status=pending" class="btn btn-sm btn-outline-warning">Pending {{ $totals['pending'] }}</a>
                <a href="{{ url('/sales') }}?status=confirmed" class="btn btn-sm btn-outline-info">Confirmed {{ $totals['confirmed'] }}</a>
                <a href="{{ url('/sales') }}?status=done" class="btn btn-sm btn-outline-success">Done {{ $totals['done'] }}</a>
            </div>

            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/sales') }}">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Sale no, customer, phone, title">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Sale::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Payment</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All payments</option>
                        @foreach (\App\Models\Sale::PAYMENT_STATUSES as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected(request('payment_status') === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Salesperson</label>
                    <select name="salesperson" class="form-select">
                        <option value="">All</option>
                        @foreach ($salespeople as $salesperson)
                            <option value="{{ $salesperson->id }}" @selected(request('salesperson') == $salesperson->id)>{{ $salesperson->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sale</th>
                            <th>Customer</th>
                            <th>Salesperson</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Balance</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ url('/sales/'.$sale->id) }}" class="text-body fw-semibold">{{ $sale->sale_no }}</a>
                                    <div class="text-muted font-size-12">{{ $sale->sale_date->format('d M Y') }} &middot; {{ ucfirst($sale->source) }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $sale->customer_name }}</div>
                                    <span class="text-muted font-size-12">{{ $sale->customer_phone ?? 'No phone' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $sale->salesperson?->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                        <span class="ms-2 font-size-13">{{ $sale->salesperson?->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">{{ $sale->items->count() }}</td>
                                <td class="text-end fw-semibold">{{ $money($sale->total) }}</td>
                                <td class="text-end {{ (float) $sale->total - (float) $sale->paid_amount > 0 ? 'text-danger' : 'text-success' }}">{{ $money($sale->balance()) }}</td>
                                <td>
                                    @php($paymentColor = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$sale->payment_status] ?? 'secondary')
                                    <span class="badge bg-{{ $paymentColor }}-subtle text-{{ $paymentColor }}">{{ ucfirst($sale->payment_status) }}</span>
                                </td>
                                <td>@include('partials.sale-status', ['status' => $sale->status])</td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/sales/'.$sale->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bx bx-show"></i></a>
                                    @if (auth()->user()->can('sales.update'))
                                        <a href="{{ url('/sales/'.$sale->id.'/edit') }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                                    @endif
                                    @if (auth()->user()->can('sales.delete'))
                                        <form action="{{ url('/sales/'.$sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this sale permanently?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No sales found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $sales->links() }}</div>
        </div>
    </div>
@endsection
