@extends('layouts.app')

@section('title', $lead->name)
@section('page-title', 'Lead — '.$lead->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/leads') }}">Leads</a></li>
    <li class="breadcrumb-item active">{{ $lead->lead_no }}</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('leads.convert') && $lead->status !== 'won')
        <form action="{{ url('/leads/'.$lead->id.'/convert') }}" method="POST" class="d-inline" onsubmit="return confirm('Convert this lead to a customer?');">
            @csrf
            <button class="btn btn-success btn-sm"><i class="bx bx-transfer-alt me-1"></i> Convert to Customer</button>
        </form>
    @endif
    @if (auth()->user()->can('sales.create'))
        <a href="{{ url('/sales/create') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-cart-add me-1"></i> New Sale</a>
    @endif
    @if (auth()->user()->can('leads.delete'))
        <form action="{{ url('/leads/'.$lead->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lead permanently?');">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash"></i></button>
        </form>
    @endif
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $activityColors = [
        'note' => 'secondary',
        'call' => 'primary',
        'email' => 'info',
        'whatsapp' => 'success',
        'meeting' => 'warning',
        'status_change' => 'dark',
        'follow_up' => 'primary',
        'bot' => 'info',
    ];
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-start justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ $lead->name }}</h5>
                        <span class="text-muted font-size-13">{{ $lead->lead_no }} &middot; {{ $lead->company ?? 'No company' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @include('partials.lead-status', ['status' => $lead->status])
                        @php($priorityColor = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$lead->priority] ?? 'secondary')
                        <span class="badge bg-{{ $priorityColor }}-subtle text-{{ $priorityColor }}">{{ ucfirst($lead->priority) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1 font-size-12">Email</p>
                            <p class="mb-0">{{ $lead->email ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1 font-size-12">Phone</p>
                            <p class="mb-0">{{ $lead->phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1 font-size-12">WhatsApp</p>
                            <p class="mb-0">{{ $lead->whatsapp_number ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1 font-size-12">City</p>
                            <p class="mb-0">{{ $lead->city ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1 font-size-12">Country</p>
                            <p class="mb-0">{{ $lead->country ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1 font-size-12">Estimated Value</p>
                            <p class="mb-0 fw-semibold">{{ $money($lead->estimated_value) }} {{ $lead->currency }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1 font-size-12">Product Interest</p>
                            <p class="mb-0">{{ $lead->product_interest ?? '—' }}</p>
                        </div>
                        @if ($lead->message)
                            <div class="col-12">
                                <p class="text-muted mb-1 font-size-12">Message</p>
                                <p class="mb-0">{{ $lead->message }}</p>
                            </div>
                        @endif
                        @if ($lead->notes)
                            <div class="col-12">
                                <p class="text-muted mb-1 font-size-12">Notes</p>
                                <p class="mb-0">{{ $lead->notes }}</p>
                            </div>
                        @endif
                        @if ($lead->lost_reason)
                            <div class="col-12">
                                <p class="text-muted mb-1 font-size-12">Lost Reason</p>
                                <p class="mb-0 text-danger">{{ $lead->lost_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Activity Timeline</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addActivityForm"><i class="bx bx-plus me-1"></i> Log Activity</button>
                </div>
                <div class="card-body">
                    <div class="collapse mb-3" id="addActivityForm">
                        <div class="border rounded p-3 bg-light-subtle">
                            <form action="{{ url('/leads/'.$lead->id.'/activity') }}" method="POST" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        @foreach (\App\Models\LeadActivity::TYPES as $type)
                                            <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Title</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Happened At</label>
                                    <input type="datetime-local" name="happened_at" class="form-control" value="{{ old('happened_at', now()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Next Follow Up</label>
                                    <input type="datetime-local" name="next_follow_up_at" class="form-control" value="{{ old('next_follow_up_at', $lead->next_follow_up_at?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Details <span class="text-danger">*</span></label>
                                    <textarea name="body" class="form-control" rows="2" required>{{ old('body') }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-primary btn-sm"><i class="bx bx-save me-1"></i> Save Activity</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @forelse ($lead->activities->sortByDesc('happened_at') as $activity)
                        <div class="d-flex border-bottom py-3">
                            <img src="{{ $activity->user?->avatarUrl() }}" class="rounded-circle avatar-sm me-3" alt="">
                            <div class="flex-1">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <h6 class="mb-0 font-size-14">{{ $activity->title ?? ucfirst($activity->type) }}</h6>
                                    <span class="badge bg-{{ $activityColors[$activity->type] ?? 'secondary' }}-subtle text-{{ $activityColors[$activity->type] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $activity->type)) }}</span>
                                </div>
                                <p class="mb-1 font-size-13">{{ $activity->body }}</p>
                                <span class="text-muted font-size-12">
                                    {{ $activity->user?->name ?? 'System' }} &middot;
                                    {{ $activity->happened_at?->diffForHumans() ?? $activity->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted py-4 mb-0">No activity recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Linked Sales</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sale</th>
                                    <th>Date</th>
                                    <th class="text-end">Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lead->sales as $sale)
                                    <tr>
                                        <td><a href="{{ url('/sales/'.$sale->id) }}" class="text-body fw-semibold">{{ $sale->sale_no }}</a></td>
                                        <td>{{ $sale->sale_date->format('d M Y') }}</td>
                                        <td class="text-end fw-semibold">{{ $money($sale->total) }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($sale->payment_status) }}</span></td>
                                        <td>@include('partials.sale-status', ['status' => $sale->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No sales linked to this lead.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($lead->whatsappContact || $lead->botConversation)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">WhatsApp &amp; Bot</h5></div>
                    <div class="card-body">
                        @if ($lead->whatsappContact)
                            <div class="d-flex align-items-center mb-3">
                                <i class="bx bxl-whatsapp font-size-24 text-success me-2"></i>
                                <div>
                                    <h6 class="mb-0">{{ $lead->whatsappContact->displayName() }}</h6>
                                    <span class="text-muted font-size-12">
                                        {{ $lead->whatsappContact->phone ?? $lead->whatsappContact->wa_id }}
                                        @if ($lead->whatsappContact->last_message_at)
                                            &middot; last message {{ $lead->whatsappContact->last_message_at->diffForHumans() }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if ($lead->botConversation)
                            <div class="border rounded p-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <h6 class="mb-1"><i class="bx bx-bot me-1"></i>Bot Conversation</h6>
                                        <span class="text-muted font-size-12">
                                            {{ ucfirst($lead->botConversation->status ?? 'unknown') }}
                                            @if ($lead->botConversation->language)
                                                &middot; {{ strtoupper($lead->botConversation->language) }}
                                            @endif
                                            &middot; {{ is_array($lead->botConversation->transcript) ? count($lead->botConversation->transcript) : 0 }} messages
                                        </span>
                                    </div>
                                    @if (auth()->user()->can('bot.view'))
                                        <a href="{{ url('/bot/conversations/'.$lead->botConversation->id) }}" class="btn btn-sm btn-outline-info">View Conversation</a>
                                    @endif
                                </div>
                                @if ($lead->botConversation->summary)
                                    <p class="mb-0 mt-2 font-size-13">{{ $lead->botConversation->summary }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Assigned To</h5></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $lead->assignedUser?->avatarUrl() }}" class="rounded-circle avatar-sm" alt="">
                        <div class="ms-2">
                            <h6 class="mb-0">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</h6>
                            <span class="text-muted font-size-12">Current owner</span>
                        </div>
                    </div>
                    @if (auth()->user()->can('leads.assign'))
                        <form action="{{ url('/leads/'.$lead->id.'/assign') }}" method="POST">
                            @csrf
                            <label class="form-label">Assign To</label>
                            <div class="input-group">
                                <select name="assigned_to" class="form-select" required>
                                    <option value="">Select salesperson</option>
                                    @foreach ($salespeople as $salesperson)
                                        <option value="{{ $salesperson->id }}" @selected($lead->assigned_to == $salesperson->id)>{{ $salesperson->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary"><i class="bx bx-user-plus"></i></button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            @if (auth()->user()->can('leads.update'))
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Update Status</h5></div>
                    <div class="card-body">
                        <form action="{{ url('/leads/'.$lead->id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="name" value="{{ $lead->name }}">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach (\App\Models\Lead::STATUSES as $status)
                                        <option value="{{ $status }}" @selected(old('status', $lead->status) === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lost Reason</label>
                                <input type="text" name="lost_reason" class="form-control" value="{{ old('lost_reason', $lead->lost_reason) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Next Follow Up</label>
                                <input type="datetime-local" name="next_follow_up_at" class="form-control" value="{{ old('next_follow_up_at', $lead->next_follow_up_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <button class="btn btn-primary w-100"><i class="bx bx-save me-1"></i> Update Lead</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Conversion</h5></div>
                <div class="card-body">
                    @if ($lead->converted_at || $lead->status === 'won')
                        <p class="mb-2"><span class="badge bg-success-subtle text-success"><i class="bx bx-check me-1"></i>Converted</span></p>
                        @if ($lead->converted_at)
                            <p class="text-muted font-size-13 mb-2">Converted {{ $lead->converted_at->format('d M Y H:i') }}</p>
                        @endif
                        @if ($lead->customer)
                            <a href="{{ url('/customers/'.$lead->customer->id) }}" class="btn btn-sm btn-outline-success w-100"><i class="bx bx-user me-1"></i> View Customer</a>
                        @endif
                    @else
                        <p class="text-muted font-size-13">Convert this lead into a customer record to keep it in the pipeline history.</p>
                        @if (auth()->user()->can('leads.convert'))
                            <form action="{{ url('/leads/'.$lead->id.'/convert') }}" method="POST" onsubmit="return confirm('Convert this lead to a customer?');">
                                @csrf
                                <button class="btn btn-success w-100"><i class="bx bx-transfer-alt me-1"></i> Convert to Customer</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Details</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Source</span>
                        <span>{{ ucwords(str_replace('_', ' ', $lead->source)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created</span>
                        <span>{{ $lead->created_at?->format('d M Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Contacted</span>
                        <span>{{ $lead->last_contacted_at?->format('d M Y H:i') ?? 'Never' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Next Follow Up</span>
                        <span>{{ $lead->next_follow_up_at?->format('d M Y H:i') ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Customer</span>
                        @if ($lead->customer)
                            <a href="{{ url('/customers/'.$lead->customer->id) }}">{{ $lead->customer->name }}</a>
                        @else
                            <span>Not linked</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
