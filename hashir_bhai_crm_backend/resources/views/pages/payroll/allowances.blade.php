@extends('layouts.app')

@section('title', 'Salary Allowances')
@section('page-title', 'Salary Allowances & Deductions')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/payroll') }}">Payroll</a></li>
    <li class="breadcrumb-item active">Allowances</li>
@endsection

@section('page-actions')
    @can('allowances.manage')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#allowance-create-modal"><i class="bx bx-plus me-1"></i> Add Allowance</button>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-4 col-md-4">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Monthly Allowances</span>
                    <h4 class="mb-0">{{ number_format($summary['allowances'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Monthly Deductions</span>
                    <h4 class="mb-0">{{ number_format($summary['deductions'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Employees with Components</span>
                    <h4 class="mb-0">{{ number_format($summary['employees']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('allowances.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-4">
                    <select name="user_id" class="form-select">
                        <option value="">All employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(($filters['user_id'] ?? null) == $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
                <div class="col-lg-2">
                    <a href="{{ route('allowances.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Component</th>
                            <th>Type</th>
                            <th>Recurrence</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allowances as $allowance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $allowance->user?->avatarUrl() }}" alt="" class="rounded-circle avatar-xs me-2">
                                        <div>
                                            <h6 class="mb-0 font-size-14">{{ $allowance->user?->name ?? '—' }}</h6>
                                            <span class="text-muted font-size-12">{{ $allowance->user?->employee_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $allowance->label }}</span>
                                    @if ($allowance->notes)
                                        <span class="d-block text-muted font-size-12">{{ $allowance->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $allowance->type === 'allowance' ? 'success' : 'danger' }}-subtle text-{{ $allowance->type === 'allowance' ? 'success' : 'danger' }}">
                                        {{ ucfirst($allowance->type) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $allowance->recurrence)) }}</td>
                                <td class="text-end fw-medium">{{ number_format((float) $allowance->amount, 2) }}</td>
                                <td>
                                    @if ($allowance->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @can('allowances.manage')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#allowance-edit-{{ $allowance->id }}"><i class="bx bx-edit"></i></button>
                                        <form action="{{ route('allowances.destroy', $allowance) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this component?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No allowances or deductions added yet. They feed into payroll automatically.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $allowances->links() }}
            </div>
        </div>
    </div>

    @can('allowances.manage')
        <div class="modal fade" id="allowance-create-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('allowances.store') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Allowance / Deduction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">— Select employee —</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" placeholder="Fuel Allowance" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    @foreach ($types as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Recurrence <span class="text-danger">*</span></label>
                                <select name="recurrence" class="form-select" required>
                                    @foreach ($recurrences as $recurrence)
                                        <option value="{{ $recurrence }}">{{ ucfirst(str_replace('_', ' ', $recurrence)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="allowance-create-active" checked>
                                    <label class="form-check-label" for="allowance-create-active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Add Component</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @foreach ($allowances as $allowance)
        @can('allowances.manage')
            <div class="modal fade" id="allowance-edit-{{ $allowance->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('allowances.update', $allowance) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit — {{ $allowance->label }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                                    <select name="user_id" class="form-select" required>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}" @selected($allowance->user_id === $employee->id)>{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Label <span class="text-danger">*</span></label>
                                    <input type="text" name="label" class="form-control" value="{{ $allowance->label }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        @foreach ($types as $value => $label)
                                            <option value="{{ $value }}" @selected($allowance->type === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ $allowance->amount }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Recurrence <span class="text-danger">*</span></label>
                                    <select name="recurrence" class="form-select" required>
                                        @foreach ($recurrences as $recurrence)
                                            <option value="{{ $recurrence }}" @selected($allowance->recurrence === $recurrence)>{{ ucfirst(str_replace('_', ' ', $recurrence)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" value="{{ $allowance->notes }}">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="allowance-edit-active-{{ $allowance->id }}" @checked($allowance->is_active)>
                                        <label class="form-check-label" for="allowance-edit-active-{{ $allowance->id }}">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
@endsection
