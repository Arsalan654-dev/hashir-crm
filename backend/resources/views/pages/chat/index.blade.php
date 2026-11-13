@extends('layouts.app')

@section('title', 'Team Chat')
@section('page-title', 'Team Chat')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Team Chat</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('chat.create_group'))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-group-modal"><i class="bx bx-group me-1"></i> New Group</button>
    @endif
@endsection

@php
    $canAny = fn (string ...$permissions): bool => auth()->user()->hasAnyPermission($permissions);
    $preview = function ($message): string {
        if ($message === null) {
            return 'No messages yet';
        }
        if (filled($message->body)) {
            return \Illuminate\Support\Str::limit((string) $message->body, 48);
        }
        return match ($message->type) {
            'image' => 'Photo',
            'video' => 'Video',
            'audio' => 'Voice message',
            'document' => (string) ($message->attachment_name ?? 'Document'),
            default => ucfirst((string) $message->type),
        };
    };
    $isUnread = function ($channel): bool {
        $member = $channel->members->firstWhere('id', auth()->id());
        $latest = $channel->latestMessage->first();
        if ($member === null || $latest === null || $latest->user_id === auth()->id()) {
            return false;
        }
        return $member->pivot->last_read_at === null
            || $latest->created_at->greaterThan(\Illuminate\Support\Carbon::parse($member->pivot->last_read_at));
    };
@endphp

@section('content')
    @if ($unreadTotal > 0)
        <div class="alert alert-info d-flex align-items-center gap-2 py-2">
            <i class="bx bx-envelope font-size-18"></i>
            <span class="flex-grow-1">You have <strong>{{ number_format($unreadTotal) }}</strong> unread message{{ $unreadTotal === 1 ? '' : 's' }}.</span>
        </div>
    @endif

    <div class="card mb-0">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-lg-5 col-xl-4 border-end">
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="mb-0"><i class="bx bx-message-dots me-1"></i> Conversations</h5>
                            <span class="badge bg-primary-subtle text-primary">{{ number_format($channels->count()) }}</span>
                        </div>

                        @if ($users->isNotEmpty())
                            <form method="GET" action="{{ url('/chat/direct') }}" id="cds-direct-form">
                                <div class="input-group input-group-sm">
                                    <select id="cds-direct-user" class="form-select">
                                        <option value="">New direct message&hellip;</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary" type="submit"><i class="bx bx-message-square-add"></i></button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <div class="cds-scroll" style="max-height: 620px;">
                        <ul class="list-unstyled mb-0">
                            @forelse ($channels as $channel)
                                @php($latest = $channel->latestMessage->first())
                                <li class="border-bottom">
                                    <a href="{{ url('/chat/'.$channel->id) }}" class="d-flex align-items-center gap-2 px-3 py-2 text-reset text-decoration-none">
                                        <div class="flex-shrink-0">
                                            @if ($channel->type === \App\Models\ChatChannel::TYPE_GROUP)
                                                <div class="d-flex align-items-center">
                                                    @foreach ($channel->members->take(2) as $member)
                                                        <img src="{{ $member->avatarUrl() }}" class="rounded-circle avatar-xs border border-2 border-white" style="margin-left: {{ $loop->first ? '0' : '-10px' }};" alt="">
                                                    @endforeach
                                                </div>
                                            @elseif ($channel->type === \App\Models\ChatChannel::TYPE_WHATSAPP)
                                                <span class="avatar-title rounded-circle bg-success-subtle text-success avatar-sm"><i class="bx bxl-whatsapp"></i></span>
                                            @else
                                                <img src="{{ $channel->members->firstWhere('id', '!=', auth()->id())?->avatarUrl() ?? asset('assets/images/users/avatar-1.jpg') }}" class="rounded-circle avatar-sm" alt="">
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="mb-0 font-size-14 text-truncate">{{ $channel->displayNameFor(auth()->user()) }}</h6>
                                                <span class="text-muted font-size-11 ms-2">{{ $latest?->created_at?->diffForHumans(null, true) }}</span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="text-muted font-size-12 text-truncate">
                                                    @if ($latest && $latest->user_id !== auth()->id() && $latest->user){{ $latest->user->name }}: @endif{{ $preview($latest) }}
                                                </span>
                                                @if ($isUnread($channel))
                                                    <span class="badge bg-danger rounded-pill ms-2">New</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-muted py-5 px-3">
                                    <i class="bx bx-message-square-dots font-size-32 d-block mb-2"></i>
                                    No conversations yet. Start a direct message or create a group.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="col-lg-7 col-xl-8 d-none d-lg-flex flex-column align-items-center justify-content-center text-center p-5">
                    <i class="bx bx-message-rounded-dots font-size-48 text-primary"></i>
                    <h5 class="mt-3 mb-1">Pick a conversation</h5>
                    <p class="text-muted mb-0">Select a chat from the list, start a direct message, or create a group to begin.</p>
                </div>
            </div>
        </div>
    </div>

    @if ($canAny('chat.create_group'))
        <div class="modal fade" id="cds-group-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ url('/chat/groups') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Group name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control" maxlength="500">{{ old('description') }}</textarea>
                        </div>
                        <label class="form-label">Members</label>
                        <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                            @forelse ($users as $user)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="members[]" value="{{ $user->id }}" id="cds-group-member-{{ $user->id }}">
                                    <label class="form-check-label" for="cds-group-member-{{ $user->id }}">{{ $user->name }}</label>
                                </div>
                            @empty
                                <p class="text-muted mb-0 font-size-13">No other active users to add.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary"><i class="bx bx-group me-1"></i> Create group</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('cds-direct-form');
            if (!form) {
                return;
            }
            form.addEventListener('submit', function (event) {
                var select = document.getElementById('cds-direct-user');
                if (!select || !select.value) {
                    event.preventDefault();
                    return;
                }
                form.action = '{{ url('/chat/direct') }}/' + select.value;
            });
        })();
    </script>
@endpush
