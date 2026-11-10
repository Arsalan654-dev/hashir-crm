@extends('layouts.app')

@section('title', 'Set Target')
@section('page-title', 'Set Target')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/targets') }}">Targets</a></li>
    <li class="breadcrumb-item active">Set Target</li>
@endsection

@section('page-actions')
    <a href="{{ url('/targets') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Back to Targets</a>
@endsection

@section('content')
    <form action="{{ url('/targets') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Target Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(old('user_id') == $employee->id)>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period Type <span class="text-danger">*</span></label>
                                <select name="period_type" class="form-select" required>
                                    @foreach (\App\Models\Target::PERIODS as $period)
                                        <option value="{{ $period }}" @selected(old('period_type', 'monthly') === $period)>{{ ucfirst($period) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period Start <span class="text-danger">*</span></label>
                                <input type="date" name="period_start" class="form-control" required value="{{ old('period_start', now()->toDateString()) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period End <span class="text-danger">*</span></label>
                                <input type="date" name="period_end" class="form-control" required value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" maxlength="255" value="{{ old('title') }}" placeholder="e.g. September Sales Target">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Amount <span class="text-danger">*</span></label>
                                <input type="number" name="target_amount" step="0.01" min="0" class="form-control" required value="{{ old('target_amount') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Orders</label>
                                <input type="number" name="target_orders" min="0" class="form-control" value="{{ old('target_orders') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bonus Amount</label>
                                <input type="number" name="bonus_amount" step="0.01" min="0" class="form-control" value="{{ old('bonus_amount') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ url('/targets') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Create Target</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Guidelines</h5></div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 font-size-13 text-muted">
                            <li class="mb-2"><i class="bx bx-check-circle text-success me-1"></i> The period end must be on or after the period start.</li>
                            <li class="mb-2"><i class="bx bx-check-circle text-success me-1"></i> Achieved amounts are calculated from confirmed and completed sales within the period.</li>
                            <li class="mb-2"><i class="bx bx-check-circle text-success me-1"></i> The bonus is paid when the target is reached during payroll generation.</li>
                            <li><i class="bx bx-check-circle text-success me-1"></i> Employees see their own progress on the My Targets page.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
