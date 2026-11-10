@extends('layouts.app')

@section('title', 'WhatsApp Broadcast')
@section('page-title', 'WhatsApp Broadcast')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/whatsapp') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">Broadcast</li>
@endsection

@section('content')
    @if (! $configured)
        <div class="alert alert-warning d-flex align-items-center">
            <i class="bx bx-error-circle font-size-20 me-2"></i>
            <div>WhatsApp is not configured yet. Add your credentials in <a href="{{ url('/whatsapp/settings') }}" class="alert-link">WhatsApp settings</a> before sending broadcasts.</div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">New Broadcast</h5></div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.broadcast.send') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Campaign title (internal)</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Eid Offer July">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required maxlength="4000" placeholder="Assalam-o-Alaikum! Aaj hamari special deal...">{{ old('message') }}</textarea>
                            <div class="form-text">Tip: WhatsApp only allows free-form messages within 24 hours of the customer's last message. Outside that window, message templates are required.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Audience <span class="text-danger">*</span></label>
                            <select name="audience" class="form-select" required>
                                @foreach ($audiences as $value => $label)
                                    <option value="{{ $value }}" @selected(old('audience') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maximum recipients</label>
                            <input type="number" name="limit" class="form-control" value="{{ old('limit', 100) }}" min="1" max="500">
                            <div class="form-text">Send in small batches (max 500) to protect your number from spam flags.</div>
                        </div>
                        <button class="btn btn-primary w-100" @disabled(! $configured)><i class="bx bx-send me-1"></i> Send Broadcast</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Broadcast History</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Campaign</th>
                                    <th>Message</th>
                                    <th class="text-end">Sent</th>
                                    <th class="text-end">Failed</th>
                                    <th>Status</th>
                                    <th>By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($broadcasts as $broadcast)
                                    <tr>
                                        <td>{{ $broadcast->title ?? '—' }}</td>
                                        <td class="font-size-13">{{ \Illuminate\Support\Str::limit($broadcast->message, 50) }}</td>
                                        <td class="text-end">{{ $broadcast->sent_count }} / {{ $broadcast->recipients_count }}</td>
                                        <td class="text-end {{ $broadcast->failed_count > 0 ? 'text-danger fw-medium' : '' }}">{{ $broadcast->failed_count }}</td>
                                        <td><span class="badge bg-{{ $broadcast->statusColor() }}-subtle text-{{ $broadcast->statusColor() }}">{{ ucfirst($broadcast->status) }}</span></td>
                                        <td>{{ $broadcast->user?->name ?? '—' }}</td>
                                        <td class="text-muted font-size-12">{{ $broadcast->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No broadcasts sent yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $broadcasts->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
