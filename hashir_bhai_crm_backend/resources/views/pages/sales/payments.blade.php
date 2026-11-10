@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Sales Payments')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/sales') }}">Sales</a></li>
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('page-actions')
    <a href="{{ url('/sales') }}" class="btn btn-outline-secondary btn-sm">All Sales</a>
    @if (auth()->user()->can('reports.view'))
        <a href="{{ url('/sales/reports') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-bar-chart-alt-2 me-1"></i> Reports</a>
    @endif
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ \App\Support\Money::compact($totalReceived) }}</h3>
                <p class="text-muted mb-0 font-size-13">Total Received</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ \App\Support\Money::compact($receivedThisMonth) }}</h3>
                <p class="text-muted mb-0 font-size-13">Received This Month</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ \App\Support\Money::compact($outstanding) }}</h3>
                <p class="text-muted mb-0 font-size-13">Outstanding Balance</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Sale</th>
                            <th>Customer</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Received By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_on?->format('d M Y') }}</td>
                                <td>
                                    @if ($payment->sale)
                                        <a href="{{ url('/sales/'.$payment->sale->id) }}" class="text-body fw-semibold">{{ $payment->sale->sale_no }}</a>
                                        <div class="text-muted font-size-12">Sale total {{ \App\Support\Money::format($payment->sale->total, null) }} {{ $payment->sale->currency }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $payment->sale?->customer_name ?? '—' }}</td>
                                <td class="text-end fw-semibold text-success">{{ $money($payment->amount) }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', $payment->method)) }}</span></td>
                                <td class="text-muted">{{ $payment->reference ?? '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $payment->receiver?->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                        <span class="ms-2 font-size-13">{{ $payment->receiver?->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-muted font-size-13">{{ $payment->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $payments->links() }}</div>
        </div>
    </div>
@endsection
