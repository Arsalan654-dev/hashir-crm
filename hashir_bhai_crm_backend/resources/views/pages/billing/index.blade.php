@extends('layouts.app')

@section('title', 'Billing')
@section('page-title', 'Billing & Subscription')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Billing</li>
@endsection

@section('content')
    @if ($organization === null)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bx bx-buildings font-size-40 text-muted d-block mb-2"></i>
                <h5>No organization linked</h5>
                <p class="text-muted mb-0">Your account is not linked to an organization yet. Please contact support.</p>
            </div>
        </div>
    @else
        @if (! $organization->isPaid())
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bx bx-error-circle font-size-24 me-3"></i>
                <div>
                    <strong>Payment required.</strong>
                    Your subscription is not active yet. WhatsApp bot, product management and other premium features are locked until payment is confirmed.
                </div>
            </div>
        @else
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle font-size-24 me-3"></i>
                <div>
                    <strong>Subscription active.</strong>
                    @if ($organization->paid_until)
                        Your plan is paid until {{ $organization->paid_until->format('d M Y') }}.
                    @endif
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Your Plan</h5>
                        <p class="text-muted mb-0 font-size-13">{{ $organization->name }}</p>
                    </div>
                    <div class="card-body">
                        <h3 class="mb-1">{{ $organization->plan_name ?? 'Standard Plan' }}</h3>
                        <p class="text-muted mb-3">{{ ucfirst(str_replace('_', ' ', $organization->billing_cycle)) }} billing</p>
                        <div class="d-flex align-items-baseline mb-3">
                            <h2 class="mb-0 me-2">{{ number_format((float) $organization->plan_amount, 2) }}</h2>
                            <span class="text-muted">{{ $organization->currency }}</span>
                        </div>
                        <ul class="list-unstyled mb-0 font-size-14">
                            <li class="d-flex justify-content-between py-1"><span class="text-muted">Status</span>
                                <span>
                                    @if ($organization->isPaid())
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">{{ ucfirst($organization->payment_status) }}</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-1"><span class="text-muted">Paid until</span><span>{{ $organization->paid_until?->format('d M Y') ?? '—' }}</span></li>
                            <li class="d-flex justify-content-between py-1"><span class="text-muted">Currency</span><span>{{ $organization->currency }}</span></li>
                        </ul>
                    </div>
                </div>

                @if (filled($bankDetails) || filled($instructions))
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Payment Instructions</h5></div>
                        <div class="card-body font-size-14">
                            @if (filled($instructions))
                                <p class="text-muted">{{ $instructions }}</p>
                            @endif
                            @if (filled($bankDetails))
                                <pre class="bg-light p-3 rounded mb-0" style="white-space: pre-wrap;">{{ $bankDetails }}</pre>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Submit a Payment</h5></div>
                    <div class="card-body">
                        <p class="text-muted font-size-13">After paying, submit the details below (attach a screenshot/receipt if you have one). Our team will verify and activate your account.</p>
                        <form action="{{ route('billing.submit') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount', $organization->plan_amount) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Method <span class="text-danger">*</span></label>
                                <select name="method" class="form-select" required>
                                    @foreach ($methodLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reference / TXN ID</label>
                                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Anything we should know">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Receipt / Proof</label>
                                <input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary"><i class="bx bx-send me-1"></i> Submit Payment</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Payment History</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('d M Y') }}</td>
                                            <td>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                            <td>{{ $payment->methodLabel() }}</td>
                                            <td>
                                                @if ($payment->status === 'paid')
                                                    <span class="badge bg-success-subtle text-success">Confirmed</span>
                                                @elseif ($payment->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning">Under review</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">{{ ucfirst($payment->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($payment->proof_path)
                                                    <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank" class="text-primary">View</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">No payments submitted yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
