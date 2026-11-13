@extends('layouts.app')

@php
    $isEdit = $organization->exists;
@endphp

@section('title', $isEdit ? 'Edit Organization' : 'Add Organization')
@section('page-title', $isEdit ? 'Edit Organization' : 'Add Organization')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organizations.index') }}">Organizations</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? $organization->name : 'Create' }}</li>
@endsection

@section('content')
    <form action="{{ $isEdit ? route('organizations.update', $organization) : route('organizations.store') }}" method="POST">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Organization Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Business / Client Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $organization->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Type <span class="text-danger">*</span></label>
                                <select name="business_type" class="form-select @error('business_type') is-invalid @enderror" required>
                                    @foreach ($businessTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('business_type', $organization->business_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('business_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Kitchen dashboard & restaurant features apply to Restaurant type.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $organization->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $organization->phone) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $organization->whatsapp) }}" placeholder="+9715...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $organization->website) }}" placeholder="https://">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $organization->address) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $organization->city) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $organization->country) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Timezone</label>
                                <input type="text" name="timezone" class="form-control" value="{{ old('timezone', $organization->timezone) }}" placeholder="Asia/Dubai">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    @foreach (['USD', 'AED', 'EUR', 'GBP', 'PKR', 'SAR', 'INR'] as $currency)
                                        <option value="{{ $currency }}" @selected(old('currency', $organization->currency) === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Internal Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notes visible only to platform admins">{{ old('notes', $organization->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @if (! $isEdit)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Owner Account (Client Login)</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted font-size-13">A login account will be created for the client. Credentials will be emailed automatically.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}" required>
                                    @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Owner Email <span class="text-danger">*</span></label>
                                    <input type="email" name="owner_email" class="form-control @error('owner_email') is-invalid @enderror" value="{{ old('owner_email') }}" required>
                                    @error('owner_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="owner_username" class="form-control @error('owner_username') is-invalid @enderror" value="{{ old('owner_username') }}" placeholder="Auto-generated from email if left blank">
                                    @error('owner_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Owner Phone</label>
                                    <input type="text" name="owner_phone" class="form-control" value="{{ old('owner_phone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="text" name="owner_password" class="form-control @error('owner_password') is-invalid @enderror" value="{{ old('owner_password') }}" placeholder="Leave blank to auto-generate">
                                    @error('owner_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Subscription / Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Plan Name</label>
                                <input type="text" name="plan_name" class="form-control" value="{{ old('plan_name', $organization->plan_name) }}" placeholder="Restaurant Starter">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Plan Amount</label>
                                <input type="number" step="0.01" min="0" name="plan_amount" class="form-control" value="{{ old('plan_amount', $organization->plan_amount) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                                <select name="billing_cycle" class="form-select" required>
                                    @foreach (\App\Models\Organization::BILLING_CYCLES as $cycle)
                                        <option value="{{ $cycle }}" @selected(old('billing_cycle', $organization->billing_cycle) === $cycle)>{{ ucfirst(str_replace('_', ' ', $cycle)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Paid Until</label>
                                <input type="date" name="paid_until" class="form-control" value="{{ old('paid_until', $organization->paid_until?->format('Y-m-d')) }}">
                                <div class="form-text">Leave blank for unpaid clients. Recording a payment sets this automatically.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Trial Ends At</label>
                                <input type="date" name="trial_ends_at" class="form-control" value="{{ old('trial_ends_at', $organization->trial_ends_at?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ $isEdit ? route('organizations.show', $organization) : route('organizations.index') }}" class="btn btn-light">Cancel</a>
                            <button class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create Organization' }}
                            </button>
                        </div>
                        @if (! $isEdit)
                            <p class="text-muted font-size-12 mb-0 mt-3"><i class="bx bx-info-circle me-1"></i> On creation, the client owner account is created and a welcome email with login credentials is sent.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
