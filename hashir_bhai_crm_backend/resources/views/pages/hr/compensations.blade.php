@extends('layouts.app')

@section('title', 'Bonuses & Incentives')
@section('page-title', 'Bonuses & Incentives')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Compensations</li>
@endsection

@section('page-actions')
    @can('compensations.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-compensation-modal"><i class="bx bx-plus me-1"></i> Record Compensation</button>
    @endcan
@endsection

@php
    $typeColors = [
        'bonus' => 'success',
        'incentive' => 'info',
        'appreciation' => 'primary',
        'penalty' => 'danger',
        'advance' => 'warning',
        'deduction' => 'danger',
    ];
@endphp

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-gift"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Bonuses &amp; Appreciation</p>
                        <h4 class="mb-0">{{ \App\Support\Money::compact($totals['bonus']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-trophy"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Incentives</p>
                        <h4 class="mb-0">{{ \App\Support\Money::compact($totals['incentive']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-danger-subtle text-danger"><i class="bx bx-minus-circle"></i></div>
                    <div class="ms-3">
                        <p class="text-muted mb-1 font-size-13">Penalties &amp; Deductions</p>
                        <h4 class="mb-0">{{ \App\Support\Money::compact($totals['penalty']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/compensations') }}">
                <div class="col-md-3">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        @foreach (\App\Models\Compensation::TYPES as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Employee</label>
                    <select name="user_id" class="form-select">
                        <option value="">All employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('user_id') == $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Period Month</label>
                    <input type="month" name="month" value="{{ request('month') }}" class="form-control">
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
                            <th>Title</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                            <th>Period</th>
                            <th>Recorded By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($compensations as $compensation)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $compensation->user?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $compensation->user?->name ?? 'Unknown' }}</h6>
                                            <span class="text-muted font-size-12">{{ $compensation->user?->department?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $typeColors[$compensation->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$compensation->type] ?? 'secondary' }}">{{ ucfirst($compensation->type) }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $compensation->title }}</span>
                                    @if ($compensation->notes)
                                        <div class="text-muted font-size-12">{{ $compensation->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ \App\Support\Money::format($compensation->amount) }}</td>
                                <td>{{ $compensation->date->format('d M Y') }}</td>
                                <td class="text-muted">{{ $compensation->period_month ?? '—' }}</td>
                                <td class="text-muted font-size-13">{{ $compensation->creator?->name ?? '—' }}</td>
                                <td class="text-end cds-table-actions">
                                    @can('compensations.manage')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cds-compensation-edit-{{ $compensation->id }}">Edit</button>
                                        <form action="{{ url('/compensations/'.$compensation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this compensation?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No compensations recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $compensations->links() }}</div>
        </div>
    </div>

    @can('compensations.manage')
        <div class="modal fade" id="cds-compensation-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ url('/compensations') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Record Compensation</h5>
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
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        @foreach (\App\Models\Compensation::TYPES as $type)
                                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required maxlength="255">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" required value="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Period Month</label>
                                    <input type="month" name="period_month" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach ($compensations as $compensation)
            <div class="modal fade" id="cds-compensation-edit-{{ $compensation->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ url('/compensations/'.$compensation->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Compensation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            @foreach (\App\Models\Compensation::TYPES as $type)
                                                <option value="{{ $type }}" @selected($compensation->type === $type)>{{ ucfirst($type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required value="{{ $compensation->amount }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" required maxlength="255" value="{{ $compensation->title }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" required value="{{ $compensation->date->toDateString() }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Period Month</label>
                                        <input type="month" name="period_month" class="form-control" value="{{ $compensation->period_month }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ $compensation->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endcan
@endsection
