@extends('layouts.app')

@section('title', 'Lead Pipeline')
@section('page-title', 'Lead Pipeline')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/leads') }}">Leads</a></li>
    <li class="breadcrumb-item active">Pipeline</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('leads.create'))
        <a href="{{ url('/leads/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Add Lead</a>
    @endif
    <a href="{{ url('/leads') }}" class="btn btn-outline-secondary btn-sm">List View</a>
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $colors = [
        'new' => 'primary',
        'contacted' => 'info',
        'qualified' => 'warning',
        'proposal' => 'secondary',
        'negotiation' => 'dark',
        'won' => 'success',
        'lost' => 'danger',
    ];
@endphp

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ url('/leads/pipeline') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, company, phone, email">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Assigned To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Anyone</option>
                        @foreach ($salespeople as $salesperson)
                            <option value="{{ $salesperson->id }}" @selected(request('assigned_to') == $salesperson->id)>{{ $salesperson->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        @foreach (\App\Models\Lead::PRIORITIES as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 flex-nowrap overflow-auto pb-2">
        @foreach ($statuses as $status)
            @php($leads = $columns->get($status, collect()))
            <div class="col" style="min-width: 290px;">
                <div class="card mb-0 h-100">
                    <div class="card-header d-flex align-items-center justify-content-between bg-{{ $colors[$status] ?? 'secondary' }}-subtle">
                        <div class="d-flex align-items-center">
                            <span class="fw-semibold text-{{ $colors[$status] ?? 'secondary' }}">{{ ucfirst($status) }}</span>
                            <span class="badge bg-{{ $colors[$status] ?? 'secondary' }} ms-2">{{ $leads->count() }}</span>
                        </div>
                        <span class="text-muted font-size-12">{{ \App\Support\Money::compact($leads->sum('estimated_value')) }}</span>
                    </div>
                    <div class="card-body cds-scroll p-2">
                        @forelse ($leads as $lead)
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex align-items-start justify-content-between">
                                    <a href="{{ url('/leads/'.$lead->id) }}" class="text-body fw-semibold font-size-14">{{ $lead->name }}</a>
                                    @php($priorityColor = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$lead->priority] ?? 'secondary')
                                    <span class="badge bg-{{ $priorityColor }}-subtle text-{{ $priorityColor }}">{{ ucfirst($lead->priority) }}</span>
                                </div>
                                <div class="text-muted font-size-12">{{ $lead->company ?? 'No company' }}</div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <span class="fw-semibold font-size-13">{{ $money($lead->estimated_value) }}</span>
                                    @if ($lead->assignedUser)
                                        <div class="d-flex align-items-center" title="{{ $lead->assignedUser->name }}">
                                            <img src="{{ $lead->assignedUser->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                            <span class="ms-1 font-size-12 text-muted">{{ \Illuminate\Support\Str::limit($lead->assignedUser->name, 12) }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Unassigned</span>
                                    @endif
                                </div>
                                @if ($lead->next_follow_up_at)
                                    <div class="text-muted font-size-12 mt-1"><i class="bx bx-time-five me-1"></i>{{ $lead->next_follow_up_at->format('d M Y') }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-muted py-4 mb-0 font-size-13">No leads</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
