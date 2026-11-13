@extends('layouts.app')

@section('title', $customer->name)
@section('page-title', $customer->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/customers') }}">Customers</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $assignees = \App\Models\User::query()->orderBy('name')->get(['id', 'name']);
    $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'blocked' => 'danger'];
    $statusColor = $statusColors[$customer->status] ?? 'secondary';
    $avatarUrl = filled($customer->avatar)
        ? (str_starts_with((string) $customer->avatar, 'http') || str_starts_with((string) $customer->avatar, 'assets/')
            ? asset($customer->avatar)
            : asset('storage/'.$customer->avatar))
        : asset('assets/images/users/avatar-1.jpg');
    $tags = is_array($customer->tags) ? $customer->tags : ($customer->tags ? preg_split('/\s*,\s*/', (string) $customer->tags) : []);
@endphp

@section('page-actions')
    @if (auth()->user()->can('customers.update'))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-customer-edit"><i class="bx bx-edit me-1"></i> Edit</button>
    @endif
    @if (auth()->user()->can('customers.delete'))
        <form action="{{ url('/customers/'.$customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?');">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash me-1"></i> Delete</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <img src="{{ $avatarUrl }}" class="rounded-circle avatar-lg" alt="">
                <div class="ms-3 flex-1">
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-1">
                        <i class="bx bx-buildings me-1"></i>{{ $customer->company ?? 'Individual' }}
                        @if ($customer->email) <span class="mx-2">·</span><i class="bx bx-envelope me-1"></i>{{ $customer->email }}@endif
                        @if ($customer->phone) <span class="mx-2">·</span><i class="bx bx-phone me-1"></i>{{ $customer->phone }}@endif
                    </p>
                    <p class="text-muted mb-0 font-size-13">
                        @if ($customer->whatsapp_number)
                            <i class="bx bxl-whatsapp me-1"></i>{{ $customer->whatsapp_number }}
                            <span class="mx-2">·</span>
                        @endif
                        <i class="bx bx-map me-1"></i>{{ collect([$customer->address, $customer->city, $customer->country])->filter()->implode(', ') ?: 'No address' }}
                        <span class="mx-2">·</span>
                        <i class="bx bx-calendar me-1"></i>Joined {{ $customer->joining_date?->format('d M Y') ?? '—' }}
                        @if ($customer->assignee)
                            <span class="mx-2">·</span>
                            <i class="bx bx-user me-1"></i>{{ $customer->assignee->name }}
                        @endif
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} font-size-13">{{ ucfirst($customer->status ?? 'active') }}</span>
                    @foreach ($tags as $tag)
                        <span class="badge bg-light text-dark">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @if (filled($customer->notes))
                <hr>
                <p class="text-muted mb-0 font-size-13">{{ $customer->notes }}</p>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-dollar-circle"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Lifetime Value</p>
                            <h4 class="mb-0">{{ $money($totals['lifetime']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-cart"></i></div>
                        <div class="ms-3 flex-1">
                            <p class="text-muted mb-1 font-size-13">Orders</p>
                            <h4 class="mb-0">{{ number_format($totals['orders']) }}</h4>
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
                            <p class="text-muted mb-1 font-size-13">Outstanding</p>
                            <h4 class="mb-0">{{ $money($totals['outstanding']) }}</h4>
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
                            <p class="text-muted mb-1 font-size-13">Open Leads</p>
                            <h4 class="mb-0">{{ number_format($totals['openLeads']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="card mb-0 h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Sales History</h5>
                    <span class="text-muted font-size-13">Latest {{ $customer->sales->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Sale</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($customer->sales as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ url('/sales/'.$sale->id) }}" class="text-body fw-medium">{{ $sale->sale_no }}</a>
                                            <div class="text-muted font-size-12">{{ $sale->title ?? '—' }}</div>
                                        </td>
                                        <td><span class="text-muted font-size-13">{{ $sale->sale_date?->format('d M Y') ?? '—' }}</span></td>
                                        <td class="text-end fw-semibold">{{ $money($sale->total) }}</td>
                                        <td>@include('partials.sale-status', ['status' => $sale->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No sales recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card mb-0 h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Leads</h5>
                    <span class="text-muted font-size-13">{{ $customer->leads->count() }} total</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Lead</th><th>Value</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($customer->leads as $lead)
                                    <tr>
                                        <td>
                                            <a href="{{ url('/leads/'.$lead->id) }}" class="text-body fw-medium">{{ $lead->name }}</a>
                                            <div class="text-muted font-size-12">{{ str_replace('_', ' ', ucfirst($lead->source)) }}</div>
                                        </td>
                                        <td class="fw-semibold">{{ $money($lead->estimated_value) }}</td>
                                        <td>@include('partials.lead-status', ['status' => $lead->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No leads yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Invoices</h5>
            <span class="text-muted font-size-13">{{ $customer->invoices->count() }} total</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
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
                        @forelse ($customer->invoices as $invoice)
                            <tr>
                                <td><a href="{{ url('/invoices/'.$invoice->id) }}" class="text-body fw-medium">{{ $invoice->invoice_no }}</a></td>
                                <td><span class="text-muted font-size-13">{{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</span></td>
                                <td><span class="text-muted font-size-13">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span></td>
                                <td class="text-end fw-semibold">{{ $money($invoice->amount) }}</td>
                                <td class="text-end">{{ $money($invoice->paid_amount) }}</td>
                                <td class="text-end fw-semibold {{ $invoice->outstanding() > 0 ? 'text-danger' : 'text-success' }}">{{ $money($invoice->outstanding()) }}</td>
                                <td><span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }}">{{ ucfirst($invoice->status) }}</span></td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/invoices/'.$invoice->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-show"></i></a>
                                    <a href="{{ url('/invoices/'.$invoice->id.'/pdf') }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-download"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No invoices raised.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (auth()->user()->can('customers.update'))
        <div class="modal fade" id="cds-customer-edit" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ url('/customers/'.$customer->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="{{ old('name', $customer->name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="company" class="form-control" value="{{ old('company', $customer->company) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">WhatsApp</label>
                                    <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $customer->whatsapp_number) }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" @selected(old('status', $customer->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Inactive</option>
                                        <option value="blocked" @selected(old('status', $customer->status) === 'blocked')>Blocked</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Assigned To</label>
                                    <select name="assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        @foreach ($assignees as $assignee)
                                            <option value="{{ $assignee->id }}" @selected(old('assigned_to', $customer->assigned_to) == $assignee->id)>{{ $assignee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tags</label>
                                    <input type="text" name="tags" class="form-control" value="{{ old('tags', implode(', ', $tags)) }}" placeholder="vip, retail">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Avatar</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes', $customer->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
