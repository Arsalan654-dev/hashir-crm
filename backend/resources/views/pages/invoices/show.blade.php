@extends('layouts.app')

@section('title', $invoice->invoice_no)
@section('page-title', 'Invoice '.$invoice->invoice_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/invoices') }}">Invoices</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_no }}</li>
@endsection

@section('page-actions')
    <a href="{{ url('/invoices') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Back to Invoices</a>
    <a href="{{ url('/invoices/'.$invoice->id.'/pdf') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-download me-1"></i> Download PDF</a>
    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bx bx-printer me-1"></i> Print</button>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))
@php($subtotal = (float) $invoice->items->sum(fn ($item) => (float) $item->price * (float) $item->quantity))

@push('styles')
    <style>
        @media print {
            #page-topbar, .vertical-menu, .footer, .page-title-box { display: none !important; }
            .main-content { margin-left: 0 !important; }
            .page-content { padding: 0 !important; }
            .container-fluid { width: 100% !important; max-width: 100% !important; }
        }
    </style>
@endpush

@section('content')
    <div class="d-print-none">
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="card-title mb-1">{{ $invoice->invoice_no }}</h5>
                    <span class="text-muted font-size-13">Issued {{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</span>
                </div>
                <span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }} font-size-12">{{ ucfirst($invoice->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-3">
                        <p class="text-muted mb-1 font-size-13">Invoice Date</p>
                        <h6 class="mb-0">{{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</h6>
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted mb-1 font-size-13">Due Date</p>
                        <h6 class="mb-0 {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->outstanding() > 0 ? 'text-danger' : '' }}">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</h6>
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted mb-1 font-size-13">Amount</p>
                        <h6 class="mb-0">{{ $money($invoice->amount) }}</h6>
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted mb-1 font-size-13">Balance Due</p>
                        <h6 class="mb-0 {{ $invoice->outstanding() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($invoice->outstanding()) }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Items</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoice->items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $item->name }}</div>
                                                @if ($item->description)
                                                    <div class="text-muted font-size-12">{{ $item->description }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ (float) $item->quantity }}</td>
                                            <td class="text-end">{{ $money($item->price) }}</td>
                                            <td class="text-end fw-semibold">{{ $money((float) $item->price * (float) $item->quantity) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No line items recorded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Totals</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-medium">{{ $money($subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <span class="fw-medium text-danger">-{{ $money($invoice->discount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax</span>
                            <span class="fw-medium">{{ $money($invoice->tax) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span class="fw-medium">{{ $money($invoice->shipping) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Amount</span>
                            <span class="fw-semibold">{{ $money($invoice->amount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Paid</span>
                            <span class="text-success">{{ $money($invoice->paid_amount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Balance Due</span>
                            <span class="{{ $invoice->outstanding() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($invoice->outstanding()) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Currency</span>
                            <span>{{ $invoice->currency ?? \App\Support\Branding::currency() }}</span>
                        </div>
                    </div>
                </div>

                @if ($invoice->description)
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Description</h5></div>
                        <div class="card-body">
                            <p class="mb-0">{{ $invoice->description }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Billed To</h5></div>
                    <div class="card-body">
                        <h6 class="mb-1">{{ $invoice->billing_name }}</h6>
                        <p class="text-muted mb-1 font-size-13"><i class="bx bx-envelope me-1"></i>{{ $invoice->email ?? '—' }}</p>
                        <p class="text-muted mb-1 font-size-13"><i class="bx bx-phone me-1"></i>{{ $invoice->phone ?? '—' }}</p>
                        <p class="text-muted mb-3 font-size-13"><i class="bx bx-map me-1"></i>{{ $invoice->address ?? '—' }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($invoice->customer)
                                <a href="{{ url('/customers/'.$invoice->customer->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-user me-1"></i> View Customer</a>
                            @endif
                            @if ($invoice->sale)
                                <a href="{{ url('/sales/'.$invoice->sale->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-cart me-1"></i> View Sale</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Details</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }}">{{ ucfirst($invoice->status) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Currency</span>
                            <span>{{ $invoice->currency ?? \App\Support\Branding::currency() }}</span>
                        </div>
                        @if ($invoice->sale)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Sale</span>
                                <a href="{{ url('/sales/'.$invoice->sale->id) }}">{{ $invoice->sale->sale_no }}</a>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created By</span>
                            <span>{{ $invoice->creator?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created</span>
                            <span>{{ $invoice->created_at?->format('d M Y H:i') ?? '—' }}</span>
                        </div>
                        @if ($invoice->sent_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Last Sent</span>
                                <span>{{ $invoice->sent_at->format('d M Y H:i') }}{{ $invoice->sent_via ? ' · '.ucfirst($invoice->sent_via) : '' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Actions</h5></div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ url('/invoices/'.$invoice->id.'/pdf') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-download me-1"></i> Download PDF</a>

                        @can('invoices.send')
                            <form action="{{ url('/invoices/'.$invoice->id.'/send') }}" method="POST" class="border rounded p-2 d-grid gap-2">
                                @csrf
                                <label class="form-label mb-0">Send Invoice</label>
                                <select name="via" class="form-select form-select-sm" required>
                                    <option value="whatsapp" @disabled(! $whatsappConfigured)>WhatsApp{{ $whatsappConfigured ? '' : ' (not configured)' }}</option>
                                    <option value="email" @disabled(! $gmailConfigured)>Email{{ $gmailConfigured ? '' : ' (not configured)' }}</option>
                                </select>
                                @unless ($whatsappConfigured)
                                    <p class="text-muted font-size-12 mb-0">WhatsApp Cloud API is not configured.</p>
                                @endunless
                                @unless ($gmailConfigured)
                                    <p class="text-muted font-size-12 mb-0">No active Gmail account is connected.</p>
                                @endunless
                                <textarea name="message" rows="2" class="form-control form-control-sm" placeholder="Optional message">{{ old('message') }}</textarea>
                                <button class="btn btn-success btn-sm" @disabled(! $whatsappConfigured && ! $gmailConfigured)><i class="bx bx-send me-1"></i> Send</button>
                            </form>
                        @endcan

                        @can('invoices.update')
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><i class="bx bx-edit me-1"></i> Record Payment / Edit Invoice</button>
                        @endcan

                        @can('invoices.delete')
                            <form action="{{ url('/invoices/'.$invoice->id) }}" method="POST" onsubmit="return confirm('Delete this invoice permanently?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash me-1"></i> Delete Invoice</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        @can('invoices.update')
            <div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form action="{{ url('/invoices/'.$invoice->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Record Payment / Edit Invoice {{ $invoice->invoice_no }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Billing Name <span class="text-danger">*</span></label>
                                        <input type="text" name="billing_name" class="form-control" required value="{{ old('billing_name', $invoice->billing_name) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $invoice->email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $invoice->phone) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control" value="{{ old('address', $invoice->address) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                        <input type="date" name="invoice_date" class="form-control" required value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            @foreach (['draft', 'pending', 'paid', 'overdue', 'cancelled'] as $status)
                                                <option value="{{ $status }}" @selected(old('status', strtolower((string) $invoice->status)) === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Paid Amount</label>
                                        <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ old('paid_amount', (float) $invoice->paid_amount) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Tax</label>
                                        <input type="number" step="0.01" min="0" name="tax" class="form-control" value="{{ old('tax', (float) $invoice->tax) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Discount</label>
                                        <input type="number" step="0.01" min="0" name="discount" class="form-control" value="{{ old('discount', (float) $invoice->discount) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Shipping</label>
                                        <input type="number" step="0.01" min="0" name="shipping" class="form-control" value="{{ old('shipping', (float) $invoice->shipping) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="2" class="form-control">{{ old('description', $invoice->description) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Terms</label>
                                        <textarea name="terms" rows="2" class="form-control">{{ old('terms', $invoice->terms) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>

    <div class="card">
        <div class="card-header d-print-none d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Invoice Preview</h5>
            <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bx bx-printer me-1"></i> Print</button>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ \App\Support\Branding::name() }}</h4>
                    <p class="text-muted mb-0 font-size-13">{{ \App\Models\Setting::getValue('company.address') }}</p>
                    <p class="text-muted mb-0 font-size-13">{{ trim((string) \App\Models\Setting::getValue('company.email').'  '.(string) \App\Models\Setting::getValue('company.phone')) }}</p>
                </div>
                <div class="text-sm-end">
                    <h5 class="mb-1">Invoice {{ $invoice->invoice_no }}</h5>
                    <p class="text-muted mb-0 font-size-13">Issued {{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</p>
                    <p class="text-muted mb-0 font-size-13">Due {{ $invoice->due_date?->format('d M Y') ?? '—' }}</p>
                    <span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }} mt-1">{{ ucfirst($invoice->status) }}</span>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <h6 class="text-muted text-uppercase font-size-12 mb-2">Billed To</h6>
                    <h6 class="mb-1">{{ $invoice->billing_name }}</h6>
                    <p class="text-muted mb-0 font-size-13">{{ $invoice->email ?? '—' }}</p>
                    <p class="text-muted mb-0 font-size-13">{{ $invoice->phone ?? '—' }}</p>
                    <p class="text-muted mb-0 font-size-13">{{ $invoice->address ?? '—' }}</p>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <h6 class="text-muted text-uppercase font-size-12 mb-2">Invoice Details</h6>
                    <p class="text-muted mb-0 font-size-13">Invoice No: {{ $invoice->invoice_no }}</p>
                    <p class="text-muted mb-0 font-size-13">Invoice Date: {{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</p>
                    <p class="text-muted mb-0 font-size-13">Due Date: {{ $invoice->due_date?->format('d M Y') ?? '—' }}</p>
                    <p class="text-muted mb-0 font-size-13">Status: {{ ucfirst($invoice->status) }}</p>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end" style="width: 130px;">Rate</th>
                            <th class="text-end" style="width: 140px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $item->name }}</div>
                                    @if ($item->description)
                                        <div class="text-muted font-size-12">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ (float) $item->quantity }}</td>
                                <td class="text-end">{{ $money($item->price) }}</td>
                                <td class="text-end">{{ $money((float) $item->price * (float) $item->quantity) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No line items recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-6 col-lg-5">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>{{ $money($subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Discount</span>
                        <span>-{{ $money($invoice->discount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax</span>
                        <span>{{ $money($invoice->tax) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span>{{ $money($invoice->shipping) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Total</span>
                        <span class="fw-semibold">{{ $money($invoice->amount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Paid</span>
                        <span class="text-success">{{ $money($invoice->paid_amount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Balance Due</span>
                        <span class="fw-semibold {{ $invoice->outstanding() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($invoice->outstanding()) }}</span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <p class="text-muted mb-0 font-size-13">{{ $invoice->terms ?: 'Thank you for your business.' }}</p>
        </div>
    </div>
@endsection
