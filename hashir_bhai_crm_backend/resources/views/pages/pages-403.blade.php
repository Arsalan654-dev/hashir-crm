@extends('layouts.app')

@section('title', '403 Forbidden')
@section('page-title', 'Access Denied')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle font-size-42">
                            <i class="bx bx-lock-alt"></i>
                        </span>
                    </div>
                    <h2 class="fw-bold text-danger">403</h2>
                    <h4>You cannot access this</h4>
                    <p class="text-muted">
                        Your account does not have the required permission for this page.
                        Ask your administrator to grant the permission from
                        <strong>Roles &amp; Access → Permission Matrix</strong>.
                    </p>
                    @isset($requiredPermissions)
                        <div class="mb-4">
                            @foreach ($requiredPermissions as $permission)
                                <span class="badge bg-light text-dark border me-1">{{ $permission }}</span>
                            @endforeach
                        </div>
                    @endisset
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ url('/') }}" class="btn btn-primary"><i class="bx bx-home-alt me-1"></i> Back to Dashboard</a>
                        <a href="{{ url('/contacts-profile') }}" class="btn btn-light">My Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.cdsPermissionDenied) {
                window.cdsPermissionDenied('403 — You cannot access this page.');
            }
        });
    </script>
@endpush
