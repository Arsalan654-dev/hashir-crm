@extends('layouts.app')

@php($isEdit = $employee->exists)

@section('title', $isEdit ? 'Edit Employee' : 'Add Employee')
@section('page-title', $isEdit ? 'Edit Employee — '.$employee->name : 'Add Employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/employees') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
@endsection

@section('content')
    <form action="{{ $isEdit ? url('/employees/'.$employee->id) : url('/employees') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Personal Information</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $employee->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required value="{{ old('username', $employee->username) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required value="{{ old('email', $employee->email) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $employee->whatsapp_number) }}">
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
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->date_of_birth?->toDateString()) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Prefer not to say</option>
                                    @foreach (['male', 'female', 'other'] as $gender)
                                        <option value="{{ $gender }}" @selected(old('gender', $employee->gender) === $gender)>{{ ucfirst($gender) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marital Status</label>
                                <select name="marital_status" class="form-select">
                                    <option value="">Not specified</option>
                                    @foreach (['single', 'married', 'divorced', 'widowed'] as $status)
                                        <option value="{{ $status }}" @selected(old('marital_status', $employee->marital_status) === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">National ID</label>
                                <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $employee->national_id) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $employee->address) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $employee->city) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $employee->country) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Employment</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Employee Code</label>
                                <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code) }}" placeholder="EMP-0001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                <select name="employment_type" class="form-select" required>
                                    @foreach (['full-time', 'part-time', 'contract', 'intern', 'freelance'] as $type)
                                        <option value="{{ $type }}" @selected(old('employment_type', $employee->employment_type) === $type)>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $employee->joining_date?->toDateString()) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Admin Note</label>
                                <textarea name="admin_note" class="form-control" rows="3">{{ old('admin_note', $employee->admin_note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Salary &amp; Bank</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salary Currency</label>
                                <input type="text" name="salary_currency" class="form-control" maxlength="10" value="{{ old('salary_currency', $employee->salary_currency ?: \App\Support\Branding::currency()) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Commission (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control" value="{{ old('commission_rate', $employee->commission_rate ?? 0) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hourly Rate</label>
                                <input type="number" step="0.01" min="0" name="hourly_rate" class="form-control" value="{{ old('hourly_rate', $employee->hourly_rate) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $employee->bank_account_number) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank IBAN</label>
                                <input type="text" name="bank_iban" class="form-control" value="{{ old('bank_iban', $employee->bank_iban) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Emergency Contact</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Access &amp; Account</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id', $employee->role_id) == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Permissions come from this role.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id', $employee->department_id) == $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Designation</label>
                            <select name="designation_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}" @selected(old('designation_id', $employee->designation_id) == $designation->id)>
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
                                    <option value="{{ $manager->id }}" @selected(old('reports_to', $employee->reports_to) == $manager->id)>{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $employee->is_active ?? true))>
                            <label class="form-check-label" for="is_active">Account active</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            @if ($employee->avatar)
                                <img src="{{ $employee->avatarUrl() }}" class="rounded-circle mt-2" width="56" height="56" alt="">
                            @endif
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ $isEdit ? url('/employees/'.$employee->id) : url('/employees') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Employee' : 'Add Employee' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
