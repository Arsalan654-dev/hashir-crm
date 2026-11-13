@extends('layouts.app')

@section('title', 'Support — '.($conversation->contact_name ?? 'Conversation'))
@section('page-title', 'Support — '.($conversation->contact_name ?? 'Conversation'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/support') }}">Support Inbox</a></li>
    <li class="breadcrumb-item active">{{ $conversation->contact_name ?? 'Conversation' }}</li>
@endsection

@section('page-actions')
    @if ($canManage)
        @if ($conversation->status === 'escalated')
            <form action="{{ route('support.resolve', $conversation) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success btn-sm"><i class="bx bx-check me-1"></i> Resolve</button>
            </form>
            <form action="{{ route('support.hand-to-bot', $conversation) }}" method="POST" class="d-inline" onsubmit="return confirm('Hand back to the bot?');">
                @csrf
                <button class="btn btn-outline-secondary btn-sm"><i class="bx bx-bot me-1"></i> Back to bot</button>
            </form>
        @elseif ($conversation->status === 'resolved')
            <form action="{{ route('support.reopen', $conversation) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-warning btn-sm"><i class="bx bx-revision me-1"></i> Reopen</button>
            </form>
        @endif
    @endif
    @if ($contact)
        <a href="{{ route('whatsapp.show', $contact) }}" class="btn btn-outline-primary btn-sm"><i class="bxl-whatsapp me-1"></i> Full WhatsApp view</a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Conversation</h5>
                    @if ($conversation->status === 'escalated')
                        <span class="badge bg-danger-subtle text-danger">Waiting for a human</span>
                    @elseif ($conversation->status === 'resolved')
                        <span class="badge bg-success-subtle text-success">Resolved</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($conversation->status) }}</span>
                    @endif
                </div>
                <div class="card-body" style="max-height: 520px; overflow-y: auto;" id="support-thread">
                    @forelse ($messages as $message)
                        @include('partials.whatsapp-message', ['message' => $message])
                    @empty
                        <p class="text-center text-muted py-4 mb-0">No messages yet.</p>
                    @endforelse
                </div>
                @if ($canManage && $contact)
                    <div class="card-footer">
                        <form action="{{ route('support.reply', $conversation) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="body" class="form-control" placeholder="Type your reply to the customer..." required maxlength="4000">
                                <button class="btn btn-primary"><i class="bx bx-send me-1"></i> Send</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Customer</h5></div>
                <div class="card-body font-size-14">
                    <p class="mb-1 fw-medium">{{ $conversation->contact_name ?? $contact?->displayName() ?? 'Unknown' }}</p>
                    <p class="mb-1 text-muted">{{ $conversation->contact_phone ?? $contact?->phone }}</p>
                    @if ($conversation->escalated_at)
                        <p class="mb-1 text-muted font-size-13">Escalated {{ $conversation->escalated_at->diffForHumans() }}</p>
                    @endif
                    @if ($conversation->handler)
                        <p class="mb-1 text-muted font-size-13">Handled by {{ $conversation->handler->name }}</p>
                    @endif
                    @if ($conversation->lead)
                        <a href="{{ route('leads.show', $conversation->lead) }}" class="btn btn-sm btn-outline-primary mt-2"><i class="bx bx-target-lock me-1"></i> Lead {{ $conversation->lead->lead_no }}</a>
                    @endif
                </div>
            </div>

            @if ($conversation->summary)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Escalation Reason</h5></div>
                    <div class="card-body font-size-14">{{ $conversation->summary }}</div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Tags</h5></div>
                <div class="card-body">
                    <div class="mb-2">
                        @forelse ($conversation->tags ?? [] as $tag)
                            <span class="badge bg-primary-subtle text-primary me-1 mb-1">{{ $tag }}</span>
                        @empty
                            <span class="text-muted font-size-13">No tags yet.</span>
                        @endforelse
                    </div>
                    @if ($canManage)
                        <form action="{{ route('support.tags', $conversation) }}" method="POST" class="input-group input-group-sm">
                            @csrf
                            <input type="text" name="tags" class="form-control" list="support-tag-suggestions" value="{{ implode(', ', $conversation->tags ?? []) }}" placeholder="complaint, inquiry, order">
                            <button class="btn btn-outline-primary">Save</button>
                        </form>
                        <datalist id="support-tag-suggestions">
                            @foreach (\App\Models\BotConversation::SUGGESTED_TAGS as $tag)
                                <option value="{{ $tag }}"></option>
                            @endforeach
                        </datalist>
                        <div class="form-text">Comma separated — e.g. complaint, inquiry, order.</div>
                    @endif
                </div>
            </div>

            @if ($conversation->order_data)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Order Data</h5></div>
                    <div class="card-body">
                        <pre class="mb-0 font-size-12" style="white-space: pre-wrap;">{{ json_encode($conversation->order_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var thread = document.getElementById('support-thread');
        if (thread) { thread.scrollTop = thread.scrollHeight; }
    </script>
@endpush
