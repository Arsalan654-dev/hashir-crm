@extends('layouts.app')

@section('title', 'My Sales')
@section('page-title', 'My Sales')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/sales') }}">Sales</a></li>
    <li class="breadcrumb-item active">My Sales</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('sales.create'))
        <a href="{{ url('/sales/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> New Sale</a>
    @endif
    <a href="{{ url('/sales') }}" class="btn btn-outline-secondary btn-sm">All Sales</a>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Total Completed</p>
                    <h4 class="mb-1">{{ $money($summary['total']) }}</h4>
                    <span class="font-size-12 text-muted">{{ $summary['count'] }} completed sales</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">This Month</p>
                    <h4 class="mb-1">{{ $money($summary['month']) }}</h4>
                    <span class="font-size-12 text-muted">{{ $money($summary['today']) }} today</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Commission Earned</p>
                    <h4 class="mb-1 text-success">{{ $money($summary['commission']) }}</h4>
                    <span class="font-size-12 text-muted">Calculated on completed sales</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0">
                <div class="card-body">
                    <p class="text-muted mb-1 font-size-13">Awaiting Approval</p>
                    <h4 class="mb-1 text-warning">{{ number_format($summary['pending']) }}</h4>
                    <span class="font-size-12 text-muted">Pending sale entries</span>
                </div>
            </div>
        </div>
    </div>

    @if ($targets->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">My Targets</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($targets as $target)
                        @php($progress = $target->progressPercent())
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-1">{{ $target->title ?? ucfirst($target->period_type).' Target' }}</h6>
                                <span class="text-muted font-size-12">{{ $target->period_start->format('d M Y') }} &rarr; {{ $target->period_end->format('d M Y') }}</span>
                                <div class="d-flex justify-content-between mt-2 font-size-13">
                                    <span>{{ $money($target->achievedAmount()) }}</span>
                                    <span class="text-muted">of {{ $money($target->target_amount) }}</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : 'primary' }}" style="width: {{ $progress }}%;"></div>
                                </div>
                                <div class="text-muted font-size-12 mt-1">{{ \App\Support\Money::percent($progress) }} achieved</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sale</th>
                            <th>Customer</th>
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
                                    <div class="text-muted font-size-12">{{ $sale->sale_date->format('d M Y') }} &middot; {{ $sale->title ?? 'No title' }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $sale->customer_name }}</div>
                                    <span class="text-muted font-size-12">{{ $sale->customer_phone ?? 'No phone' }}</span>
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
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">You have not recorded any sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $sales->links() }}</div>
        </div>
    </div>
@endsection
