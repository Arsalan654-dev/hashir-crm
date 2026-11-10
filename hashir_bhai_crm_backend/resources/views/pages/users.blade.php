@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('users.create'))
        <a href="{{ url('/users/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Add User</a>
    @endif
    @if (auth()->user()->can('permissions.manage', 'roles.view'))
        <a href="{{ url('/permissions') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-shield-quarter me-1"></i> Permission Matrix</a>
    @endif
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1">{{ number_format($totalUsers) }}</h3>
                <p class="text-muted mb-0 font-size-13">Total Users</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ number_format($activeUsers) }}</h3>
                <p class="text-muted mb-0 font-size-13">Active</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ number_format($inactiveUsers) }}</h3>
                <p class="text-muted mb-0 font-size-13">Inactive</p>
            </div></div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ number_format($superAdmins) }}</h3>
                <p class="text-muted mb-0 font-size-13">Super Admins</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/users') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email, phone, code">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(request('role') == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All</option>
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
                <div class="col-md-1">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Code</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $user->name }}</h6>
                                            <span class="text-muted font-size-12">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $user->employee_code ?? '—' }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $user->assignedRole?->color ?? 'secondary' }}-subtle text-{{ $user->assignedRole?->color ?? 'secondary' }}">
                                        {{ $user->assignedRole?->name ?? 'No role' }}
                                    </span>
                                </td>
                                <td>{{ $user->department?->name ?? '—' }}</td>
                                <td>
                                    <span class="text-muted font-size-13">
                                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @if (auth()->user()->can('users.update'))
                                        <a href="{{ url('/users/'.$user->id.'/edit') }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ url('/users/'.$user->id.'/toggle') }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                    @endif
                                    @if (auth()->user()->can('users.impersonate') && ! $user->is(auth()->user()))
                                        <form action="{{ url('/users/'.$user->id.'/impersonate') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" title="Login as this user"><i class="bx bx-log-in-circle"></i></button>
                                        </form>
                                    @endif
                                    @if (auth()->user()->can('users.delete') && ! $user->is_super_admin)
                                        <form action="{{ url('/users/'.$user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user permanently?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>
@endsection
