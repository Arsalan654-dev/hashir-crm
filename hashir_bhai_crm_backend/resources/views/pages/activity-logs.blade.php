@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Activity Logs</li>
@endsection

@php
    $actionColor = function (string $action): string {
        $key = strtolower($action);

        return match (true) {
            str_contains($key, 'delete'), str_contains($key, 'remove'), str_contains($key, 'reject') => 'danger',
            str_contains($key, 'create'), str_contains($key, 'store'), str_contains($key, 'approve') => 'success',
            str_contains($key, 'update'), str_contains($key, 'edit') => 'info',
            str_contains($key, 'login') => 'primary',
            str_contains($key, 'export'), str_contains($key, 'send') => 'warning',
            default => 'secondary',
        };
    };
@endphp

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/activity-logs') }}">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Description, action, module">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Module</label>
                    <select name="module" class="form-select">
                        <option value="">All modules</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ ucwords(str_replace('_', ' ', $module)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Subject</th>
                            <th>IP Address</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    @if ($log->user)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $log->user->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                            <span class="ms-2 font-size-13">{{ $log->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->module)
                                        <span class="badge bg-primary-subtle text-primary">{{ ucwords(str_replace('_', ' ', $log->module)) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php($color = $actionColor((string) $log->action))
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucwords(str_replace('_', ' ', (string) $log->action)) }}</span>
                                </td>
                                <td>
                                    <span class="font-size-13">{{ $log->description ?? '—' }}</span>
                                </td>
                                <td>
                                    @if ($log->subject_type)
                                        <span class="badge bg-light text-dark">{{ class_basename($log->subject_type) }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><span class="text-muted font-size-13">{{ $log->ip_address ?? '—' }}</span></td>
                                <td>
                                    <span class="font-size-13">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                    <div class="text-muted font-size-12">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No activity logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
