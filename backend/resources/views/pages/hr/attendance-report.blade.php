@extends('layouts.app')

@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/attendance') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Monthly Report</li>
@endsection

@section('page-actions')
    <a href="{{ url('/attendance') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-calendar-check me-1"></i> Daily Register</a>
@endsection

@php
    $statusLabels = [
        'present' => 'Present',
        'remote' => 'Remote',
        'half-day' => 'Half Day',
        'late' => 'Late',
        'leave' => 'Leave',
        'holiday' => 'Holiday',
        'absent' => 'Absent',
    ];
    $allRecords = $records->collapse();
    $summary = [
        'employees' => $employees->count(),
        'marked' => $allRecords->count(),
        'present' => $allRecords->whereIn('status', ['present', 'remote', 'half-day'])->count(),
        'late' => $allRecords->where('status', 'late')->count(),
        'absent' => $allRecords->where('status', 'absent')->count(),
        'leave' => $allRecords->where('status', 'leave')->count(),
        'hours' => (float) $allRecords->sum('worked_hours'),
        'overtime' => (float) $allRecords->sum('overtime_hours'),
    ];
@endphp

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/attendance/report') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Month</label>
                    <select name="month" class="form-select">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month === $m)>{{ \Illuminate\Support\Carbon::create(null, $m, 1)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Year</label>
                    <select name="year" class="form-select">
                        @foreach (range(now()->year - 4, now()->year + 1) as $optionYear)
                            <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Apply</button>
                </div>
                <div class="col-md-5 text-md-end">
                    <span class="text-muted font-size-13">Showing {{ $periodLabel }}</span>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1">{{ number_format($summary['employees']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Employees</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ number_format($summary['present']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Present Days</p>
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
                <p class="text-muted mb-0 font-size-13">Leave</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1">{{ number_format($summary['hours'], 2) }}</h3>
                <p class="text-muted mb-0 font-size-13">Worked Hours</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-warning">{{ number_format($summary['overtime'], 2) }}</h3>
                <p class="text-muted mb-0 font-size-13">Overtime</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{ $periodLabel }} Summary</h5>
            <span class="text-muted font-size-13">{{ number_format($summary['marked']) }} records marked</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            @foreach ($statusLabels as $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                            <th class="text-end">Worked Hours</th>
                            <th class="text-end">Overtime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php($userRecords = $records[$employee->id] ?? collect())
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $employee->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $employee->name }}</h6>
                                            <span class="text-muted font-size-12">{{ $employee->department?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($statusLabels as $status => $label)
                                    <td class="text-center">{{ $userRecords->where('status', $status)->count() ?: '—' }}</td>
                                @endforeach
                                <td class="text-end">{{ number_format((float) $userRecords->sum('worked_hours'), 2) }}</td>
                                <td class="text-end">{{ number_format((float) $userRecords->sum('overtime_hours'), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($statusLabels) + 3 }}" class="text-center text-muted py-4">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-semibold">Totals</td>
                            @foreach ($statusLabels as $status => $label)
                                <td class="text-center fw-semibold">{{ $allRecords->where('status', $status)->count() ?: '—' }}</td>
                            @endforeach
                            <td class="text-end fw-semibold">{{ number_format($summary['hours'], 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($summary['overtime'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
