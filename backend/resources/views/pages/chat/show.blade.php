@extends('layouts.app')

@section('title', $channel->displayNameFor(auth()->user()))
@section('page-title', $channel->displayNameFor(auth()->user()))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/chat') }}">Team Chat</a></li>
    <li class="breadcrumb-item active">{{ $channel->displayNameFor(auth()->user()) }}</li>
@endsection

@php
    $canAny = fn (string ...$permissions): bool => auth()->user()->hasAnyPermission($permissions);
    $memberIds = $channel->members->pluck('id');
    $candidates = $users->whereNotIn('id', $memberIds);
    $canManageMembers = $canAny('chat.manage_members', 'chat.create_group');
@endphp

@section('page-actions')
    <a href="{{ url('/chat') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> All Chats</a>
    @if ($channel->isGroup())
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-members-modal"><i class="bx bx-group me-1"></i> Members ({{ $channel->members->count() }})</button>
    @endif
@endsection

@section('content')
    <div class="card mb-0">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-lg-4 col-xl-3 border-end d-none d-lg-block">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="bx bx-message-dots me-1"></i> Conversations</h6>
                        <a href="{{ url('/chat') }}" class="btn btn-sm btn-link p-0">All</a>
                    </div>
                    <div class="cds-scroll" style="max-height: 640px;">
                        <ul class="list-unstyled mb-0">
                            @forelse ($channels as $sidebarChannel)
                                <li class="border-bottom">
                                    <a href="{{ url('/chat/'.$sidebarChannel->id) }}" class="d-flex align-items-center gap-2 px-3 py-2 text-reset text-decoration-none {{ $sidebarChannel->id === $channel->id ? 'bg-primary-subtle' : '' }}">
                                        <div class="flex-shrink-0">
                                            @if ($sidebarChannel->type === \App\Models\ChatChannel::TYPE_GROUP)
                                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary avatar-sm"><i class="bx bx-group"></i></span>
                                            @elseif ($sidebarChannel->type === \App\Models\ChatChannel::TYPE_WHATSAPP)
                                                <span class="avatar-title rounded-circle bg-success-subtle text-success avatar-sm"><i class="bx bxl-whatsapp"></i></span>
                                            @else
                                                <span class="avatar-title rounded-circle bg-info-subtle text-info avatar-sm">{{ strtoupper(mb_substr((string) $sidebarChannel->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-0 font-size-14 text-truncate">{{ $sidebarChannel->name }}</h6>
                                            <span class="text-muted font-size-11 text-truncate d-block">{{ $sidebarChannel->latestMessage->first()?->created_at?->diffForHumans(null, true) ?? 'No messages' }}</span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-muted py-4">No conversations yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="col-lg-8 col-xl-9 d-flex flex-column">
                    <div class="p-3 border-bottom d-flex flex-wrap align-items-center gap-2">
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $channel->displayNameFor(auth()->user()) }}</h5>
                            <span class="text-muted font-size-12">
                                @if ($channel->isGroup())
                                    {{ $channel->members->count() }} member{{ $channel->members->count() === 1 ? '' : 's' }}
                                @elseif ($isWhatsapp)
                                    WhatsApp conversation
                                @else
                                    Direct message
                                @endif
                            </span>
                        </div>
                        @if ($channel->isGroup())
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cds-members-modal"><i class="bx bx-group me-1"></i> Members</button>
                        @endif
                    </div>

                    @if ($isWhatsapp)
                        <div class="alert alert-info d-flex flex-wrap align-items-center gap-2 m-3 mb-0">
                            <i class="bx bxl-whatsapp font-size-18"></i>
                            <span class="flex-grow-1">This thread is linked to a WhatsApp contact.</span>
                            @if ($channel->whatsappContact)
                                <a href="{{ url('/whatsapp/'.$channel->whatsappContact->id) }}" class="btn btn-sm btn-outline-primary">Open in WhatsApp inbox</a>
                            @endif
                        </div>
                    @endif

                    <div id="cds-chat-thread" data-last-id="{{ $messages->last()?->id ?? 0 }}" class="p-3 bg-light flex-grow-1" style="height: 520px; overflow-y: auto;">
                        @forelse ($messages as $message)
                            @include('partials.chat-message', ['message' => $message, 'mine' => $message->user_id === auth()->id()])
                        @empty
                            <p class="text-center text-muted py-5 mb-0">No messages yet. Say hello.</p>
                        @endforelse
                    </div>

                    @if ($canAny('chat.send', 'chat.view'))
                        <form action="{{ url('/chat/'.$channel->id.'/messages') }}" method="POST" enctype="multipart/form-data" class="border-top p-3 d-flex align-items-end gap-2">
                            @csrf
                            <div class="flex-grow-1">
                                <textarea name="body" rows="1" class="form-control" placeholder="Type a message&hellip;" maxlength="5000">{{ old('body') }}</textarea>
                            </div>
                            <div>
                                <label class="btn btn-outline-secondary mb-0" title="Attach a file">
                                    <i class="bx bx-paperclip"></i>
                                    <input type="file" name="attachment" class="d-none">
                                </label>
                            </div>
                            <button class="btn btn-primary" type="submit"><i class="bx bx-send"></i></button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($channel->isGroup())
        <div class="modal fade" id="cds-members-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Group members</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($channel->members as $member)
                                <li class="d-flex align-items-center py-2 border-bottom">
                                    <img src="{{ $member->avatarUrl() }}" class="rounded-circle avatar-xs me-2" alt="">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 font-size-14">
                                            {{ $member->name }}
                                            @if ($member->id === $channel->created_by)
                                                <span class="badge bg-light text-dark">Owner</span>
                                            @endif
                                        </h6>
                                        <span class="text-muted font-size-12">{{ ucfirst((string) ($member->pivot->role ?? 'member')) }}</span>
                                    </div>
                                    @if ($canManageMembers && ($member->id !== $channel->created_by || auth()->id() === $channel->created_by))
                                        <form action="{{ url('/chat/'.$channel->id.'/members/'.$member->id) }}" method="POST" onsubmit="return confirm('Remove this member from the group?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Remove member"><i class="bx bx-user-minus"></i></button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if ($canManageMembers)
                            <form action="{{ url('/chat/'.$channel->id.'/members') }}" method="POST" class="mt-3">
                                @csrf
                                <label class="form-label">Add members</label>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                    @forelse ($candidates as $candidate)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="members[]" value="{{ $candidate->id }}" id="cds-add-member-{{ $candidate->id }}">
                                            <label class="form-check-label" for="cds-add-member-{{ $candidate->id }}">{{ $candidate->name }}</label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0 font-size-13">Everyone is already in this group.</p>
                                    @endforelse
                                </div>
                                @if ($candidates->isNotEmpty())
                                    <button class="btn btn-primary btn-sm mt-2"><i class="bx bx-user-plus me-1"></i> Add to group</button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            var thread = document.getElementById('cds-chat-thread');
            if (!thread) {
                return;
            }

            var lastId = Number(thread.getAttribute('data-last-id')) || 0;
            var endpoint = '{{ url('/chat/'.$channel->id.'/messages') }}';

            function scrollToBottom() {
                thread.scrollTop = thread.scrollHeight;
            }

            scrollToBottom();

            function poll() {
                window.cdsFetch(endpoint + '?after=' + lastId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) { return response.ok ? response.json() : null; })
                    .then(function (data) {
                        if (!data || !data.messages || !data.messages.length) {
                            return;
                        }
                        data.messages.forEach(function (message) {
                            thread.insertAdjacentHTML('beforeend', message.html);
                            lastId = Math.max(lastId, Number(message.id) || 0);
                        });
                        scrollToBottom();
                    })
                    .catch(function () {});
            }

            window.setInterval(poll, 7000);
        })();
    </script>
@endpush
