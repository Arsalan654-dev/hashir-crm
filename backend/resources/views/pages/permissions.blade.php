@extends('layouts.app')

@section('title', 'Permission Matrix')
@section('page-title', 'Permission Matrix')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/roles') }}">Roles</a></li>
    <li class="breadcrumb-item active">Permissions</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('/permissions') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label mb-1">Role</label>
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected($selectedRole?->id === $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label mb-1">Or jump to a user's personal permissions</label>
                    <select name="user" class="form-select" onchange="this.form.submit()">
                        <option value="">Select user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(optional($selectedUser)->id === $user->id)>
                                {{ $user->name }} — {{ $user->assignedRole?->name ?? 'No role' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Apply</button>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedUser)
        <form action="{{ url('/permissions/users/'.$selectedUser->id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div>
                        <h5 class="card-title mb-0">{{ $selectedUser->name }}</h5>
                        <span class="text-muted font-size-13">
                            Role: {{ $selectedUser->assignedRole?->name ?? 'No role' }} ·
                            Tick = extra permission, cross out = deny even if the role allows it
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @php($allowed = $selectedUser->directPermissions()->wherePivot('effect', 'allow')->pluck('key')->all())
                    @php($denied = $selectedUser->directPermissions()->wherePivot('effect', 'deny')->pluck('key')->all())
                    @foreach ($permissionGroups as $group => $permissions)
                        <h6 class="fw-semibold mt-3">{{ $group }}</h6>
                        <div class="row g-2">
                            @foreach ($permissions as $key => $label)
                                <div class="col-xl-3 col-md-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="allowed[]" value="{{ $key }}"
                                                id="allow-{{ Str::slug($key) }}" @checked(in_array($key, $allowed, true))
                                                title="Grant extra permission">
                                        </div>
                                        <div class="form-check form-check-danger mb-0">
                                            <input class="form-check-input" type="checkbox" name="denied[]" value="{{ $key }}"
                                                id="deny-{{ Str::slug($key) }}" @checked(in_array($key, $denied, true))
                                                title="Deny this permission">
                                        </div>
                                        <label class="font-size-13" for="allow-{{ Str::slug($key) }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save {{ $selectedUser->name }}'s Permissions</button>
                </div>
            </div>
        </form>
    @elseif ($selectedRole)
        <form action="{{ url('/permissions/roles/'.$selectedRole->id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $selectedRole->name }} — Role Permissions</h5>
                    <span class="text-muted font-size-13">{{ $selectedRole->description }}</span>
                </div>
                <div class="card-body">
                    @if ($selectedRole->isSuperAdmin())
                        <div class="alert alert-warning mb-0">Super admin always has full access.</div>
                    @else
                        @include('partials.permission-checkboxes', [
                            'prefix' => 'matrix-'.$selectedRole->id,
                            'selected' => $rolePermissions,
                        ])
                    @endif
                </div>
                @unless ($selectedRole->isSuperAdmin())
                    <div class="card-footer">
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Role Permissions</button>
                    </div>
                @endunless
            </div>
        </form>
    @endif
@endsection
