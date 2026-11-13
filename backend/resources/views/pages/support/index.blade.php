@extends('layouts.app')

@section('title', 'Support Inbox')
@section('page-title', 'Support Inbox')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Support Inbox</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-danger">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Waiting for a human</span>
                    <h4 class="mb-0 text-danger">{{ number_format($counts['escalated']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Resolved today</span>
                    <h4 class="mb-0 text-success">{{ number_format($counts['resolved_today']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Converted to orders</span>
                    <h4 class="mb-0">{{ number_format($counts['converted']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Total conversations</span>
                    <h4 class="mb-0">{{ number_format($counts['total']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'escalated' ? 'active' : '' }}" href="{{ url('/support?status=escalated') }}">Waiting ({{ $counts['escalated'] }})</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'resolved' ? 'active' : '' }}" href="{{ url('/support?status=resolved') }}">Resolved</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ url('/support?status=all') }}">All</a>
                </li>
                <li class="nav-item ms-auto">
                    <form method="GET" action="{{ url('/support') }}" class="d-flex gap-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <select name="tag" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Filter by tag</option>
                            @foreach (\App\Models\BotConversation::SUGGESTED_TAGS as $suggested)
                                <option value="{{ $suggested }}" @selected($tag === $suggested)>{{ ucfirst($suggested) }}</option>
                            @endforeach
                        </select>
                    </form>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Last message</th>
                            <th>Lead</th>
                            <th>Waiting since</th>
                            <th>Handled by</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversations as $conversation)
                            <tr class="{{ $conversation->status === 'escalated' ? 'table-warning' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2">
                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger font-size-14">
                                                <i class="bx bx-user"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-size-14"><a href="{{ route('support.show', $conversation) }}" class="text-body">{{ $conversation->contact_name ?? $conversation->contact?->displayName() ?? 'Unknown' }}</a></h6>
                                            <span class="text-muted font-size-12">{{ $conversation->contact_phone ?? $conversation->contact?->phone }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-size-13">
                                    {{ \Illuminate\Support\Str::limit($conversation->summary ?? '—', 50) }}
                                    @if (! empty($conversation->tags))
                                        <div class="mt-1">
                                            @foreach ($conversation->tags as $conversationTag)
                                                <span class="badge bg-primary-subtle text-primary font-size-10">{{ $conversationTag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($conversation->lead)
                                        <a href="{{ route('leads.show', $conversation->lead) }}" class="badge bg-primary-subtle text-primary">{{ $conversation->lead->lead_no }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="font-size-13">
                                    @if ($conversation->escalated_at)
                                        <span class="{{ $conversation->status === 'escalated' && $conversation->escalated_at->diffInMinutes(now()) > 15 ? 'text-danger fw-medium' : '' }}">
                                            {{ $conversation->escalated_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $conversation->handler?->name ?? '—' }}</td>
                                <td>
                                    @if ($conversation->status === 'escalated')
                                        <span class="badge bg-danger-subtle text-danger">Waiting</span>
                                    @elseif ($conversation->status === 'resolved')
                                        <span class="badge bg-success-subtle text-success">Resolved</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($conversation->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ route('support.show', $conversation) }}" class="btn btn-sm btn-primary" title="Open"><i class="bx bx-chat"></i> Open</a>
                                    @if ($canManage && $conversation->status === 'escalated')
                                        <form action="{{ route('support.resolve', $conversation) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Resolve"><i class="bx bx-check"></i></button>
                                        </form>
                                        <form action="{{ route('support.hand-to-bot', $conversation) }}" method="POST" class="d-inline" onsubmit="return confirm('Hand this conversation back to the bot?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" title="Back to bot"><i class="bx bx-bot"></i></button>
                                        </form>
                                    @elseif ($canManage && $conversation->status === 'resolved')
                                        <form action="{{ route('support.reopen', $conversation) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" title="Reopen"><i class="bx bx-revision"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nothing waiting — the bot is handling everything. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $conversations->links() }}</div>
        </div>
    </div>
@endsection
