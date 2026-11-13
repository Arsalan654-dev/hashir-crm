@extends('layouts.app')

@section('title', 'Sales Teams')
@section('page-title', 'Sales Teams')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/sales') }}">Sales</a></li>
    <li class="breadcrumb-item active">Teams</li>
@endsection

@section('page-actions')
    @if ($canCreate)
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#team-create-modal"><i class="bx bx-plus me-1"></i> Create Team</button>
    @endif
@endsection

@section('content')
    <div class="row">
        @forelse ($teams as $entry)
            @php
                $team = $entry['team'];
            @endphp
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <span class="avatar-title rounded-circle bg-{{ $team->color ?? 'primary' }}-subtle text-{{ $team->color ?? 'primary' }} font-size-18">
                                    <i class="bx bx-group"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $team->name }}</h5>
                                <span class="text-muted font-size-13">
                                    Manager: {{ $team->manager?->name ?? 'Not assigned' }}
                                </span>
                            </div>
                            @if (! $team->is_active)
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </div>

                        @if ($team->description)
                            <p class="text-muted font-size-13 mb-3">{{ $team->description }}</p>
                        @endif

                        <div class="row text-center mb-3">
                            <div class="col-6 border-end">
                                <h5 class="mb-0">{{ number_format($entry['member_count']) }}</h5>
                                <span class="text-muted font-size-12">Members</span>
                            </div>
                            <div class="col-6">
                                <h5 class="mb-0">{{ number_format($entry['month_sales'], 2) }}</h5>
                                <span class="text-muted font-size-12">{{ $monthLabel }} sales</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach ($team->members->take(8) as $member)
                                <img src="{{ $member->avatarUrl() }}" alt="{{ $member->name }}" class="rounded-circle avatar-xs" title="{{ $member->name }}">
                            @endforeach
                            @if ($team->members->count() > 8)
                                <span class="badge bg-light text-dark align-self-center">+{{ $team->members->count() - 8 }}</span>
                            @endif
                        </div>

                        @if ($entry['can_manage'])
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#team-members-{{ $team->id }}">
                                    <i class="bx bx-user-plus me-1"></i> Members
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#team-edit-{{ $team->id }}" title="Edit"><i class="bx bx-edit"></i></button>
                                <form action="{{ route('teams.destroy', $team) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-group font-size-40 text-muted d-block mb-2"></i>
                        <h5>No sales teams yet</h5>
                        <p class="text-muted mb-3">Create Team A / Team B, assign a manager and add members.</p>
                        @if ($canCreate)
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#team-create-modal"><i class="bx bx-plus me-1"></i> Create Team</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($canCreate)
        <div class="modal fade" id="team-create-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ route('teams.store') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Sales Team</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Team Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Team A" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Manager</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">— Select manager —</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="North region sales team">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Colour</label>
                                <select name="color" class="form-select">
                                    @foreach (['primary', 'success', 'info', 'warning', 'danger', 'secondary'] as $color)
                                        <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Members</label>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                                    @foreach ($employees as $employee)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="members[]" value="{{ $employee->id }}" id="team-create-member-{{ $employee->id }}">
                                            <label class="form-check-label" for="team-create-member-{{ $employee->id }}">{{ $employee->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="team-create-active" checked>
                                    <label class="form-check-label" for="team-create-active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Create Team</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @foreach ($teams as $entry)
        @php
            $team = $entry['team'];
        @endphp
        @if ($entry['can_manage'])
            <div class="modal fade" id="team-edit-{{ $team->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('teams.update', $team) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit — {{ $team->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Team Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $team->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Manager</label>
                                    <select name="manager_id" class="form-select">
                                        <option value="">— Select manager —</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}" @selected($team->manager_id === $employee->id)>{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" value="{{ $team->description }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Colour</label>
                                    <select name="color" class="form-select">
                                        @foreach (['primary', 'success', 'info', 'warning', 'danger', 'secondary'] as $color)
                                            <option value="{{ $color }}" @selected($team->color === $color)>{{ ucfirst($color) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="team-edit-active-{{ $team->id }}" @checked($team->is_active)>
                                        <label class="form-check-label" for="team-edit-active-{{ $team->id }}">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save Team</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="team-members-{{ $team->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('teams.members.store', $team) }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Members — {{ $team->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @php
                                $memberIds = $team->memberships()->pluck('user_id')->all();
                            @endphp
                            <div class="border rounded p-2" style="max-height: 320px; overflow-y: auto;">
                                @foreach ($employees as $employee)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $employee->id }}" id="team-{{ $team->id }}-member-{{ $employee->id }}" @checked(in_array($employee->id, $memberIds, true))>
                                        <label class="form-check-label" for="team-{{ $team->id }}-member-{{ $employee->id }}">
                                            {{ $employee->name }}
                                            @if ($team->manager_id === $employee->id)
                                                <span class="badge bg-info-subtle text-info font-size-10">Manager</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save Members</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endsection
