@extends('layouts.app')

@section('title', 'Add Lead')
@section('page-title', 'Add Lead')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/leads') }}">Leads</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    @php($currencies = array_values(array_unique(array_merge(['USD', 'EUR', 'GBP', 'AED', 'SAR', 'PKR', 'INR'], [$lead->currency ?? 'USD']))))

    <form action="{{ url('/leads') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Contact Information</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $lead->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <input type="text" name="company" class="form-control" value="{{ old('company', $lead->company) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $lead->whatsapp_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $lead->country) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Existing Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">None / not a customer yet</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected(old('customer_id', $lead->customer_id) == $customer->id)>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Qualification</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Source</label>
                                <select name="source" class="form-select">
                                    @foreach (\App\Models\Lead::SOURCES as $source)
                                        <option value="{{ $source }}" @selected(old('source', $lead->source ?? 'manual') === $source)>{{ ucwords(str_replace('_', ' ', $source)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach (\App\Models\Lead::STATUSES as $status)
                                        <option value="{{ $status }}" @selected(old('status', $lead->status ?? 'new') === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    @foreach (\App\Models\Lead::PRIORITIES as $priority)
                                        <option value="{{ $priority }}" @selected(old('priority', $lead->priority ?? 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estimated Value</label>
                                <input type="number" step="0.01" min="0" name="estimated_value" class="form-control" value="{{ old('estimated_value', $lead->estimated_value ?? 0) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency }}" @selected(old('currency', $lead->currency ?? 'USD') === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Product Interest</label>
                                <input type="text" name="product_interest" class="form-control" value="{{ old('product_interest', $lead->product_interest) }}" placeholder="Product or service the lead asked about">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Follow Up</label>
                                <input type="datetime-local" name="next_follow_up_at" class="form-control" value="{{ old('next_follow_up_at', $lead->next_follow_up_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lost Reason</label>
                                <input type="text" name="lost_reason" class="form-control" value="{{ old('lost_reason', $lead->lost_reason) }}" placeholder="Only when the lead is lost">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Message &amp; Notes</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="2">{{ old('message', $lead->message) }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $lead->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Assignment</h5></div>
                    <div class="card-body">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach ($salespeople as $salesperson)
                                <option value="{{ $salesperson->id }}" @selected(old('assigned_to', $lead->assigned_to) == $salesperson->id)>{{ $salesperson->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Leave unassigned to place the lead in the assignment queue.</small>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ url('/leads') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Create Lead</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
