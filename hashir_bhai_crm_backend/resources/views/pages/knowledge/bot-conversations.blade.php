@extends('layouts.app')

@section('title', 'Bot Conversations')
@section('page-title', 'Bot Conversations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Bot Conversations</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('bot.settings'))
        <a href="{{ url('/bot/settings') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-cog me-1"></i> Bot Settings</a>
    @endif
    @if (auth()->user()->can('knowledge.view'))
        <a href="{{ url('/knowledge') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-book me-1"></i> Knowledge Base</a>
    @endif
@endsection

@php
    $statusColors = ['active' => 'primary', 'converted' => 'success', 'closed' => 'secondary', 'abandoned' => 'danger'];
@endphp

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-conversation"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">Total Conversations</p>
                            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-check-shield"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">Converted</p>
                            <h4 class="mb-0">{{ number_format($stats['converted']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-cart"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">With Orders</p>
                            <h4 class="mb-0">{{ number_format($stats['withOrders']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-6">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-calendar-event"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">Today</p>
                            <h4 class="mb-0">{{ number_format($stats['today']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/bot/conversations') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Contact, phone or summary">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="converted" @selected(request('status') === 'converted')>Converted</option>
                        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                        <option value="abandoned" @selected(request('status') === 'abandoned')>Abandoned</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Filter</button>
                </div>
                @if (request()->filled('search') || request()->filled('status'))
                    <div class="col-md-2">
                        <a href="{{ url('/bot/conversations') }}" class="btn btn-light w-100">Clear</a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Language</th>
                            <th>Linked Records</th>
                            <th>Started</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversations as $conversation)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 36px; height: 36px;">
                                            {{ mb_strtoupper(mb_substr($conversation->contact_name ?: $conversation->contact_phone ?: '?', 0, 1)) }}
                                        </span>
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">
                                                <a href="{{ url('/bot/conversations/'.$conversation->id) }}" class="text-body">{{ $conversation->contact_name ?: 'Unknown contact' }}</a>
                                            </h6>
                                            <span class="text-muted font-size-12">{{ $conversation->contact_phone ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$conversation->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$conversation->status] ?? 'secondary' }}">
                                        {{ ucfirst($conversation->status ?? 'unknown') }}
                                    </span>
                                </td>
                                <td>{{ $conversation->language ? strtoupper($conversation->language) : '—' }}</td>
                                <td>
                                    @if ($conversation->lead)
                                        <a href="{{ url('/leads/'.$conversation->lead->id) }}" class="badge bg-info-subtle text-info text-decoration-none">{{ $conversation->lead->lead_no }}</a>
                                    @endif
                                    @if ($conversation->sale)
                                        <a href="{{ url('/sales/'.$conversation->sale->id) }}" class="badge bg-success-subtle text-success text-decoration-none">{{ $conversation->sale->sale_no }}</a>
                                    @endif
                                    @if ($conversation->invoice)
                                        <a href="{{ url('/invoices/'.$conversation->invoice->id) }}" class="badge bg-warning-subtle text-warning text-decoration-none">{{ $conversation->invoice->invoice_no }}</a>
                                    @endif
                                    @if (! $conversation->lead && ! $conversation->sale && ! $conversation->invoice)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-size-13">{{ ($conversation->started_at ?? $conversation->created_at)?->format('d M Y') ?? '—' }}</div>
                                    <span class="text-muted font-size-12">{{ ($conversation->started_at ?? $conversation->created_at)?->diffForHumans() }}</span>
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/bot/conversations/'.$conversation->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No bot conversations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $conversations->links() }}</div>
        </div>
    </div>
@endsection
