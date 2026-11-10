@php
    $incoming = $message->isIncoming();
    $isMedia = in_array($message->type, ['image', 'video', 'audio', 'document'], true);
    $mediaUrl = filled($message->media_url) ? asset('storage/'.$message->media_url) : null;
    $rawLink = $mediaUrl === null && $isMedia ? data_get($message->raw, $message->type.'.link') : null;
    $time = ($message->sent_at ?? $message->created_at)?->format('d M H:i');
@endphp

<div class="d-flex mb-3 {{ $incoming ? '' : 'justify-content-end' }}" data-message-id="{{ $message->id }}">
    <div class="cds-chat-bubble {{ $incoming ? 'cds-chat-in' : 'cds-chat-out' }}">
        @if (filled($message->body))
            <div>{!! nl2br(e($message->body)) !!}</div>
        @endif

        @if (filled($message->caption) && $message->caption !== $message->body)
            <div class="font-size-13 {{ $incoming ? 'text-muted' : 'text-white-50' }}">{!! nl2br(e($message->caption)) !!}</div>
        @endif

        @if ($mediaUrl !== null)
            @if ($message->type === 'image')
                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                    <img src="{{ $mediaUrl }}" class="rounded mt-2" style="max-width: 240px;" alt="">
                </a>
            @elseif ($message->type === 'video')
                <video src="{{ $mediaUrl }}" class="rounded mt-2" style="max-width: 260px;" controls></video>
            @elseif ($message->type === 'audio')
                <audio src="{{ $mediaUrl }}" class="mt-2" controls></audio>
            @else
                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="d-block mt-2 text-decoration-underline">
                    <i class="bx bx-paperclip"></i> {{ $message->filename ?? 'Attachment' }}
                </a>
            @endif
        @elseif ($rawLink !== null)
            <a href="{{ $rawLink }}" target="_blank" rel="noopener" class="d-block mt-2 text-decoration-underline">
                <i class="bx bx-link-external"></i> {{ $message->filename ?? ucfirst((string) $message->type) }}
            </a>
        @elseif ($isMedia)
            <span class="d-inline-block mt-2 font-size-12 {{ $incoming ? 'text-muted' : 'text-white-50' }}">
                <i class="bx bx-image-alt"></i> {{ ucfirst((string) $message->type) }} not available
            </span>
        @endif

        <div class="font-size-11 mt-1 d-flex align-items-center gap-1 {{ $incoming ? 'text-muted' : 'text-white-50' }}">
            @if ($incoming)
                <i class="bx bxl-whatsapp"></i>
            @else
                @if (in_array($message->status, ['read', 'delivered'], true))
                    <i class="bx bx-check-double"></i>
                @elseif ($message->status === 'failed')
                    <i class="bx bx-error-circle"></i>
                @else
                    <i class="bx bx-check"></i>
                @endif
                <span>{{ ucfirst((string) $message->status) }}</span>
                <span>&middot;</span>
            @endif
            <span>{{ $time }}</span>
        </div>
    </div>
</div>
