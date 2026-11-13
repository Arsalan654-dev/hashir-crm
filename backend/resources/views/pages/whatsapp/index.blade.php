@extends('layouts.app')

@section('title', 'WhatsApp Inbox')
@section('page-title', 'WhatsApp Inbox')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">WhatsApp</li>
@endsection

@section('page-actions')
    <a href="{{ url('/whatsapp/contacts') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-user-plus me-1"></i> Contacts</a>
    @if (auth()->user()->can('whatsapp.settings'))
        <a href="{{ url('/whatsapp/settings') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-cog me-1"></i> API Settings</a>
    @endif
@endsection

@section('content')
    @if (! $configured)
        <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2">
            <i class="bx bx-error-circle font-size-22"></i>
            <div class="flex-grow-1">
                <strong>WhatsApp Cloud API is not configured.</strong>
                Add the phone number ID and access token to send and receive messages.
            </div>
            <a href="{{ url('/whatsapp/settings') }}" class="btn btn-sm btn-warning">Configure now</a>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-user-voice"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Contacts</p>
                        <h4 class="mb-0">{{ number_format($totals['contacts']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-info-subtle text-info"><i class="bx bx-download"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Incoming</p>
                        <h4 class="mb-0">{{ number_format($totals['incoming']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-upload"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Outgoing</p>
                        <h4 class="mb-0">{{ number_format($totals['outgoing']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-calendar-check"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Today</p>
                        <h4 class="mb-0">{{ number_format($totals['today']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-danger-subtle text-danger"><i class="bx bx-target-lock"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Leads</p>
                        <h4 class="mb-0">{{ number_format($totals['leads']) }}</h4>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card mb-0 h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="cds-kpi-icon bg-secondary-subtle text-secondary"><i class="bx bxl-whatsapp"></i></div>
                    <div class="ms-3 flex-1">
                        <p class="text-muted mb-1 font-size-13">Account</p>
                        <h6 class="mb-0 text-truncate">{{ $account?->name ?? 'Not configured' }}</h6>
                        @if ($configured)
                            <span class="badge bg-success-subtle text-success">Connected</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/whatsapp') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, phone or WhatsApp ID">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Search</button>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ url('/whatsapp/contacts') }}" class="btn btn-link btn-sm">Manage contacts</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Messages</th>
                            <th>Linked</th>
                            <th>Last activity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-title rounded-circle bg-success-subtle text-success avatar-sm">{{ strtoupper(mb_substr($contact->displayName(), 0, 1)) }}</span>
                                        <div class="ms-2">
                                            <h6 class="mb-0 font-size-14">{{ $contact->displayName() }}</h6>
                                            <span class="text-muted font-size-12">{{ $contact->wa_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $contact->phone ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark">{{ number_format($contact->messages_count) }}</span></td>
                                <td>
                                    @if ($contact->lead)
                                        <a href="{{ url('/leads/'.$contact->lead->id) }}" class="badge bg-primary-subtle text-primary text-decoration-none">{{ $contact->lead->name }}</a>
                                    @endif
                                    @if ($contact->customer)
                                        <a href="{{ url('/customers/'.$contact->customer->id) }}" class="badge bg-success-subtle text-success text-decoration-none">{{ $contact->customer->name }}</a>
                                    @endif
                                    @if (! $contact->lead && ! $contact->customer)
                                        <span class="text-muted font-size-12">Not linked</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted font-size-13">
                                        {{ $contact->last_message_at?->diffForHumans() ?? $contact->created_at?->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/whatsapp/'.$contact->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-message-dots me-1"></i> Open chat</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No WhatsApp contacts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $contacts->links() }}</div>
        </div>
    </div>
@endsection
