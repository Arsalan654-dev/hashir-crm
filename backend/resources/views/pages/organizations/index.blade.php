@extends('layouts.app')

@section('title', 'Organizations')
@section('page-title', 'Organizations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Organizations</li>
@endsection

@section('page-actions')
    @can('organizations.create')
        <a href="{{ route('organizations.create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Add Organization</a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-1 d-block">Total Clients</span>
                            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-20"><i class="bx bx-buildings"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-1 d-block">Active</span>
                            <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-success-subtle text-success font-size-20"><i class="bx bx-check-shield"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-1 d-block">Not Paid</span>
                            <h4 class="mb-0">{{ number_format($stats['unpaid']) }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-warning-subtle text-warning font-size-20"><i class="bx bx-error-circle"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-1 d-block">Blocked</span>
                            <h4 class="mb-0">{{ number_format($stats['blocked']) }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger font-size-20"><i class="bx bx-block"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('organizations.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Organization::STATUSES as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="payment_status" class="form-select">
                        <option value="">All payments</option>
                        @foreach (\App\Models\Organization::PAYMENT_STATUSES as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected(($filters['payment_status'] ?? '') === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
                <div class="col-lg-2">
                    <a href="{{ route('organizations.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Organization</th>
                            <th>Owner</th>
                            <th>Business</th>
                            <th class="text-end">Users</th>
                            <th>Plan</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($organizations as $organization)
                            @php
                                $latestPayment = $organization->payments->first();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2">
                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-14">{{ strtoupper(mb_substr($organization->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-size-14"><a href="{{ route('organizations.show', $organization) }}" class="text-body">{{ $organization->name }}</a></h6>
                                            <span class="text-muted font-size-12">{{ $organization->email ?? $organization->phone ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($organization->owner)
                                        <span class="d-block font-size-13">{{ $organization->owner->name }}</span>
                                        <span class="text-muted font-size-12">{{ $organization->owner->email }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">No owner</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info-subtle text-info">{{ $businessTypes[$organization->business_type] ?? $organization->business_type }}</span></td>
                                <td class="text-end">{{ number_format($organization->users_count) }}</td>
                                <td>
                                    <span class="d-block font-size-13">{{ $organization->plan_name ?? '—' }}</span>
                                    <span class="text-muted font-size-12">{{ number_format((float) $organization->plan_amount, 2) }} {{ $organization->currency }} / {{ str_replace('_', ' ', $organization->billing_cycle) }}</span>
                                </td>
                                <td>
                                    @if ($organization->isPaid())
                                        <span class="badge bg-success-subtle text-success">Paid</span>
                                        @if ($organization->paid_until)
                                            <span class="d-block text-muted font-size-12">until {{ $organization->paid_until->format('d M Y') }}</span>
                                        @endif
                                    @elseif ($organization->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning">Partial</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Not Paid</span>
                                    @endif
                                    @if ($latestPayment && $latestPayment->status === 'pending')
                                        <span class="badge bg-warning text-dark d-block mt-1">Payment review pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($organization->status !== 'active')
                                        <span class="badge bg-danger-subtle text-danger">{{ ucfirst($organization->status) }}</span>
                                    @elseif ($organization->crm_blocked)
                                        <span class="badge bg-danger-subtle text-danger">CRM Blocked</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @endif
                                    @if ($organization->bot_blocked)
                                        <span class="badge bg-secondary-subtle text-secondary d-block mt-1">Bot Blocked</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ route('organizations.show', $organization) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bx bx-show"></i></a>
                                    @can('organizations.update')
                                        <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bx bx-edit"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No organizations found. Click "Add Organization" to onboard your first client.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $organizations->links() }}
            </div>
        </div>
    </div>
@endsection
