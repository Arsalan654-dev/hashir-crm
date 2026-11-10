<div class="d-flex mb-3 {{ $mine ? 'justify-content-end' : '' }}" data-message-id="{{ $message->id }}">
    <div class="cds-chat-bubble {{ $mine ? 'cds-chat-out' : 'cds-chat-in' }}">
        @unless ($mine)
            <div class="fw-semibold font-size-12 mb-1">{{ $message->user?->name ?? 'System' }}</div>
        @endunless

        @if ($message->body)
            <div>{!! nl2br(e($message->body)) !!}</div>
        @endif

        @if ($message->type === 'image' && $message->attachment_path)
            <img src="{{ asset('storage/'.$message->attachment_path) }}" class="rounded mt-2" style="max-width: 220px;" alt="">
        @elseif ($message->type === 'video' && $message->attachment_path)
            <video src="{{ asset('storage/'.$message->attachment_path) }}" class="rounded mt-2" style="max-width: 240px;" controls></video>
        @elseif ($message->type === 'audio' && $message->attachment_path)
            <audio src="{{ asset('storage/'.$message->attachment_path) }}" class="mt-2" controls></audio>
        @elseif ($message->attachment_path)
            <a href="{{ asset('storage/'.$message->attachment_path) }}" target="_blank" class="d-block mt-2 text-decoration-underline">
                <i class="bx bx-paperclip"></i> {{ $message->attachment_name ?? 'Attachment' }}
            </a>
        @endif

        <div class="font-size-11 mt-1 {{ $mine ? 'text-white-50' : 'text-muted' }}">
            {{ ($message->sent_at ?? $message->created_at)?->format('d M H:i') }}
        </div>
    </div>
</div>
