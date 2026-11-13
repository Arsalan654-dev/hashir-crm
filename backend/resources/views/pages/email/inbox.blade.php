@extends('layouts.app')

@section('title', 'Inbox')
@section('page-title', 'Email Inbox')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Inbox</li>
@endsection

@section('page-actions')
    @if ($gmailConfigured && $account)
        <form action="{{ url('/gmail/sync') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-primary btn-sm"><i class="bx bx-refresh me-1"></i> Sync now</button>
        </form>
    @elseif (! $account)
        <a href="{{ url('/gmail/connect') }}" class="btn btn-outline-primary btn-sm"><i class="bx bxl-google me-1"></i> Connect Gmail</a>
    @endif
    <a href="{{ url('/gmail/settings') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-cog me-1"></i> Gmail Settings</a>
    @if (auth()->user()->can('email.send'))
        <a href="{{ url('/email/compose') }}" class="btn btn-primary btn-sm"><i class="bx bx-pencil me-1"></i> Compose</a>
    @endif
@endsection

@section('content')
    <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
        @foreach (['inbox' => 'Inbox', 'sent' => 'Sent', 'unread' => 'Unread', 'starred' => 'Starred'] as $key => $label)
            <li class="nav-item">
                <a href="{{ url('/email-inbox?folder='.$key) }}" class="nav-link {{ $folder === $key ? 'active' : '' }}">
                    {{ $label }}
                    <span class="badge bg-{{ $folder === $key ? 'primary' : 'secondary' }}-subtle text-{{ $folder === $key ? 'primary' : 'secondary' }} ms-1">{{ $folderCounts[$key] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    @if (! $account)
        <div class="alert alert-warning d-flex align-items-start">
            <i class="bx bx-error-circle font-size-20 me-2"></i>
            <div>
                No Gmail account is connected. New messages are saved inside the CRM only.
                @if ($gmailConfigured)
                    <a href="{{ url('/gmail/connect') }}" class="alert-link">Connect Gmail</a> to sync your real inbox.
                @else
                    <a href="{{ url('/gmail/settings') }}" class="alert-link">Add your Google credentials</a> first, then connect.
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center">
                <i class="bx bxl-gmail font-size-20 me-2"></i>
                <div>
                    <strong>{{ $account->email }}</strong>
                    <div class="font-size-12">Last sync: {{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}</div>
                </div>
            </div>
            @if ($gmailConfigured)
                <form action="{{ url('/gmail/sync') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-success"><i class="bx bx-refresh me-1"></i> Sync now</button>
                </form>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/email-inbox') }}">
                <input type="hidden" name="folder" value="{{ $folder }}">
                <div class="col-md-5">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Subject, sender or content">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Search</button>
                </div>
                @if (request()->filled('search'))
                    <div class="col-md-2">
                        <a href="{{ url('/email-inbox?folder='.$folder) }}" class="btn btn-light w-100">Clear</a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emails as $email)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($email->avatar)
                                            <img src="{{ $email->avatar }}" class="rounded-circle avatar-sm" alt="">
                                        @else
                                            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 36px; height: 36px;">
                                                {{ mb_strtoupper(mb_substr($email->from_name ?: $email->from_email ?: '?', 0, 1)) }}
                                            </span>
                                        @endif
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14 {{ $email->is_read ? '' : 'fw-bold' }}">
                                                <a href="{{ url('/email-read/'.$email->id) }}" class="text-body">{{ $email->from_name ?: $email->from_email }}</a>
                                            </h6>
                                            <span class="text-muted font-size-12">{{ $email->from_email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ url('/email-read/'.$email->id) }}" class="text-body {{ $email->is_read ? '' : 'fw-bold' }}">
                                        @if ($email->is_starred)
                                            <i class="bx bxs-star text-warning me-1"></i>
                                        @elseif (! $email->is_read)
                                            <i class="bx bxs-circle text-primary me-1"></i>
                                        @endif
                                        {{ $email->subject ?: '(no subject)' }}
                                    </a>
                                    <div class="text-muted font-size-12">{{ $email->teaser ?? $email->snippet ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="font-size-13">{{ $email->date_label ?? '—' }}</div>
                                    <span class="text-muted font-size-12">{{ $email->time_label ?? $email->received_at?->format('H:i') }}</span>
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/email-read/'.$email->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                    <form action="{{ url('/email/'.$email->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this email?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No emails found in this folder.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $emails->links() }}</div>
        </div>
    </div>
@endsection
