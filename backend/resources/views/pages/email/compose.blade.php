@extends('layouts.app')

@section('title', 'Compose')
@section('page-title', 'Compose Email')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/email-inbox') }}">Inbox</a></li>
    <li class="breadcrumb-item active">Compose</li>
@endsection

@php
    $replyToAddress = old('to', $replyTo?->from_email);
    $replySubject = old('subject', $replyTo?->subject);
    if ($replySubject !== null && $replySubject !== '' && ! \Illuminate\Support\Str::startsWith($replySubject, 'Re:')) {
        $replySubject = 'Re: '.$replySubject;
    }
@endphp

@section('content')
    @if (! $gmailConfigured)
        <div class="alert alert-warning d-flex align-items-start">
            <i class="bx bx-error-circle font-size-20 me-2"></i>
            <div>
                Gmail is not connected yet. This message will only be saved as a draft inside the CRM and will not actually be sent.
                <a href="{{ url('/gmail/settings') }}" class="alert-link">Connect a Gmail account</a> to enable sending.
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0"><i class="bx bx-pencil me-1"></i> New Message</h5>
            @if ($account)
                <span class="text-muted font-size-13">Sending as <strong>{{ $account->email }}</strong></span>
            @else
                <span class="badge bg-warning-subtle text-warning">No Gmail account connected</span>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ url('/email/compose') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">To <span class="text-danger">*</span></label>
                        <input type="email" name="to" class="form-control" value="{{ $replyToAddress }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cc</label>
                        <input type="text" name="cc" class="form-control" value="{{ old('cc') }}" placeholder="email@example.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bcc</label>
                        <input type="text" name="bcc" class="form-control" value="{{ old('bcc') }}" placeholder="email@example.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="{{ $replySubject }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="12" required>{{ old('body') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                        <small class="text-muted">Maximum 10 MB.</small>
                    </div>
                </div>
                <div class="border-top mt-3 pt-3 text-end">
                    <a href="{{ url('/email-inbox') }}" class="btn btn-light">Cancel</a>
                    <button class="btn btn-primary"><i class="bx bx-send me-1"></i> Send Email</button>
                </div>
            </form>
        </div>
    </div>
@endsection
