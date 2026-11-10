@extends('layouts.app')

@section('title', $email->subject ?: 'Email')
@section('page-title', 'Read Email')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/email-inbox') }}">Inbox</a></li>
    <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($email->subject ?: '(no subject)', 40) }}</li>
@endsection

@section('page-actions')
    <a href="{{ url('/email/compose?reply_to='.$email->id) }}" class="btn btn-primary btn-sm"><i class="bx bx-reply me-1"></i> Reply</a>
    <a href="{{ url('/email-inbox') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Back to Inbox</a>
    <form action="{{ url('/email/'.$email->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this email?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash me-1"></i> Delete</button>
    </form>
@endsection

@php
    $toRecipients = is_array($email->to_recipients) ? $email->to_recipients : [];
    $ccRecipients = is_array($email->cc_recipients) ? $email->cc_recipients : [];
    $attachments = is_array($email->attachments) ? $email->attachments : [];
@endphp

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom pb-3 mb-3">
                <div class="d-flex align-items-center">
                    @if ($email->avatar)
                        <img src="{{ $email->avatar }}" class="rounded-circle avatar-md" alt="">
                    @else
                        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 48px; height: 48px;">
                            {{ mb_strtoupper(mb_substr($email->from_name ?: $email->from_email ?: '?', 0, 1)) }}
                        </span>
                    @endif
                    <div class="ms-3">
                        <h5 class="mb-0">{{ $email->from_name ?: $email->from_email }}</h5>
                        <span class="text-muted font-size-13">{{ $email->from_email }}</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted font-size-13">
                        <i class="bx bx-time-five me-1"></i>
                        {{ $email->received_at?->format('d M Y H:i') ?? trim(($email->date_label ?? '').' '.($email->time_label ?? '')) }}
                    </div>
                    <div class="mt-1">
                        @if ($email->is_starred)
                            <span class="badge bg-warning-subtle text-warning"><i class="bx bxs-star me-1"></i> Starred</span>
                        @endif
                        @if ($email->is_important)
                            <span class="badge bg-danger-subtle text-danger">Important</span>
                        @endif
                        <span class="badge bg-light text-dark">{{ ucfirst($email->folder ?? 'inbox') }}</span>
                    </div>
                </div>
            </div>

            <h4 class="mb-2">{{ $email->subject ?: '(no subject)' }}</h4>

            <div class="mb-3">
                <p class="mb-1 font-size-13">
                    <span class="text-muted">To:</span>
                    {{ $toRecipients !== [] ? implode(', ', $toRecipients) : ($email->to_email ?? '—') }}
                </p>
                @if ($ccRecipients !== [])
                    <p class="mb-0 font-size-13"><span class="text-muted">Cc:</span> {{ implode(', ', $ccRecipients) }}</p>
                @endif
            </div>

            <div class="border-top pt-3">
                @if (filled($email->html_body))
                    <div>{!! $email->html_body !!}</div>
                @else
                    <p class="mb-0">{!! nl2br(e($email->body)) !!}</p>
                @endif
            </div>

            @if ($attachments !== [])
                <div class="border-top pt-3 mt-3">
                    <h6 class="mb-3"><i class="bx bx-paperclip me-1"></i> Attachments ({{ count($attachments) }})</h6>
                    @foreach ($attachments as $attachment)
                        <div class="d-flex align-items-center border rounded px-3 py-2 mb-2">
                            <i class="bx bx-file font-size-24 text-primary me-2"></i>
                            <div>
                                <div class="font-size-14 fw-medium">{{ $attachment['filename'] ?? 'Attachment' }}</div>
                                <span class="text-muted font-size-12">
                                    {{ $attachment['mime'] ?? 'file' }}
                                    @if (filled($attachment['size'] ?? null))
                                        &middot; {{ number_format(((int) $attachment['size']) / 1024, 1) }} KB
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
