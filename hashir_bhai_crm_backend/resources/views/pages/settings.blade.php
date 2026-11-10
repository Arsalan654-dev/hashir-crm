@extends('layouts.app')

@section('title', 'General Settings')
@section('page-title', 'Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('page-actions')
    <a href="{{ url('/settings/theme') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-palette me-1"></i> Theme &amp; Branding</a>
    <a href="{{ url('/settings/integrations') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-plug me-1"></i> Integrations</a>
@endsection

@php($company = $settings['company'])
@php($sales = $settings['sales'])
@php($payroll = $settings['payroll'])

@section('content')
    <form action="{{ url('/settings') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Company Profile</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $company['company.name'] ?? 'Cyprus Digital Solution') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Email</label>
                                <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $company['company.email'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $company['company.phone'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $company['company.website'] ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $company['company.address'] ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency</label>
                                <select name="company_currency" class="form-select">
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency }}" @selected(($company['company.currency'] ?? 'USD') === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" name="company_currency_symbol" class="form-control" value="{{ old('company_currency_symbol', $company['company.currency_symbol'] ?? '$') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date Format</label>
                                <select name="company_date_format" class="form-select">
                                    @foreach (['d M Y', 'd/m/Y', 'm/d/Y', 'Y-m-d'] as $format)
                                        <option value="{{ $format }}" @selected(($company['company.date_format'] ?? 'd M Y') === $format)>{{ now()->format($format) }} ({{ $format }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Timezone</label>
                                <select name="company_timezone" class="form-select">
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected(($company['company.timezone'] ?? 'UTC') === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Sales Defaults</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Default Commission Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="sales_commission_rate" class="form-control"
                                value="{{ old('sales_commission_rate', $sales['sales.commission_rate'] ?? 5) }}">
                            <small class="text-muted">Used when no commission rule matches a sale.</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Default Tax Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="sales_tax_rate" class="form-control"
                                value="{{ old('sales_tax_rate', $sales['sales.tax_rate'] ?? 0) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Payroll Defaults</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Working Days Per Month</label>
                            <input type="number" min="1" max="31" name="payroll_working_days_per_month" class="form-control"
                                value="{{ old('payroll_working_days_per_month', $payroll['payroll.working_days_per_month'] ?? 26) }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Late / Absence Deduction per Day</label>
                            <input type="number" step="0.01" min="0" name="payroll_late_deduction" class="form-control"
                                value="{{ old('payroll_late_deduction', $payroll['payroll.late_deduction'] ?? 0) }}">
                        </div>
                    </div>
                </div>

                @if (auth()->user()->can('settings.manage'))
                    <div class="card mb-0">
                        <div class="card-body text-end">
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Settings</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection
