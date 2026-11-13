@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Invoices</li>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-receipt"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Billed ({{ number_format($stats['count']) }} invoices)</p>
                            <h4 class="mb-0">{{ $money($stats['billed']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-check-circle"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Collected</p>
                            <h4 class="mb-0">{{ $money($stats['collected']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-time-five"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Outstanding</p>
                            <h4 class="mb-0">{{ $money($stats['outstanding']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-danger-subtle text-danger"><i class="bx bx-error-circle"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Overdue</p>
                            <h4 class="mb-0">{{ number_format($stats['overdue']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/invoices') }}">
                <div class="col-md-5">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Invoice no or billing name">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ url('/invoices/'.$invoice->id) }}" class="text-body fw-medium">{{ $invoice->invoice_no }}</a>
                                    @if ($invoice->sale)
                                        <div class="text-muted font-size-12">Sale {{ $invoice->sale->sale_no }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-size-13">{{ $invoice->customer?->name ?? '—' }}</div>
                                    <div class="text-muted font-size-12">{{ $invoice->billing_name }}</div>
                                </td>
                                <td><span class="text-muted font-size-13">{{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</span></td>
                                <td>
                                    <span class="font-size-13 {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->outstanding() > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ $money($invoice->amount) }}</td>
                                <td class="text-end">{{ $money($invoice->paid_amount) }}</td>
                                <td class="text-end fw-semibold {{ $invoice->outstanding() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($invoice->outstanding()) }}</td>
                                <td><span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }}">{{ ucfirst($invoice->status) }}</span></td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/invoices/'.$invoice->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-show"></i></a>
                                    <a href="{{ url('/invoices/'.$invoice->id.'/pdf') }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-download"></i></a>
                                    @if (auth()->user()->can('invoices.delete'))
                                        <form action="{{ url('/invoices/'.$invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this invoice?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $invoices->links() }}</div>
        </div>
    </div>
@endsection
