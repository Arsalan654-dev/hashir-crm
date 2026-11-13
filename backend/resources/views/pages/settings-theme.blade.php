@extends('layouts.app')

@section('title', 'Theme & Branding')
@section('page-title', 'Theme & Branding')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/settings') }}">Settings</a></li>
    <li class="breadcrumb-item active">Theme</li>
@endsection

@section('content')
    <form action="{{ url('/settings/theme') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Branding</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Dashboard Name</label>
                                <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name', $branding['name']) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tagline</label>
                                <input type="text" name="brand_tagline" class="form-control" value="{{ old('brand_tagline', $branding['tagline']) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Primary Colour</label>
                                <input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $branding['primary_color']) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sidebar Style</label>
                                <select name="sidebar_style" class="form-select">
                                    @foreach (['dark' => 'Dark', 'light' => 'Light', 'brand' => 'Brand'] as $value => $label)
                                        <option value="{{ $value }}" @selected($branding['sidebar_style'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Topbar Style</label>
                                <select name="topbar_style" class="form-select">
                                    @foreach (['light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                                        <option value="{{ $value }}" @selected($branding['topbar_style'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Layout</label>
                                <select name="theme_layout" class="form-select">
                                    @foreach (['vertical' => 'Vertical', 'horizontal' => 'Horizontal'] as $value => $label)
                                        <option value="{{ $value }}" @selected($branding['layout'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Logo (light backgrounds)</label>
                                <input type="file" name="logo_dark" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Logo (dark backgrounds)</label>
                                <input type="file" name="logo_light" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="favicon" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Theme</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-0">
                    <div class="card-header"><h5 class="card-title mb-0">Live Preview</h5></div>
                    <div class="card-body">
                        <p class="text-muted font-size-13">Current branding assets in use across the CRM.</p>
                        <div class="border rounded p-3 mb-3 bg-light">
                            <img src="{{ asset($branding['logo']) }}" alt="Logo" style="max-height: 46px;">
                        </div>
                        <div class="border rounded p-3 mb-3 bg-dark">
                            <img src="{{ asset($branding['logo_light']) }}" alt="Logo light" style="max-height: 46px;">
                        </div>
                        <div class="border rounded p-3 mb-3">
                            <img src="{{ asset($branding['logo_mark']) }}" alt="Mark" style="max-height: 44px;">
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="border rounded p-2" style="background: {{ $branding['primary_color'] }}; width: 46px; height: 46px;"></span>
                            <div>
                                <div class="fw-semibold">{{ $branding['name'] }}</div>
                                <div class="text-muted font-size-12">{{ $branding['tagline'] }}</div>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted font-size-13 mb-0">
                            Tip: to use the exact PNG logo you supplied, upload it above as
                            <strong>Logo (light backgrounds)</strong> and <strong>Logo (dark backgrounds)</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
