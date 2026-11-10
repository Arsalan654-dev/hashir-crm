@extends('layouts.app')

@section('title', 'Team Targets')
@section('page-title', 'Team Targets')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Targets</li>
@endsection

@section('page-actions')
    @can('targets.manage')
        <a href="{{ url('/targets/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Set Target</a>
    @endcan
    <a href="{{ url('/targets/my') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-flag me-1"></i> My Targets</a>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        @forelse ($leaderboard as $entry)
            @if ($loop->index >= 8)
                @break
            @endif
            <div class="col-xl-3 col-md-6">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $entry['user']?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                            <div class="ms-2 flex-1">
                                <h6 class="mb-0 font-size-14">{{ $entry['user']?->name ?? 'Unknown' }}</h6>
                                <span class="text-muted font-size-12">Rank #{{ $loop->iteration }} of this month</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between font-size-13">
                            <span class="text-muted">Achieved / Target</span>
                            <span class="fw-semibold">{{ \App\Support\Money::compact($entry['achieved']) }} / {{ \App\Support\Money::compact($entry['target']) }}</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-{{ $entry['progress'] >= 100 ? 'success' : ($entry['progress'] >= 50 ? 'primary' : 'warning') }}" style="width: {{ min(100, $entry['progress']) }}%"></div>
                        </div>
                        <div class="text-muted font-size-12 mt-1">{{ \App\Support\Money::percent($entry['progress']) }} complete</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card mb-0"><div class="card-body text-center text-muted py-4">No targets set for this month yet.</div></div>
            </div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/targets') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Employee</label>
                    <select name="user_id" class="form-select">
                        <option value="">All employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('user_id') == $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Target</th>
                            <th>Period</th>
                            <th class="text-end">Target Amount</th>
                            <th class="text-end">Achieved</th>
                            <th style="min-width: 160px;">Progress</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($targets as $target)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $target->user?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $target->user?->name ?? 'Unknown' }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $target->title ?: ucfirst($target->period_type).' Target' }}</span>
                                    @if ($target->notes)
                                        <div class="text-muted font-size-12">{{ $target->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ ucfirst($target->period_type) }}</span>
                                    <div class="text-muted font-size-12">{{ $target->period_start->format('d M Y') }} → {{ $target->period_end->format('d M Y') }}</div>
                                </td>
                                <td class="text-end fw-semibold">{{ \App\Support\Money::format($target->target_amount) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($target->achievedAmount()) }}</td>
                                <td>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $target->progressPercent() >= 100 ? 'success' : 'primary' }}" style="width: {{ min(100, $target->progressPercent()) }}%"></div>
                                    </div>
                                    <span class="text-muted font-size-12">{{ \App\Support\Money::percent($target->progressPercent()) }}</span>
                                </td>
                                <td class="text-end">
                                    @if ((float) $target->bonus_amount > 0)
                                        <span class="text-success fw-semibold">{{ \App\Support\Money::format($target->bonus_amount) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @can('targets.manage')
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#cds-target-edit"
                                            data-action="{{ url('/targets/'.$target->id) }}"
                                            data-user="{{ $target->user_id }}"
                                            data-period="{{ $target->period_type }}"
                                            data-start="{{ $target->period_start->toDateString() }}"
                                            data-end="{{ $target->period_end->toDateString() }}"
                                            data-amount="{{ $target->target_amount }}"
                                            data-orders="{{ $target->target_orders }}"
                                            data-bonus="{{ $target->bonus_amount }}"
                                            data-title="{{ $target->title }}"
                                            data-notes="{{ $target->notes }}">Edit</button>
                                        <form action="{{ url('/targets/'.$target->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this target?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No targets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $targets->links() }}</div>
        </div>
    </div>

    @can('targets.manage')
        <div class="modal fade" id="cds-target-edit" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form id="cds-target-edit-form" action="{{ url('/targets') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Target</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                                    <select name="user_id" class="form-select" required>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Period Type <span class="text-danger">*</span></label>
                                    <select name="period_type" class="form-select" required>
                                        @foreach (\App\Models\Target::PERIODS as $period)
                                            <option value="{{ $period }}">{{ ucfirst($period) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Target Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="target_amount" step="0.01" min="0" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Period Start <span class="text-danger">*</span></label>
                                    <input type="date" name="period_start" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Period End <span class="text-danger">*</span></label>
                                    <input type="date" name="period_end" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Target Orders</label>
                                    <input type="number" name="target_orders" min="0" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bonus Amount</label>
                                    <input type="number" name="bonus_amount" step="0.01" min="0" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" maxlength="255">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Target</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script>
        (function () {
            var editModal = document.getElementById('cds-target-edit');

            if (!editModal) { return; }

            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var form = document.getElementById('cds-target-edit-form');
                form.setAttribute('action', button.getAttribute('data-action'));
                form.querySelector('[name="user_id"]').value = button.getAttribute('data-user') || '';
                form.querySelector('[name="period_type"]').value = button.getAttribute('data-period') || 'monthly';
                form.querySelector('[name="period_start"]').value = button.getAttribute('data-start') || '';
                form.querySelector('[name="period_end"]').value = button.getAttribute('data-end') || '';
                form.querySelector('[name="target_amount"]').value = button.getAttribute('data-amount') || '';
                form.querySelector('[name="target_orders"]').value = button.getAttribute('data-orders') || '';
                form.querySelector('[name="bonus_amount"]').value = button.getAttribute('data-bonus') || '';
                form.querySelector('[name="title"]').value = button.getAttribute('data-title') || '';
                form.querySelector('[name="notes"]').value = button.getAttribute('data-notes') || '';
            });
        })();
    </script>
@endpush
