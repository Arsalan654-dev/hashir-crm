@extends('layouts.app')

@section('title', 'Roles & Access')
@section('page-title', 'Roles & Access')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('permissions.manage'))
        <a href="{{ url('/permissions') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-grid-alt me-1"></i> Permission Matrix</a>
    @endif
    @if (auth()->user()->can('roles.create'))
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#role-create-modal"><i class="bx bx-plus me-1"></i> New Role</button>
    @endif
@endsection

@section('content')
    <div class="row g-3">
        @foreach ($roles as $role)
            <div class="col-xl-4 col-md-6">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="mb-0">
                                <span class="badge bg-{{ $role->color ?? 'primary' }}-subtle text-{{ $role->color ?? 'primary' }} me-1">&nbsp;</span>
                                {{ $role->name }}
                            </h5>
                            <div>
                                @if ($role->is_system)
                                    <span class="badge bg-light text-dark">System</span>
                                @endif
                                @if ($role->isSuperAdmin())
                                    <span class="badge bg-danger-subtle text-danger">Full Access</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-muted font-size-13 mb-3">{{ $role->description ?: 'No description provided.' }}</p>
                        <div class="d-flex gap-3 mb-3">
                            <div><h5 class="mb-0">{{ $role->users_count }}</h5><span class="text-muted font-size-12">Users</span></div>
                            <div><h5 class="mb-0">{{ $role->permissions_count }}</h5><span class="text-muted font-size-12">Permissions</span></div>
                        </div>
                        <div class="d-flex gap-2">
                            @if (auth()->user()->can('roles.update'))
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#role-edit-{{ $role->id }}">
                                    <i class="bx bx-edit me-1"></i> Edit &amp; Permissions
                                </button>
                            @endif
                            @if (auth()->user()->can('roles.delete') && ! $role->is_system)
                                <form action="{{ url('/roles/'.$role->id) }}" method="POST" onsubmit="return confirm('Delete this role?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if (auth()->user()->can('roles.create'))
        <div class="modal fade" id="role-create-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <form action="{{ url('/roles') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Role Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Colour</label>
                                <select name="color" class="form-select">
                                    @foreach (['primary', 'success', 'info', 'warning', 'danger', 'secondary'] as $color)
                                        <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                        </div>
                        @include('partials.permission-checkboxes', ['prefix' => 'create'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @foreach ($roles as $role)
        @can('roles.update')
            <div class="modal fade" id="role-edit-{{ $role->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <form action="{{ url('/roles/'.$role->id) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Role — {{ $role->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Role Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Colour</label>
                                    <select name="color" class="form-select">
                                        @foreach (['primary', 'success', 'info', 'warning', 'danger', 'secondary'] as $color)
                                            <option value="{{ $color }}" @selected($role->color === $color)>{{ ucfirst($color) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" value="{{ $role->description }}">
                                </div>
                            </div>
                            @if ($role->isSuperAdmin())
                                <div class="alert alert-warning mb-0">
                                    The super admin role always has access to everything and cannot be restricted.
                                </div>
                            @else
                                @include('partials.permission-checkboxes', [
                                    'prefix' => 'role-'.$role->id,
                                    'selected' => $rolePermissions[$role->id] ?? [],
                                ])
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save Role</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
@endsection
