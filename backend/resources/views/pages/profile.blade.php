@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('page-actions')
    <a href="{{ url('/profile/pdf') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-download me-1"></i> Download Profile PDF</a>
@endsection

@php($permissions = $user->effectivePermissionKeys())

@section('content')
    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-body text-center">
                    <img src="{{ $user->avatarUrl() }}" class="rounded-circle avatar-xl mb-3" alt="">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-1">{{ $user->designation ?? $user->designationRecord?->title ?? 'Team Member' }}</p>
                    <span class="badge bg-{{ $user->assignedRole?->color ?? 'primary' }}-subtle text-{{ $user->assignedRole?->color ?? 'primary' }}">
                        {{ $user->assignedRole?->name ?? 'No role' }}
                    </span>
                    <hr>
                    <form action="{{ url('/profile/avatar') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="avatar" class="form-control" accept="image/*" required>
                            <button class="btn btn-primary"><i class="bx bx-upload"></i></button>
                        </div>
                        <small class="text-muted">Upload a new profile picture (JPG/PNG, max 4MB)</small>
                    </form>
                    <div class="text-start">
                        <p class="mb-2"><i class="bx bx-envelope text-muted me-2"></i>{{ $user->email }}</p>
                        <p class="mb-2"><i class="bx bx-phone text-muted me-2"></i>{{ $user->phone ?? 'Not provided' }}</p>
                        <p class="mb-2"><i class="bx bxl-whatsapp text-muted me-2"></i>{{ $user->whatsapp_number ?? 'Not provided' }}</p>
                        <p class="mb-2"><i class="bx bx-buildings text-muted me-2"></i>{{ $user->department?->name ?? 'No department' }}</p>
                        <p class="mb-2"><i class="bx bx-calendar text-muted me-2"></i>Joined {{ $user->joining_date?->format('d M Y') ?? 'N/A' }}</p>
                        <p class="mb-0"><i class="bx bx-user-check text-muted me-2"></i>Reports to {{ $user->manager?->name ?? 'Not assigned' }}</p>
                    </div>
                </div>
            </div>

            @if (auth()->user()->can('sales.view') || auth()->user()->can('leads.view') || auth()->user()->can('payroll.view_own'))
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">My Snapshot</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">My Sales</span><strong>{{ number_format($mySalesCount) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">My Leads</span><strong>{{ number_format($myLeadsCount) }}</strong>
                        </div>
                        @if (auth()->user()->can('payroll.view_own'))
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Basic Salary</span>
                                <strong>{{ \App\Support\Money::format($user->basic_salary) }} {{ $user->salary_currency }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">Commission Rate</span><strong>{{ number_format((float) $user->commission_rate, 2) }}%</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">My Access ({{ $permissions->count() }} permissions)</h5></div>
                <div class="card-body cds-scroll">
                    @foreach ($permissions->groupBy(fn ($key) => explode('.', $key)[0]) as $module => $keys)
                        <h6 class="fw-semibold text-capitalize">{{ $module }}</h6>
                        <div class="mb-2">
                            @foreach ($keys as $key)
                                <span class="badge bg-light text-dark border mb-1">{{ $key }}</span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Edit Profile</h5></div>
                <form action="{{ url('/profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $user->whatsapp_number) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" value="{{ old('designation', $user->designation) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth?->toDateString()) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Prefer not to say</option>
                                    @foreach (['male', 'female', 'other'] as $gender)
                                        <option value="{{ $gender }}" @selected(old('gender', $user->gender) === $gender)>{{ ucfirst($gender) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $user->country) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->bio) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Emergency Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $user->bank_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bank Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $user->bank_account_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IBAN</label>
                                <input type="text" name="bank_iban" class="form-control" value="{{ old('bank_iban', $user->bank_iban) }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Profile</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Change Password</h5></div>
                <form action="{{ url('/profile/password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-warning"><i class="bx bx-lock me-1"></i> Update Password</button>
                    </div>
                </form>
            </div>

            @unless ($user->is_super_admin)
                <div class="card border-danger">
                    <div class="card-header border-danger"><h5 class="card-title text-danger mb-0">Delete Account</h5></div>
                    <form action="{{ url('/profile') }}" method="POST" onsubmit="return confirm('This will permanently delete your account. Continue?');">
                        @csrf @method('DELETE')
                        <div class="card-body">
                            <p class="text-muted mb-3">Deleting your account removes your access permanently. Enter your password to confirm.</p>
                            <input type="password" name="password" class="form-control" placeholder="Your password" required>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-danger"><i class="bx bx-trash me-1"></i> Delete My Account</button>
                        </div>
                    </form>
                </div>
            @endunless
        </div>
    </div>
@endsection
