@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', 'Daily Attendance')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('page-actions')
    <a href="{{ url('/attendance/report') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-bar-chart-alt-2 me-1"></i> Monthly Report</a>
    <a href="{{ url('/attendance/my') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-user-check me-1"></i> My Attendance</a>
@endsection

@php
    $statusColors = [
        'present' => 'success',
        'remote' => 'info',
        'half-day' => 'warning',
        'late' => 'warning',
        'leave' => 'primary',
        'holiday' => 'secondary',
        'absent' => 'danger',
    ];
@endphp

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/attendance') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> View</button>
                </div>
                <div class="col-md-7 d-flex justify-content-md-end gap-2">
                    <a href="{{ url('/attendance?date='.$date->copy()->subDay()->toDateString()) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-chevron-left"></i> {{ $date->copy()->subDay()->format('d M') }}</a>
                    <a href="{{ url('/attendance?date='.$date->copy()->addDay()->toDateString()) }}" class="btn btn-outline-secondary btn-sm">{{ $date->copy()->addDay()->format('d M') }} <i class="bx bx-chevron-right"></i></a>
                </div>
            </form>
        </div>
    </div>

    @if ($isHoliday)
        <div class="alert alert-info d-flex align-items-center">
            <i class="bx bx-party me-2"></i> {{ $date->format('d M Y') }} is marked as a holiday.
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ number_format($summary['present']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Present</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-warning">{{ number_format($summary['late']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Late</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ number_format($summary['absent']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Absent</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ number_format($summary['leave']) }}</h3>
                <p class="text-muted mb-0 font-size-13">On Leave</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-muted">{{ number_format($summary['unmarked']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Unmarked</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{ $date->format('l, d M Y') }} Register</h5>
            <span class="text-muted font-size-13">{{ $employees->count() }} employees</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Hours</th>
                            <th style="min-width: 320px;">Mark Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php($record = $records[$employee->id] ?? null)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $employee->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $employee->name }}</h6>
                                            <span class="text-muted font-size-12">{{ $employee->department?->name ?? 'Unassigned' }}{{ $employee->employee_code ? ' · '.$employee->employee_code : '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($record)
                                        <span class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$record->status] ?? 'secondary' }}">{{ str_replace('-', ' ', ucfirst($record->status)) }}</span>
                                        @if ($record->notes)
                                            <div class="text-muted font-size-12">{{ $record->notes }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted">Not marked</span>
                                    @endif
                                </td>
                                <td>{{ $record && $record->check_in ? substr($record->check_in, 0, 5) : '—' }}</td>
                                <td>{{ $record && $record->check_out ? substr($record->check_out, 0, 5) : '—' }}</td>
                                <td>{{ $record ? number_format((float) $record->worked_hours, 2) : '—' }}</td>
                                <td>
                                    @can('attendance.manage')
                                        <form action="{{ url('/attendance/mark') }}" method="POST" class="row g-1">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                            <div class="col-6">
                                                <select name="status" class="form-select form-select-sm">
                                                    @foreach (\App\Models\AttendanceRecord::STATUSES as $status)
                                                        <option value="{{ $status }}" @selected(($record?->status ?? 'present') === $status)>{{ str_replace('-', ' ', ucfirst($status)) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <input type="time" name="check_in" class="form-control form-control-sm" value="{{ $record && $record->check_in ? substr($record->check_in, 0, 5) : '' }}">
                                            </div>
                                            <div class="col-3">
                                                <input type="time" name="check_out" class="form-control form-control-sm" value="{{ $record && $record->check_out ? substr($record->check_out, 0, 5) : '' }}">
                                            </div>
                                            <div class="col-12 text-end">
                                                <button class="btn btn-sm btn-outline-primary"><i class="bx bx-save me-1"></i> {{ $record ? 'Update' : 'Mark' }}</button>
                                            </div>
                                        </form>
                                    @else
                                        <span class="text-muted font-size-12">View only</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
