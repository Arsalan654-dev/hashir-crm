@extends('layouts.app')

@section('title', 'My Leaves')
@section('page-title', 'My Leaves')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/leaves/my') }}">Leaves</a></li>
    <li class="breadcrumb-item active">Mine</li>
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
            <div class="card mb-0"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-sun"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Annual Leave Remaining</p>
                        <h4 class="mb-0">{{ number_format($balances['annual'], 1) }} days</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-plus-medical"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Sick Leave Remaining</p>
                        <h4 class="mb-0">{{ number_format($balances['sick'], 1) }} days</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-calendar-check"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Leave Days Used</p>
                        <h4 class="mb-0">{{ number_format($balances['used'], 1) }} days</h4>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    @can('leaves.request', 'leaves.approve')
        <div class="card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Request Leave</h5></div>
            <div class="card-body">
                <form action="{{ url('/leaves') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach (\App\Models\LeaveRequest::TYPES as $type)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required value="{{ old('end_date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100"><i class="bx bx-send me-1"></i> Submit Request</button>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="1000">{{ old('reason') }}</textarea>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Leave History</h5>
            <span class="text-muted font-size-13">{{ $leaves->total() }} requests</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Applied</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaves as $leave)
                            <tr>
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
                                    @if ($leave->admin_note)
                                        <div class="text-muted font-size-12"><i class="bx bx-comment"></i> {{ $leave->admin_note }}</div>
                                    @endif
                                </td>
                                <td class="text-muted font-size-13">{{ $leave->reason ?? '—' }}</td>
                                <td class="text-muted font-size-13">{{ $leave->created_at->format('d M Y') }}</td>
                                <td class="text-end cds-table-actions">
                                    @if (in_array($leave->status, ['pending', 'rejected'], true))
                                        <form action="{{ url('/leaves/'.$leave->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this leave request?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-x me-1"></i> Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">You have not requested any leave yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $leaves->links() }}</div>
        </div>
    </div>
@endsection
