@extends('layouts.app')

@section('title', 'Bot Conversation')
@section('page-title', 'Bot Conversation — '.($conversation->contact_name ?: 'Unknown'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/bot/conversations') }}">Bot Conversations</a></li>
    <li class="breadcrumb-item active">#{{ $conversation->id }}</li>
@endsection

@section('page-actions')
    <a href="{{ url('/bot/conversations') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Back to Conversations</a>
@endsection

@php
    $statusColors = ['active' => 'primary', 'converted' => 'success', 'closed' => 'secondary', 'abandoned' => 'danger'];
    $statusColor = $statusColors[$conversation->status] ?? 'secondary';
    $transcript = is_array($conversation->transcript) ? $conversation->transcript : [];
    $orderData = is_array($conversation->order_data) ? $conversation->order_data : [];
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 48px; height: 48px;">
                            {{ mb_strtoupper(mb_substr($conversation->contact_name ?: $conversation->contact_phone ?: '?', 0, 1)) }}
                        </span>
                        <div class="ms-3">
                            <h5 class="mb-0">{{ $conversation->contact_name ?: 'Unknown contact' }}</h5>
                            <span class="text-muted font-size-13">{{ $conversation->contact_phone ?? 'No phone' }}</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($conversation->status ?? 'unknown') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Language</span>
                        <span>{{ $conversation->language ? strtoupper($conversation->language) : '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Started</span>
                        <span>{{ ($conversation->started_at ?? $conversation->created_at)?->format('d M Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Ended</span>
                        <span>{{ $conversation->ended_at?->format('d M Y H:i') ?? 'In progress' }}</span>
                    </div>
                </div>
            </div>

            @if ($conversation->contact)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">WhatsApp Contact</h5></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0 font-size-14">{{ $conversation->contact->displayName() }}</h6>
                                <span class="text-muted font-size-12">{{ $conversation->contact->phone ?? $conversation->contact->wa_id }}</span>
                            </div>
                            <a href="{{ url('/whatsapp/'.$conversation->contact->id) }}" class="btn btn-sm btn-outline-success"><i class="bx bxl-whatsapp me-1"></i> Open</a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($conversation->summary)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Summary</h5></div>
                    <div class="card-body">
                        <p class="mb-0 font-size-13">{{ $conversation->summary }}</p>
                    </div>
                </div>
            @endif

            @if ($conversation->lead || $conversation->sale || $conversation->invoice)
                <div class="card mb-0">
                    <div class="card-header"><h5 class="card-title mb-0">Linked Records</h5></div>
                    <div class="card-body">
                        @if ($conversation->lead)
                            <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <div>
                                    <span class="text-muted font-size-12">Lead</span>
                                    <h6 class="mb-0 font-size-14">{{ $conversation->lead->lead_no }} — {{ $conversation->lead->name }}</h6>
                                </div>
                                <a href="{{ url('/leads/'.$conversation->lead->id) }}" class="btn btn-sm btn-outline-info">View Lead</a>
                            </div>
                        @endif
                        @if ($conversation->sale)
                            <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <div>
                                    <span class="text-muted font-size-12">Sale</span>
                                    <h6 class="mb-0 font-size-14">{{ $conversation->sale->sale_no }}</h6>
                                </div>
                                <a href="{{ url('/sales/'.$conversation->sale->id) }}" class="btn btn-sm btn-outline-success">View Sale</a>
                            </div>
                        @endif
                        @if ($conversation->invoice)
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div>
                                    <span class="text-muted font-size-12">Invoice</span>
                                    <h6 class="mb-0 font-size-14">{{ $conversation->invoice->invoice_no }}</h6>
                                </div>
                                <a href="{{ url('/invoices/'.$conversation->invoice->id) }}" class="btn btn-sm btn-outline-warning">View Invoice</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            @if ($orderData !== [])
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-cart me-1"></i> Order Data</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tbody>
                                    @foreach ($orderData as $key => $value)
                                        <tr>
                                            <td class="text-muted" style="width: 35%;">{{ ucwords(str_replace(['_', '-'], ' ', (string) $key)) }}</td>
                                            <td class="fw-medium">
                                                {{ is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? '—')) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-0">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="bx bx-message-dots me-1"></i> Transcript</h5>
                    <span class="text-muted font-size-13">{{ count($transcript) }} messages</span>
                </div>
                <div class="card-body">
                    @forelse ($transcript as $item)
                        @php
                            if (is_array($item)) {
                                $role = (string) ($item['role'] ?? $item['from'] ?? 'bot');
                                $text = $item['message'] ?? $item['text'] ?? $item['body'] ?? null;
                                if ($text === null) {
                                    $text = json_encode($item);
                                }
                            } else {
                                $role = 'bot';
                                $text = json_encode($item);
                            }
                            $outgoing = in_array(strtolower($role), ['user', 'customer', 'contact', 'lead'], true);
                        @endphp
                        <div class="d-flex mb-3 {{ $outgoing ? 'justify-content-end' : '' }}">
                            <div class="cds-chat-bubble {{ $outgoing ? 'cds-chat-out' : 'cds-chat-in' }}">
                                <div class="fw-semibold font-size-12 mb-1 {{ $outgoing ? 'text-white-50' : 'text-muted' }}">{{ ucfirst($role) }}</div>
                                <div>{!! nl2br(e((string) $text)) !!}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted py-4 mb-0">No transcript recorded for this conversation.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
