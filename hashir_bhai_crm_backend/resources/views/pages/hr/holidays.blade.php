@extends('layouts.app')

@section('title', 'Holidays')
@section('page-title', 'Holidays')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/employees') }}">Employees</a></li>
    <li class="breadcrumb-item active">Holidays</li>
@endsection

@section('page-actions')
    @can('holidays.manage')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#holiday-create-modal"><i class="bx bx-plus me-1"></i> Add Holiday</button>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <form class="d-flex align-items-end gap-2" method="GET" action="{{ url('/holidays') }}">
                    <div>
                        <label class="form-label mb-1">Year</label>
                        <input type="number" name="year" class="form-control" style="width: 120px;" min="2000" max="2100" value="{{ $year }}">
                    </div>
                    <button class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Apply</button>
                </form>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url('/holidays?year='.($year - 1)) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-chevron-left"></i> {{ $year - 1 }}</a>
                    <a href="{{ url('/holidays?year='.($year + 1)) }}" class="btn btn-sm btn-outline-secondary">{{ $year + 1 }} <i class="bx bx-chevron-right"></i></a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Holiday</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Paid</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($holidays as $holiday)
                            <tr>
                                <td>
                                    <h6 class="mb-0 font-size-14">{{ $holiday->name }}</h6>
                                    @if ($holiday->description)
                                        <span class="text-muted font-size-12">{{ $holiday->description }}</span>
                                    @endif
                                </td>
                                <td>{{ $holiday->date?->format('d M Y') }}</td>
                                <td><span class="text-muted">{{ $holiday->date?->format('l') }}</span></td>
                                <td>
                                    @if ($holiday->is_paid)
                                        <span class="badge bg-success-subtle text-success">Paid</span>
                                    @else
                                        <span class="badge bg-light text-dark">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @can('holidays.manage')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#holiday-edit-{{ $holiday->id }}" title="Edit"><i class="bx bx-edit"></i></button>
                                        <form action="{{ url('/holidays/'.$holiday->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this holiday?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No holidays for {{ $year }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('holidays.manage')
        <div class="modal fade" id="holiday-create-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ url('/holidays') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Holiday</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_paid" value="1" id="holiday-create-paid" checked>
                                    <label class="form-check-label" for="holiday-create-paid">Paid holiday</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Add Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @foreach ($holidays as $holiday)
        @can('holidays.manage')
            <div class="modal fade" id="holiday-edit-{{ $holiday->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ url('/holidays/'.$holiday->id) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Holiday — {{ $holiday->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="{{ $holiday->name }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" required value="{{ $holiday->date?->toDateString() }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" value="{{ $holiday->description }}">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_paid" value="1" id="holiday-edit-paid-{{ $holiday->id }}" @checked($holiday->is_paid)>
                                        <label class="form-check-label" for="holiday-edit-paid-{{ $holiday->id }}">Paid holiday</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary">Save Holiday</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
@endsection
