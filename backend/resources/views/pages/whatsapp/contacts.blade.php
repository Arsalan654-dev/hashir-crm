@extends('layouts.app')

@section('title', 'WhatsApp Contacts')
@section('page-title', 'WhatsApp Contacts')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/whatsapp') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">Contacts</li>
@endsection

@section('page-actions')
    <a href="{{ url('/whatsapp') }}" class="btn btn-outline-primary btn-sm"><i class="bx bxl-whatsapp me-1"></i> Inbox</a>
    @if (auth()->user()->can('whatsapp.settings'))
        <a href="{{ url('/whatsapp/settings') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-cog me-1"></i> API Settings</a>
    @endif
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/whatsapp/contacts') }}">
                        <div class="col-md-5">
                            <label class="form-label mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name or phone number">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Search</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>WhatsApp ID</th>
                                    <th>Lead</th>
                                    <th>Customer</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contacts as $contact)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 font-size-14">{{ $contact->displayName() }}</h6>
                                            @if ($contact->source)
                                                <span class="text-muted font-size-12">{{ ucwords(str_replace('_', ' ', $contact->source)) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $contact->phone ?? '—' }}</td>
                                        <td><span class="text-muted font-size-13">{{ $contact->wa_id }}</span></td>
                                        <td>
                                            @if ($contact->lead)
                                                <a href="{{ url('/leads/'.$contact->lead->id) }}" class="badge bg-primary-subtle text-primary text-decoration-none">{{ $contact->lead->name }}</a>
                                            @else
                                                <span class="text-muted font-size-12">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($contact->customer)
                                                <a href="{{ url('/customers/'.$contact->customer->id) }}" class="badge bg-success-subtle text-success text-decoration-none">{{ $contact->customer->name }}</a>
                                            @else
                                                <span class="text-muted font-size-12">—</span>
                                            @endif
                                        </td>
                                        <td><span class="text-muted font-size-13">{{ $contact->created_at?->format('d M Y') }}</span></td>
                                        <td class="text-end cds-table-actions">
                                            <a href="{{ url('/whatsapp/'.$contact->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-message-dots"></i></a>
                                            @if (! $contact->lead && auth()->user()->can('leads.create'))
                                                <form action="{{ url('/whatsapp/contacts/'.$contact->id.'/lead') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success" title="Create lead from this contact"><i class="bx bx-plus"></i> Lead</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No contacts found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $contacts->links() }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bx bx-import me-1"></i> Import contacts</h5>
                    @if (auth()->user()->can('whatsapp.import_contacts'))
                        <form action="{{ url('/whatsapp/contacts/import') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">One contact per line</label>
                                <textarea name="contacts" rows="8" class="form-control" placeholder="Name, Phone&#10;Jane Doe, +35799123456" required>{{ old('contacts') }}</textarea>
                                <div class="form-text">Format: <code>Name, Phone</code>. Existing numbers are updated, new numbers are added.</div>
                            </div>
                            <button class="btn btn-primary w-100"><i class="bx bx-upload me-1"></i> Import contacts</button>
                        </form>
                    @else
                        <p class="text-muted mb-0 font-size-13">You do not have permission to import contacts.</p>
                    @endif
                </div>
            </div>

            @if ($leads->isNotEmpty())
                <div class="card mt-3 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title mb-0">Recent leads</h5>
                            <span class="badge bg-light text-dark">{{ number_format($leads->count()) }}</span>
                        </div>
                        <ul class="list-unstyled mb-0">
                            @foreach ($leads->take(5) as $lead)
                                <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                    <a href="{{ url('/leads/'.$lead->id) }}" class="text-body font-size-13">{{ $lead->name }}</a>
                                    <span class="text-muted font-size-12">{{ $lead->created_at?->diffForHumans(null, true) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
