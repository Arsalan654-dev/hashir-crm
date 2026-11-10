@extends('layouts.app')

@section('title', $organization->name)
@section('page-title', 'Organization — '.$organization->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organizations.index') }}">Organizations</a></li>
    <li class="breadcrumb-item active">{{ $organization->name }}</li>
@endsection

@section('page-actions')
    @can('organizations.update')
        <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-edit me-1"></i> Edit</a>
    @endcan
    @if ($organization->owner)
        <form action="{{ route('organizations.impersonate-owner', $organization) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-primary btn-sm"><i class="bx bx-log-in-circle me-1"></i> Open client CRM</button>
        </form>
    @endif
@endsection

@section('content')
    @if (session('generated_password'))
        <div class="alert alert-warning">
            <strong>Generated password:</strong> <code>{{ session('generated_password') }}</code> — copy it now, it will not be shown again.
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-20">{{ strtoupper(mb_substr($organization->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $organization->name }}</h5>
                            <span class="text-muted font-size-13">{{ $businessTypes[$organization->business_type] ?? $organization->business_type }}</span>
                        </div>
                    </div>

                    <ul class="list-unstyled mb-0 font-size-14">
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">Status</span>
                            <span>
                                @if ($organization->status === 'active')
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">{{ ucfirst($organization->status) }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">CRM Access</span>
                            <span>
                                @if ($organization->crmIsBlocked())
                                    <span class="badge bg-danger-subtle text-danger">Blocked</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Allowed</span>
                                @endif
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">WhatsApp Bot</span>
                            <span>
                                @if ($organization->botIsBlocked())
                                    <span class="badge bg-secondary-subtle text-secondary">Blocked</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Enabled</span>
                                @endif
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">Payment</span>
                            <span>
                                @if ($organization->isPaid())
                                    <span class="badge bg-success-subtle text-success">Paid</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ ucfirst($organization->payment_status) }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">Email</span><span>{{ $organization->email ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">Phone</span><span>{{ $organization->phone ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">WhatsApp</span><span>{{ $organization->whatsapp ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">Location</span><span>{{ collect([$organization->city, $organization->country])->filter()->implode(', ') ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-1"><span class="text-muted">Created</span><span>{{ $organization->created_at->format('d M Y') }}</span></li>
                    </ul>
                </div>
                @can('organizations.block')
                    <div class="card-footer">
                        <div class="d-flex gap-2">
                            <form action="{{ route('organizations.toggle-crm', $organization) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button class="btn btn-sm w-100 {{ $organization->crm_blocked ? 'btn-success' : 'btn-outline-danger' }}">
                                    <i class="bx {{ $organization->crm_blocked ? 'bx-check' : 'bx-block' }} me-1"></i>
                                    {{ $organization->crm_blocked ? 'Unblock CRM' : 'Block CRM' }}
                                </button>
                            </form>
                            <form action="{{ route('organizations.toggle-bot', $organization) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button class="btn btn-sm w-100 {{ $organization->bot_blocked ? 'btn-success' : 'btn-outline-warning' }}">
                                    <i class="bx {{ $organization->bot_blocked ? 'bx-check' : 'bx-bot' }} me-1"></i>
                                    {{ $organization->bot_blocked ? 'Enable Bot' : 'Block Bot' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subscription</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Plan</span>
                        <span class="fw-medium">{{ $organization->plan_name ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Amount</span>
                        <span class="fw-medium">{{ number_format((float) $organization->plan_amount, 2) }} {{ $organization->currency }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cycle</span>
                        <span class="fw-medium">{{ ucfirst(str_replace('_', ' ', $organization->billing_cycle)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Paid until</span>
                        <span class="fw-medium">{{ $organization->paid_until?->format('d M Y') ?? '—' }}</span>
                    </div>
                    @can('organizations.payments')
                        <button class="btn btn-primary btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#record-payment-modal">
                            <i class="bx bx-money me-1"></i> Record Payment
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Client Users</h5>
                    <span class="badge bg-primary-subtle text-primary">{{ $organization->users->count() }} users</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Last login</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($organization->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $user->avatarUrl() }}" alt="" class="rounded-circle avatar-xs me-2">
                                                <div>
                                                    <h6 class="mb-0 font-size-14">{{ $user->name }} @if ($user->id === $organization->owner_user_id)<span class="badge bg-primary-subtle text-primary font-size-10">Owner</span>@endif</h6>
                                                    <span class="text-muted font-size-12">{{ $user->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->assignedRole?->name ?? '—' }}</td>
                                        <td>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                        <td>
                                            @if ($user->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No users yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Payment History</h5>
                    @if ($organization->owner)
                        <div class="d-flex gap-2">
                            <form action="{{ route('organizations.send-credentials', $organization) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary"><i class="bx bx-envelope me-1"></i> Resend credentials</button>
                            </form>
                            <form action="{{ route('organizations.reset-owner-password', $organization) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset the owner password and email a new one?');">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning"><i class="bx bx-key me-1"></i> Reset password</button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($organization->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('d M Y') }}</td>
                                        <td>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                        <td>{{ $payment->methodLabel() }} @if ($payment->reference)<span class="text-muted font-size-12">({{ $payment->reference }})</span>@endif</td>
                                        <td class="font-size-12">
                                            {{ $payment->period_start?->format('d M Y') ?? '—' }}
                                            @if ($payment->period_end) → {{ $payment->period_end->format('d M Y') }} @endif
                                        </td>
                                        <td>
                                            @if ($payment->status === 'paid')
                                                <span class="badge bg-success-subtle text-success">Paid</span>
                                            @elseif ($payment->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning">Pending review</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">{{ ucfirst($payment->status) }}</span>
                                            @endif
                                            @if ($payment->proof_path)
                                                <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank" class="badge bg-info-subtle text-info">Proof</a>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($payment->status === 'pending')
                                                @can('organizations.payments')
                                                    <form action="{{ route('organizations.payments.approve', [$organization, $payment]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-success" title="Approve"><i class="bx bx-check"></i> Approve</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($organization->notes)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Internal Notes</h5></div>
                    <div class="card-body">{{ $organization->notes }}</div>
                </div>
            @endif
        </div>
    </div>

    @can('organizations.payments')
        <div class="modal fade" id="record-payment-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('organizations.payments.store', $organization) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record Payment — {{ $organization->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ $organization->plan_amount }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Method <span class="text-danger">*</span></label>
                                <select name="method" class="form-select" required>
                                    @foreach ($methodLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference / TXN ID</label>
                                <input type="text" name="reference" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="paid">Paid (activate now)</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period Start</label>
                                <input type="date" name="period_start" class="form-control" value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period End</label>
                                <input type="date" name="period_end" class="form-control" placeholder="Auto from billing cycle">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection
