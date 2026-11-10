@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Leaves</li>
@endsection

@section('page-actions')
    @can('leaves.approve')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-leave-modal"><i class="bx bx-plus me-1"></i> Request on Behalf</button>
    @endcan
    <a href="{{ url('/leaves/my') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-calendar-x me-1"></i> My Leaves</a>
@endsection

@php
    $statusColors = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'secondary',
    ];
    $typeColors = [
        'annual' => 'primary',
        'sick' => 'info',
        'casual' => 'secondary',
        'unpaid' => 'warning',
        'maternity' => 'success',
        'emergency' => 'danger',
    ];
@endphp

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-warning">{{ number_format($counts['pending']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Pending</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ number_format($counts['approved']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Approved</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ number_format($counts['rejected']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Rejected</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/leaves') }}">
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\LeaveRequest::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        @foreach (\App\Models\LeaveRequest::TYPES as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaves as $leave)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $leave->user?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $leave->user?->name ?? 'Unknown' }}</h6>
                                            <span class="text-muted font-size-12">{{ $leave->user?->department?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $typeColors[$leave->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$leave->type] ?? 'secondary' }}">{{ ucfirst($leave->type) }}</span>
                                </td>
                                <td>
                                    {{ $leave->start_date->format('d M Y') }}
                                    @if (! $leave->start_date->isSameDay($leave->end_date))
                                        <i class="bx bx-right-arrow-alt"></i> {{ $leave->end_date->format('d M Y') }}
                                    @endif
                                </td>
                                <td>{{ number_format((float) $leave->days, 1) }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$leave->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$leave->status] ?? 'secondary' }}">{{ ucfirst($leave->status) }}</span>
                                    @if ($leave->approver)
                                        <div class="text-muted font-size-12">by {{ $leave->approver->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($leave->reason)
                                        <span class="text-muted font-size-13">{{ $leave->reason }}</span>
                                    @endif
                                    @if ($leave->admin_note)
                                        <div class="text-muted font-size-12"><i class="bx bx-comment"></i> {{ $leave->admin_note }}</div>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @can('leaves.approve')
                                        @if ($leave->status === 'pending')
                                            <form action="{{ url('/leaves/'.$leave->id.'/approve') }}" method="POST" class="d-inline-flex align-items-center gap-1">
                                                @csrf
                                                <input type="text" name="admin_note" class="form-control form-control-sm" style="width: 140px;" placeholder="Note (optional)">
                                                <button class="btn btn-sm btn-success">Approve</button>
                                                <button class="btn btn-sm btn-outline-danger" formaction="{{ url('/leaves/'.$leave->id.'/reject') }}">Reject</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No leave requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $leaves->links() }}</div>
        </div>
    </div>

    @can('leaves.approve')
        <div class="modal fade" id="cds-leave-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ url('/leaves') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Request Leave on Behalf</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    @foreach (\App\Models\LeaveRequest::TYPES as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" class="form-control" rows="3" maxlength="1000"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-send me-1"></i> Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
