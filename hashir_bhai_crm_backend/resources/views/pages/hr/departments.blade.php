@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/employees') }}">Employees</a></li>
    <li class="breadcrumb-item active">Departments</li>
@endsection

@section('page-actions')
    @canany(['hr.view', 'employees.create', 'employees.update'])
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#department-create-modal"><i class="bx bx-plus me-1"></i> Add Department</button>
    @endcanany
    <a href="{{ url('/designations') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-purchase-tag me-1"></i> Designations</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Code</th>
                            <th>Colour</th>
                            <th class="text-end">Employees</th>
                            <th class="text-end">Designations</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-{{ $department->color ?? 'primary' }} me-2">&nbsp;</span>
                                        <div>
                                            <h6 class="mb-0 font-size-14">{{ $department->name }}</h6>
                                            @if ($department->description)
                                                <span class="text-muted font-size-12">{{ $department->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $department->code ?? '—' }}</span></td>
                                <td><span class="badge bg-{{ $department->color ?? 'secondary' }}-subtle text-{{ $department->color ?? 'secondary' }}">{{ $department->color ?? 'none' }}</span></td>
                                <td class="text-end">{{ number_format($department->users_count) }}</td>
                                <td class="text-end">{{ number_format($department->designations_count) }}</td>
                                <td>
                                    @if ($department->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @canany(['hr.view', 'employees.create', 'employees.update'])
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#department-edit-{{ $department->id }}" title="Edit"><i class="bx bx-edit"></i></button>
                                        <form action="{{ url('/departments/'.$department->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcanany
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No departments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @canany(['hr.view', 'employees.create', 'employees.update'])
        <div class="modal fade" id="department-create-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ url('/departments') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" placeholder="HR">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Colour</label>
                                <select name="color" class="form-select">
                                    @foreach (['primary', 'success', 'info', 'warning', 'danger', 'secondary'] as $color)
                                        <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="department-create-active" checked>
                                    <label class="form-check-label" for="department-create-active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Create Department</button>
                    </div>
                </form>
            </div>
        </div>
    @endcanany

    @foreach ($departments as $department)
        @canany(['hr.view', 'employees.create', 'employees.update'])
            <div class="modal fade" id="department-edit-{{ $department->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ url('/departments/'.$department->id) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Department — {{ $department->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="{{ $department->name }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ $department->code }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Colour</label>
                                    <select name="color" class="form-select">
                                        @foreach (['primary', 'success', 'info', 'warning', 'danger', 'secondary'] as $color)
                                            <option value="{{ $color }}" @selected($department->color === $color)>{{ ucfirst($color) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" value="{{ $department->description }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save Department</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcanany
    @endforeach
@endsection
