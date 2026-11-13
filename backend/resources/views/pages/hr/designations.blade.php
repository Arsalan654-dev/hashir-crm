@extends('layouts.app')

@section('title', 'Designations')
@section('page-title', 'Designations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/employees') }}">Employees</a></li>
    <li class="breadcrumb-item active">Designations</li>
@endsection

@section('page-actions')
    @canany(['hr.view', 'employees.create', 'employees.update'])
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#designation-create-modal"><i class="bx bx-plus me-1"></i> Add Designation</button>
    @endcanany
    <a href="{{ url('/departments') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-buildings me-1"></i> Departments</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Level</th>
                            <th class="text-end">Employees</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($designations as $designation)
                            <tr>
                                <td>
                                    <h6 class="mb-0 font-size-14">{{ $designation->title }}</h6>
                                    @if ($designation->description)
                                        <span class="text-muted font-size-12">{{ $designation->description }}</span>
                                    @endif
                                </td>
                                <td>{{ $designation->department?->name ?? '—' }}</td>
                                <td><span class="text-muted">{{ $designation->level ? ucfirst($designation->level) : '—' }}</span></td>
                                <td class="text-end">{{ number_format($designation->users_count) }}</td>
                                <td>
                                    @if ($designation->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @canany(['hr.view', 'employees.create', 'employees.update'])
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#designation-edit-{{ $designation->id }}" title="Edit"><i class="bx bx-edit"></i></button>
                                        <form action="{{ url('/designations/'.$designation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this designation?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcanany
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No designations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @canany(['hr.view', 'employees.create', 'employees.update'])
        <div class="modal fade" id="designation-create-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ url('/designations') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Designation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Level</label>
                                <input type="text" name="level" class="form-control" placeholder="senior">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Create Designation</button>
                    </div>
                </form>
            </div>
        </div>
    @endcanany

    @foreach ($designations as $designation)
        @canany(['hr.view', 'employees.create', 'employees.update'])
            <div class="modal fade" id="designation-edit-{{ $designation->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ url('/designations/'.$designation->id) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Designation — {{ $designation->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required value="{{ $designation->title }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Level</label>
                                    <input type="text" name="level" class="form-control" value="{{ $designation->level }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Department</label>
                                    <select name="department_id" class="form-select">
                                        <option value="">None</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" @selected($designation->department_id == $department->id)>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" value="{{ $designation->description }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save Designation</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcanany
    @endforeach
@endsection
