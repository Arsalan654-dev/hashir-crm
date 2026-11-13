@extends('layouts.app')

@section('title', $contact->displayName())
@section('page-title', $contact->displayName())
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/whatsapp') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">{{ $contact->displayName() }}</li>
@endsection

@section('page-actions')
    <a href="{{ url('/whatsapp') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Inbox</a>
    @if ($contact->lead)
        <a href="{{ url('/leads/'.$contact->lead->id) }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-target-lock me-1"></i> Open lead</a>
    @elseif (auth()->user()->can('leads.create'))
        <form action="{{ url('/whatsapp/contacts/'.$contact->id.'/lead') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Create lead</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="card mb-0">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-lg-4 col-xl-3 border-end d-none d-lg-block">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="bx bxl-whatsapp me-1"></i> Recent contacts</h6>
                        <a href="{{ url('/whatsapp/contacts') }}" class="btn btn-sm btn-link p-0">All</a>
                    </div>
                    <div class="cds-scroll" style="max-height: 640px;">
                        <ul class="list-unstyled mb-0">
                            @forelse ($contacts as $recent)
                                <li class="border-bottom">
                                    <a href="{{ url('/whatsapp/'.$recent->id) }}" class="d-flex align-items-center gap-2 px-3 py-2 text-reset text-decoration-none {{ $recent->id === $contact->id ? 'bg-primary-subtle' : '' }}">
                                        <span class="avatar-title rounded-circle bg-success-subtle text-success avatar-sm">{{ strtoupper(mb_substr($recent->displayName(), 0, 1)) }}</span>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-0 font-size-14 text-truncate">{{ $recent->displayName() }}</h6>
                                            <span class="text-muted font-size-11 text-truncate d-block">{{ $recent->last_message_at?->diffForHumans() ?? $recent->phone }}</span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-muted py-4">No contacts yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="col-lg-8 col-xl-9 d-flex flex-column">
                    <div class="p-3 border-bottom d-flex flex-wrap align-items-center gap-2">
                        <span class="avatar-title rounded-circle bg-success-subtle text-success avatar-md">{{ strtoupper(mb_substr($contact->displayName(), 0, 1)) }}</span>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $contact->displayName() }}</h5>
                            <span class="text-muted font-size-12">
                                <i class="bx bx-phone me-1"></i>{{ $contact->phone ?? $contact->wa_id }}
                                @if ($contact->company)
                                    <span class="mx-1">&middot;</span><i class="bx bx-buildings me-1"></i>{{ $contact->company }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            @if ($contact->lead)
                                @include('partials.lead-status', ['status' => $contact->lead->status])
                            @endif
                            @if ($contact->customer)
                                <a href="{{ url('/customers/'.$contact->customer->id) }}" class="badge bg-success-subtle text-success text-decoration-none">{{ $contact->customer->name }}</a>
                            @endif
                            @if ($contact->is_blocked)
                                <span class="badge bg-danger-subtle text-danger">Blocked</span>
                            @endif
                        </div>
                    </div>

                    @if (! $configured)
                        <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 m-3 mb-0">
                            <i class="bx bx-error-circle font-size-18"></i>
                            <span class="flex-grow-1">WhatsApp Cloud API is not configured, so no messages can be sent or received.</span>
                            @if (auth()->user()->can('whatsapp.settings'))
                                <a href="{{ url('/whatsapp/settings') }}" class="btn btn-sm btn-warning">Open settings</a>
                            @endif
                        </div>
                    @endif

                    <div id="cds-wa-thread" data-last-id="{{ $messages->last()?->id ?? 0 }}" class="p-3 bg-light flex-grow-1" style="height: 520px; overflow-y: auto;">
                        @forelse ($messages as $message)
                            @include('partials.whatsapp-message', ['message' => $message])
                        @empty
                            <p class="text-center text-muted py-5 mb-0">No messages yet.</p>
                        @endforelse
                    </div>

                    @if ($configured && auth()->user()->can('whatsapp.send'))
                        <form action="{{ url('/whatsapp/'.$contact->id.'/send') }}" method="POST" enctype="multipart/form-data" class="border-top p-3 d-flex align-items-end gap-2">
                            @csrf
                            <div class="flex-grow-1">
                                <textarea name="body" rows="1" class="form-control" placeholder="Type a WhatsApp message&hellip;" maxlength="4000">{{ old('body') }}</textarea>
                            </div>
                            <div>
                                <label class="btn btn-outline-secondary mb-0" title="Attach an image, video, audio or document">
                                    <i class="bx bx-paperclip"></i>
                                    <input type="file" name="attachment" class="d-none" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                </label>
                            </div>
                            <button class="btn btn-primary" type="submit"><i class="bx bx-send"></i></button>
                        </form>
                    @elseif (! auth()->user()->can('whatsapp.send'))
                        <div class="border-top p-3 text-muted font-size-13">
                            <i class="bx bx-lock me-1"></i> You do not have permission to send WhatsApp messages.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var thread = document.getElementById('cds-wa-thread');
            if (!thread) {
                return;
            }

            var lastId = Number(thread.getAttribute('data-last-id')) || 0;
            var endpoint = '{{ url('/whatsapp/'.$contact->id.'/messages') }}';

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
