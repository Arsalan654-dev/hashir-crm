@extends('layouts.app')

@php($isEdit = $user->exists)

@section('title', $isEdit ? 'Edit User' : 'Add User')
@section('page-title', $isEdit ? 'Edit User — '.$user->name : 'Add User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/users') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
@endsection

@section('content')
    <form action="{{ $isEdit ? url('/users/'.$user->id) : url('/users') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Account Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $user->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required value="{{ old('username', $user->username) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required value="{{ old('email', $user->email) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $user->whatsapp_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isEdit ? 'New Password (leave blank to keep)' : 'Password' }} @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
                                <input type="password" name="password" class="form-control" @unless($isEdit) required @endunless>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" @unless($isEdit) required @endunless>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Employee Code</label>
                                <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $user->employee_code) }}" placeholder="EMP-0001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-select">
                                    @foreach (['full-time', 'part-time', 'contract', 'intern', 'freelance'] as $type)
                                        <option value="{{ $type }}" @selected(old('employment_type', $user->employment_type) === $type)>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $user->joining_date?->toDateString()) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Specific Permissions</h5>
                        <span class="text-muted font-size-12">Overrides on top of the role permissions</span>
                    </div>
                    <div class="card-body">
                        @foreach ($permissionGroups as $group => $permissions)
                            <h6 class="fw-semibold mt-3">{{ $group }}</h6>
                            <div class="row g-2">
                                @foreach ($permissions as $key => $label)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}"
                                                id="perm-{{ Str::slug($key) }}" @checked(in_array($key, old('permissions', $userPermissions->all()), true))>
                                            <label class="form-check-label font-size-13" for="perm-{{ Str::slug($key) }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Role &amp; Employment</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Permissions come from this role.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Designation</label>
                            <select name="designation_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}" @selected(old('designation_id', $user->designation_id) == $designation->id)>
                                        {{ $designation->title }}@if($designation->department) — {{ $designation->department->name }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reports To</label>
                            <select name="reports_to" class="form-select">
                                <option value="">None</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->id }}" @selected(old('reports_to', $user->reports_to) == $manager->id)>{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Basic Salary ({{ \App\Support\Branding::currency() }})</label>
                            <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" value="{{ old('basic_salary', $user->basic_salary ?? 0) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control" value="{{ old('commission_rate', $user->commission_rate ?? 0) }}">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $user->is_active ?? true))>
                            <label class="form-check-label" for="is_active">Account active</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            @if ($user->avatar)
                                <img src="{{ $user->avatarUrl() }}" class="rounded-circle mt-2" width="56" height="56" alt="">
                            @endif
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ url('/users') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update User' : 'Create User' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
