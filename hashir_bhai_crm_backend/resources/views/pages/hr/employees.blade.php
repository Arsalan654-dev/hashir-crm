@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employee Directory')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Employees</li>
@endsection

@section('page-actions')
    @can('employees.create')
        <a href="{{ url('/employees/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Add Employee</a>
    @endcan
    @can('hr.view')
        <a href="{{ url('/departments') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-buildings me-1"></i> Departments</a>
    @endcan
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary me-3"><i class="bx bx-group"></i></div>
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            <span class="text-muted font-size-13">Total Employees</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success me-3"><i class="bx bx-user-check"></i></div>
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                            <span class="text-muted font-size-13">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-info-subtle text-info me-3"><i class="bx bx-wallet"></i></div>
                        <div>
                            <h4 class="mb-0">{{ \App\Support\Money::compact($stats['salaries']) }}</h4>
                            <span class="text-muted font-size-13">Monthly Salaries</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning me-3"><i class="bx bx-calendar-x"></i></div>
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['onLeave']) }}</h4>
                            <span class="text-muted font-size-13">On Leave Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-danger-subtle text-danger me-3"><i class="bx bx-time-five"></i></div>
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['presentToday']) }}</h4>
                            <span class="text-muted font-size-13">Present Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/employees') }}">
                <div class="col-md-5">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email, phone, code">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
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
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $employee->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $employee->name }}</h6>
                                            <span class="text-muted font-size-12">{{ $employee->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $employee->employee_code ?? '—' }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $employee->assignedRole?->color ?? 'secondary' }}-subtle text-{{ $employee->assignedRole?->color ?? 'secondary' }}">
                                        {{ $employee->assignedRole?->name ?? 'No role' }}
                                    </span>
                                </td>
                                <td>{{ $employee->department?->name ?? '—' }}</td>
                                <td>{{ $employee->designationRecord?->title ?? '—' }}</td>
                                <td>
                                    <span class="fw-medium">{{ \App\Support\Money::format($employee->basic_salary) }}</span>
                                    <span class="text-muted font-size-12">{{ $employee->salary_currency }}</span>
                                </td>
                                <td>
                                    @if ($employee->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @can('employees.view')
                                        <a href="{{ url('/employees/'.$employee->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bx bx-show"></i></a>
                                    @endcan
                                    @can('employees.update')
                                        <a href="{{ url('/employees/'.$employee->id.'/edit') }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                                    @endcan
                                    @can('users.update')
                                        <form action="{{ url('/users/'.$employee->id.'/toggle') }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning" title="{{ $employee->is_active ? 'Disable' : 'Enable' }}"><i class="bx bx-power-off"></i></button>
                                        </form>
                                    @endcan
                                    @can('employees.delete')
                                        @unless ($employee->is_super_admin || $employee->is(auth()->user()))
                                            <form action="{{ url('/employees/'.$employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this employee?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                            </form>
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $employees->links() }}</div>
        </div>
    </div>
@endsection
