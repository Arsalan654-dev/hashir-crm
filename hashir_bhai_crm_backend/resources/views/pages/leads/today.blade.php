@extends('layouts.app')

@section('title', "Today's Leads")
@section('page-title', "Today's Leads — Daily Working Queue")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/leads') }}">Leads</a></li>
    <li class="breadcrumb-item active">Today</li>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-h-100 border-danger">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Overdue follow-ups</span>
                    <h4 class="mb-0 text-danger">{{ $overdue->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-h-100 border-warning">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Due today</span>
                    <h4 class="mb-0 text-warning">{{ $dueToday->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">No follow-up set</span>
                    <h4 class="mb-0">{{ $noFollowUp->count() }}</h4>
                </div>
            </div>
        </div>
    </div>

    @foreach ([
        ['title' => 'Overdue Follow-ups', 'leads' => $overdue, 'class' => 'danger'],
        ['title' => "Today's Follow-ups", 'leads' => $dueToday, 'class' => 'warning'],
        ['title' => 'Not Scheduled Yet', 'leads' => $noFollowUp, 'class' => 'secondary'],
    ] as $section)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <span class="badge bg-{{ $section['class'] }}-subtle text-{{ $section['class'] }} me-2">{{ $section['leads']->count() }}</span>
                    {{ $section['title'] }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Lead</th>
                                <th>Assigned To</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Follow-up</th>
                                <th>Value</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($section['leads'] as $lead)
                                <tr>
                                    <td>
                                        <h6 class="mb-0 font-size-14"><a href="{{ route('leads.show', $lead) }}" class="text-body">{{ $lead->name }}</a></h6>
                                        <span class="text-muted font-size-12">{{ $lead->lead_no }}{{ $lead->company ? ' · '.$lead->company : '' }}</span>
                                    </td>
                                    <td>
                                        @if ($lead->assignedUser)
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $lead->assignedUser->avatarUrl() }}" alt="" class="rounded-circle avatar-xs me-2">
                                                <span class="font-size-13">{{ $lead->assignedUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->phone ?? '—' }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ ucfirst($lead->status) }}</span></td>
                                    <td>
                                        @if ($lead->next_follow_up_at)
                                            <span class="{{ $lead->next_follow_up_at->isPast() ? 'text-danger fw-medium' : '' }}">
                                                {{ $lead->next_follow_up_at->format('d M, h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((float) $lead->estimated_value, 2) }} {{ $lead->currency }}</td>
                                    <td class="text-end cds-table-actions">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-primary" title="Open"><i class="bx bx-show"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Nothing here — all clear!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
