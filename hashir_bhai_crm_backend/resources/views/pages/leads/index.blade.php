@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'Leads')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Leads</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('leads.create'))
        <a href="{{ url('/leads/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Add Lead</a>
    @endif
    <a href="{{ url('/leads/pipeline') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-columns me-1"></i> Pipeline</a>
    <a href="{{ url('/leads/export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-download me-1"></i> Export</a>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ url('/leads') }}" class="btn btn-sm btn-outline-secondary">All {{ number_format($counts['all'] ?? 0) }}</a>
        @foreach (\App\Models\Lead::STATUSES as $status)
            <a href="{{ url('/leads') }}?status={{ $status }}" class="btn btn-sm btn-outline-dark">
                {{ ucfirst($status) }} {{ number_format($counts[$status] ?? 0) }}
            </a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/leads') }}">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, company, phone, email, lead no">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Lead::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Source</label>
                    <select name="source" class="form-select">
                        <option value="">All sources</option>
                        @foreach (\App\Models\Lead::SOURCES as $source)
                            <option value="{{ $source }}" @selected(request('source') === $source)>{{ ucwords(str_replace('_', ' ', $source)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All priorities</option>
                        @foreach (\App\Models\Lead::PRIORITIES as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Assigned To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Anyone</option>
                        @foreach ($salespeople as $salesperson)
                            <option value="{{ $salesperson->id }}" @selected(request('assigned_to') == $salesperson->id)>{{ $salesperson->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead</th>
                            <th>Contact</th>
                            <th>Source</th>
                            <th class="text-end">Value</th>
                            <th>Assigned</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Follow Up</th>
                            <th class="text-end">Actions</th>
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
                                <td>
                                    @if ($lead->assignedUser)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $lead->assignedUser->avatarUrl() }}" class="rounded-circle avatar-xs" alt="">
                                            <span class="ms-2 font-size-13">{{ $lead->assignedUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Unassigned</span>
                                    @endif
                                </td>
                                <td>@include('partials.lead-status', ['status' => $lead->status])</td>
                                <td>
                                    @php($priorityColor = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$lead->priority] ?? 'secondary')
                                    <span class="badge bg-{{ $priorityColor }}-subtle text-{{ $priorityColor }}">{{ ucfirst($lead->priority) }}</span>
                                </td>
                                <td>
                                    <span class="text-muted font-size-13">{{ $lead->next_follow_up_at?->format('d M Y') ?? 'Not set' }}</span>
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/leads/'.$lead->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bx bx-show"></i></a>
                                    @if (auth()->user()->can('leads.assign'))
                                        <button type="button" class="btn btn-sm btn-outline-info" title="Assign"
                                            data-bs-toggle="modal" data-bs-target="#assignLeadModal"
                                            data-assign-url="{{ url('/leads/'.$lead->id.'/assign') }}"
                                            data-lead-name="{{ $lead->name }}"><i class="bx bx-user-plus"></i></button>
                                    @endif
                                    @if (auth()->user()->can('leads.convert') && $lead->status !== 'won')
                                        <form action="{{ url('/leads/'.$lead->id.'/convert') }}" method="POST" class="d-inline" onsubmit="return confirm('Convert this lead to a customer?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Convert"><i class="bx bx-transfer-alt"></i></button>
                                        </form>
                                    @endif
                                    @if (auth()->user()->can('leads.delete'))
                                        <form action="{{ url('/leads/'.$lead->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lead permanently?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No leads found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $leads->links() }}</div>
        </div>
    </div>

    <div class="modal fade" id="assignLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="" method="POST" id="assignLeadForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Lead</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-2" id="assignLeadName"></p>
                        <label class="form-label">Salesperson <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select salesperson</option>
                            @foreach ($salespeople as $salesperson)
                                <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Assign Lead</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('assignLeadModal');

            if (!modal) {
                return;
            }

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;

                if (!button) {
                    return;
                }

                document.getElementById('assignLeadForm').action = button.getAttribute('data-assign-url');
                document.getElementById('assignLeadName').textContent = button.getAttribute('data-lead-name');
            });
        });
    </script>
@endpush
