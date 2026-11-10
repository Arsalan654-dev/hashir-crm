@extends('layouts.app')

@section('title', $sale->sale_no)
@section('page-title', 'Sale '.$sale->sale_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/sales') }}">Sales</a></li>
    <li class="breadcrumb-item active">{{ $sale->sale_no }}</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('sales.update'))
        <a href="{{ url('/sales/'.$sale->id.'/edit') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-edit me-1"></i> Edit</a>
    @endif
    @if (auth()->user()->can('sales.confirm') && $sale->status === 'pending')
        <form action="{{ url('/sales/'.$sale->id.'/confirm') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-success btn-sm"><i class="bx bx-check me-1"></i> Confirm</button>
        </form>
    @endif
    @if (auth()->user()->can('sales.mark_done') && ! in_array($sale->status, ['done', 'cancelled'], true))
        <form action="{{ url('/sales/'.$sale->id.'/done') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-info btn-sm"><i class="bx bx-flag me-1"></i> Mark Done</button>
        </form>
    @endif
    @if (auth()->user()->can('sales.confirm', 'sales.mark_done') && ! in_array($sale->status, ['rejected', 'cancelled'], true))
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectSaleModal"><i class="bx bx-x me-1"></i> Reject</button>
    @endif
    @if (auth()->user()->can('invoices.create') && ! $sale->invoice_id)
        <form action="{{ url('/sales/'.$sale->id.'/invoice') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-secondary btn-sm"><i class="bx bx-receipt me-1"></i> Generate Invoice</button>
        </form>
    @endif
    @if (auth()->user()->can('sales.delete'))
        <form action="{{ url('/sales/'.$sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this sale permanently?');">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash"></i></button>
        </form>
    @endif
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0"><div class="card-body text-center">
                <p class="text-muted mb-1 font-size-13">Total</p>
                <h4 class="mb-0">{{ $money($sale->total) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0"><div class="card-body text-center">
                <p class="text-muted mb-1 font-size-13">Received</p>
                <h4 class="mb-0 text-success">{{ $money($sale->paid_amount) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0"><div class="card-body text-center">
                <p class="text-muted mb-1 font-size-13">Balance</p>
                <h4 class="mb-0 {{ $sale->balance() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($sale->balance()) }}</h4>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0"><div class="card-body text-center">
                <p class="text-muted mb-1 font-size-13">Commission</p>
                <h4 class="mb-0 text-warning">{{ $money($sale->commission_amount) }}</h4>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ $sale->title ?? 'Sale '.$sale->sale_no }}</h5>
                        <span class="text-muted font-size-13">{{ $sale->customer_name }} &middot; {{ $sale->sale_date->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @include('partials.sale-status', ['status' => $sale->status])
                        @php($paymentColor = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$sale->payment_status] ?? 'secondary')
                        <span class="badge bg-{{ $paymentColor }}-subtle text-{{ $paymentColor }}">{{ ucfirst($sale->payment_status) }}</span>
                    </div>
                </div>
                @if ($sale->description)
                    <div class="card-body border-bottom py-2">
                        <p class="mb-0 text-muted">{{ $sale->description }}</p>
                    </div>
                @endif
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Cost</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sale->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $item->name }}</div>
                                            @if ($item->product)
                                                <a href="{{ url('/products/'.$item->product->id) }}" class="text-muted font-size-12">{{ $item->product->sku ?? 'View product' }}</a>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ (float) $item->quantity }}</td>
                                        <td class="text-end">{{ $money($item->unit_price) }}</td>
                                        <td class="text-end text-muted">{{ $money($item->cost) }}</td>
                                        <td class="text-end text-danger">{{ $money($item->discount) }}</td>
                                        <td class="text-end fw-semibold">{{ $money($item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No line items recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Payments</h5>
                    <span class="text-muted font-size-12">{{ $sale->payments->count() }} record(s)</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Received By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sale->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->paid_on?->format('d M Y') }}</td>
                                        <td class="text-end fw-semibold text-success">{{ $money($payment->amount) }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', $payment->method)) }}</span></td>
                                        <td class="text-muted">{{ $payment->reference ?? '—' }}</td>
                                        <td>{{ $payment->receiver?->name ?? '—' }}</td>
                                        <td class="text-muted font-size-13">{{ $payment->notes ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (auth()->user()->can('sales.payments', 'sales.update') && $sale->status !== 'cancelled')
                        <hr>
                        <h6 class="mb-3">Record Payment</h6>
                        <form action="{{ url('/sales/'.$sale->id.'/payments') }}" method="POST" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-2">
                                <label class="form-label mb-1">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required value="{{ old('amount', $sale->balance() > 0 ? $sale->balance() : '') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Method <span class="text-danger">*</span></label>
                                <select name="method" class="form-select" required>
                                    @foreach (\App\Models\SalePayment::METHODS as $method)
                                        <option value="{{ $method }}" @selected(old('method') === $method)>{{ ucwords(str_replace('_', ' ', $method)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Paid On <span class="text-danger">*</span></label>
                                <input type="date" name="paid_on" class="form-control" required value="{{ old('paid_on', now()->toDateString()) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Reference</label>
                                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary w-100"><i class="bx bx-plus"></i></button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Notes</h5></div>
                <div class="card-body">
                    @if ($sale->notes)
                        <p class="mb-0">{{ $sale->notes }}</p>
                    @else
                        <p class="text-muted mb-0">No notes recorded.</p>
                    @endif
                    @if ($sale->admin_note)
                        <hr>
                        <h6 class="mb-1 text-danger">Admin Note</h6>
                        <p class="mb-0">{{ $sale->admin_note }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Summary</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-medium">{{ $money($sale->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Discount</span>
                        <span class="fw-medium text-danger">-{{ $money($sale->discount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax</span>
                        <span class="fw-medium">{{ $money($sale->tax) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Total</span>
                        <span class="fw-semibold">{{ $money($sale->total) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Paid</span>
                        <span class="text-success">{{ $money($sale->paid_amount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Balance</span>
                        <span class="{{ $sale->balance() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($sale->balance()) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cost</span>
                        <span>{{ $money($sale->cost) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Profit</span>
                        <span class="fw-semibold {{ $sale->profit() >= 0 ? 'text-success' : 'text-danger' }}">{{ $money($sale->profit()) }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Customer</h5></div>
                <div class="card-body">
                    <h6 class="mb-1">{{ $sale->customer_name }}</h6>
                    <p class="text-muted mb-1 font-size-13">{{ $sale->customer_phone ?? 'No phone' }}</p>
                    <p class="text-muted mb-2 font-size-13">{{ $sale->customer_email ?? 'No email' }}</p>
                    @if ($sale->customer)
                        <a href="{{ url('/customers/'.$sale->customer->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-user me-1"></i> View Customer</a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Details</h5></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $sale->salesperson?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                        <div class="ms-2">
                            <h6 class="mb-0">{{ $sale->salesperson?->name ?? 'Unassigned' }}</h6>
                            <span class="text-muted font-size-12">Salesperson</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Source</span>
                        <span>{{ ucwords(str_replace('_', ' ', $sale->source)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Currency</span>
                        <span>{{ $sale->currency }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created</span>
                        <span>{{ $sale->created_at?->format('d M Y H:i') }}</span>
                    </div>
                    @if ($sale->confirmed_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Confirmed</span>
                            <span>{{ $sale->confirmed_at->format('d M Y') }}{{ $sale->confirmedBy ? ' · '.$sale->confirmedBy->name : '' }}</span>
                        </div>
                    @endif
                    @if ($sale->done_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Done</span>
                            <span>{{ $sale->done_at->format('d M Y') }}{{ $sale->doneBy ? ' · '.$sale->doneBy->name : '' }}</span>
                        </div>
                    @endif
                    @if ($sale->lead)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Lead</span>
                            <a href="{{ url('/leads/'.$sale->lead->id) }}">{{ $sale->lead->lead_no }}</a>
                        </div>
                    @endif
                    @if ($sale->invoice)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Invoice</span>
                            <a href="{{ url('/invoices/'.$sale->invoice->id) }}">{{ $sale->invoice->invoice_no }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectSaleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ url('/sales/'.$sale->id.'/reject') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Sale {{ $sale->sale_no }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="admin_note" class="form-control" rows="3" required>{{ old('admin_note') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Reject Sale</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
