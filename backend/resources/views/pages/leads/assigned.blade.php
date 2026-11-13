@extends('layouts.app')

@section('title', 'Assignment Queue')
@section('page-title', 'Assignment Queue')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/leads') }}">Leads</a></li>
    <li class="breadcrumb-item active">Assignment Queue</li>
@endsection

@section('page-actions')
    <a href="{{ url('/leads') }}" class="btn btn-outline-secondary btn-sm">All Leads</a>
    <a href="{{ url('/leads/pipeline') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-columns me-1"></i> Pipeline</a>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-warning">{{ number_format($leads->total()) }}</h3>
                <p class="text-muted mb-0 font-size-13">Leads Awaiting Assignment</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0"><div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ $salespeople->count() }}</h3>
                <p class="text-muted mb-0 font-size-13">Available Salespeople</p>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Unassigned &amp; New Leads</h5>
            <span class="text-muted font-size-12">Assign each lead to a salesperson</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead</th>
                            <th>Contact</th>
                            <th>Source</th>
                            <th class="text-end">Value</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th style="min-width: 260px;">Assign To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>
                                    <a href="{{ url('/leads/'.$lead->id) }}" class="text-body fw-semibold">{{ $lead->name }}</a>
                                    <div class="text-muted font-size-12">{{ $lead->lead_no }} &middot; {{ $lead->company ?? 'No company' }}</div>
                                </td>
                                <td>
                                    <div class="font-size-13">{{ $lead->phone ?? '—' }}</div>
                                    <span class="text-muted font-size-12">{{ $lead->email ?? 'No email' }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', $lead->source)) }}</span></td>
                                <td class="text-end fw-semibold">{{ $money($lead->estimated_value) }}</td>
                                <td>@include('partials.lead-status', ['status' => $lead->status])</td>
                                <td>
                                    @php($priorityColor = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$lead->priority] ?? 'secondary')
                                    <span class="badge bg-{{ $priorityColor }}-subtle text-{{ $priorityColor }}">{{ ucfirst($lead->priority) }}</span>
                                </td>
                                <td>
                                    @if (auth()->user()->can('leads.assign'))
                                        <form action="{{ url('/leads/'.$lead->id.'/assign') }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            <select name="assigned_to" class="form-select form-select-sm" required>
                                                <option value="">Select salesperson</option>
                                                @foreach ($salespeople as $salesperson)
                                                    <option value="{{ $salesperson->id }}" @selected($lead->assigned_to == $salesperson->id)>{{ $salesperson->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-primary text-nowrap"><i class="bx bx-user-plus me-1"></i> Assign</button>
                                        </form>
                                    @else
                                        <span class="text-muted font-size-13">No permission</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nothing to assign. Every lead has an owner.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $leads->links() }}</div>
        </div>
    </div>
@endsection
