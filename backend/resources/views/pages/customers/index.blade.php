@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Customers</li>
@endsection

@php
    $assignees = \App\Models\User::query()->orderBy('name')->get(['id', 'name']);
    $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'blocked' => 'danger'];
    $avatarUrl = fn ($customer) => filled($customer->avatar)
        ? (str_starts_with((string) $customer->avatar, 'http') || str_starts_with((string) $customer->avatar, 'assets/')
            ? asset($customer->avatar)
            : asset('storage/'.$customer->avatar))
        : asset('assets/images/users/avatar-1.jpg');
@endphp

@section('page-actions')
    @if (auth()->user()->can('customers.create'))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-customer-modal"><i class="bx bx-plus me-1"></i> Add Customer</button>
    @endif
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ number_format($stats['total']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-user-voice me-1"></i>Total Customers</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-success">{{ number_format($stats['active']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-check-circle me-1"></i>Active</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-primary">{{ number_format($stats['newThisMonth']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-calendar-plus me-1"></i>New This Month</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-info">{{ number_format($stats['withWhatsapp']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bxl-whatsapp me-1"></i>With WhatsApp</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/customers') }}">
                <div class="col-md-5">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, company, email, phone">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
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
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>WhatsApp</th>
                            <th>Assigned To</th>
                            <th class="text-center">Activity</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            @php($statusColor = $statusColors[$customer->status] ?? 'secondary')
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $avatarUrl($customer) }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <a href="{{ url('/customers/'.$customer->id) }}" class="text-body fw-medium">{{ $customer->name }}</a>
                                            <div class="text-muted font-size-12">{{ $customer->company ?? 'Individual' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-size-13">{{ $customer->email ?? '—' }}</div>
                                    <div class="text-muted font-size-12">{{ $customer->phone ?? '—' }}</div>
                                </td>
                                <td>
                                    @if (filled($customer->whatsapp_number))
                                        <span class="text-success font-size-13"><i class="bx bxl-whatsapp me-1"></i>{{ $customer->whatsapp_number }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($customer->assignee)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $customer->assignee->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                            <span class="ms-2 font-size-13">{{ $customer->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary" title="Sales">{{ $customer->sales_count }} sales</span>
                                    <span class="badge bg-info-subtle text-info" title="Invoices">{{ $customer->invoices_count }} invoices</span>
                                    <span class="badge bg-warning-subtle text-warning" title="Leads">{{ $customer->leads_count }} leads</span>
                                </td>
                                <td><span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($customer->status ?? 'active') }}</span></td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/customers/'.$customer->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-show"></i></a>
                                    @if (auth()->user()->can('customers.update'))
                                        <a href="{{ url('/customers/'.$customer->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i></a>
                                    @endif
                                    @if (auth()->user()->can('customers.delete'))
                                        <form action="{{ url('/customers/'.$customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $customers->links() }}</div>
        </div>
    </div>

    @if (auth()->user()->can('customers.create'))
        <div class="modal fade" id="cds-customer-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ url('/customers') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="company" class="form-control" value="{{ old('company') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">WhatsApp</label>
                                    <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                        <option value="blocked" @selected(old('status') === 'blocked')>Blocked</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Assigned To</label>
                                    <select name="assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        @foreach ($assignees as $assignee)
                                            <option value="{{ $assignee->id }}" @selected(old('assigned_to') == $assignee->id)>{{ $assignee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tags</label>
                                    <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="vip, retail">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Avatar</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Create Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
