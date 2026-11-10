@extends('layouts.app')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/attendance/my') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Mine</li>
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
    $periodLabel = \Illuminate\Support\Carbon::create($year, $month, 1)->format('F Y');
    $prev = \Illuminate\Support\Carbon::create($year, $month, 1)->subMonth();
    $next = \Illuminate\Support\Carbon::create($year, $month, 1)->addMonth();
@endphp

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">Today — {{ now()->format('l, d M Y') }}</h5>
                    @if ($today?->check_in)
                        <p class="text-muted mb-0">
                            Clocked in at {{ substr($today->check_in, 0, 5) }}
                            @if ($today->check_out)
                                · clocked out at {{ substr($today->check_out, 0, 5) }}
                            @endif
                        </p>
                    @else
                        <p class="text-muted mb-0">You have not clocked in yet.</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @can('attendance.mark_own', 'attendance.manage')
                        <form action="{{ url('/attendance/clock-in') }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm" @disabled($today !== null && $today->check_in !== null)><i class="bx bx-log-in-circle me-1"></i> Clock In</button>
                        </form>
                        <form action="{{ url('/attendance/clock-out') }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm" @disabled($today === null || $today->check_in === null || $today->check_out !== null)><i class="bx bx-log-out-circle me-1"></i> Clock Out</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ url('/attendance/my?month='.$prev->month.'&year='.$prev->year) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-chevron-left"></i> {{ $prev->format('M Y') }}</a>
        <h5 class="mb-0">{{ $periodLabel }}</h5>
        <a href="{{ url('/attendance/my?month='.$next->month.'&year='.$next->year) }}" class="btn btn-sm btn-outline-secondary">{{ $next->format('M Y') }} <i class="bx bx-chevron-right"></i></a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ number_format($summary['present']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Present Days</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ number_format($summary['absent']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Absent Days</p>
            </div></div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ number_format($summary['leave']) }}</h3>
                <p class="text-muted mb-0 font-size-13">Leave Days</p>
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
                <p class="text-muted mb-0 font-size-13">Overtime Hours</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{ $periodLabel }} Record</h5>
            <span class="text-muted font-size-13">{{ $records->count() }} days marked</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Worked</th>
                            <th>Overtime</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td class="fw-medium">{{ $record->date->format('d M Y') }}</td>
                                <td class="text-muted">{{ $record->date->format('D') }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$record->status] ?? 'secondary' }}">{{ str_replace('-', ' ', ucfirst($record->status)) }}</span>
                                </td>
                                <td>{{ $record->check_in ? substr($record->check_in, 0, 5) : '—' }}</td>
                                <td>{{ $record->check_out ? substr($record->check_out, 0, 5) : '—' }}</td>
                                <td>{{ number_format((float) $record->worked_hours, 2) }}</td>
                                <td>{{ number_format((float) $record->overtime_hours, 2) }}</td>
                                <td class="text-muted font-size-13">{{ $record->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No attendance recorded for this month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
