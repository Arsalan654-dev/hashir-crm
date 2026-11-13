@extends('layouts.app')

@section('title', 'Search')
@section('page-title', 'Search')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Search</li>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ url('/search') }}">
                <div class="col-md-10">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="q" value="{{ $term }}" class="form-control" placeholder="Leads, sales, customers, products, employees, WhatsApp, invoices">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Search</button>
                </div>
            </form>
        </div>
    </div>

    @if (mb_strlen($term) < 2)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bx bx-search-alt font-size-24 text-muted d-block mb-2"></i>
                <h5 class="mb-1">Start typing to search</h5>
                <p class="text-muted mb-0">Enter at least 2 characters to search across the CRM.</p>
            </div>
        </div>
    @elseif ($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bx bx-search-alt font-size-24 text-muted d-block mb-2"></i>
                <h5 class="mb-1">No results for &ldquo;{{ $term }}&rdquo;</h5>
                <p class="text-muted mb-0">Try a different keyword, phone number, or reference number.</p>
            </div>
        </div>
    @else
        <p class="text-muted font-size-13">{{ number_format($total) }} result{{ $total === 1 ? '' : 's' }} for &ldquo;{{ $term }}&rdquo;</p>

        <div class="row g-3">
            @if ($results['leads']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bx-target-lock me-1"></i> Leads <span class="badge bg-light text-dark ms-1">{{ $results['leads']->count() }}</span></h5>
                            <a href="{{ url('/leads') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['leads'] as $lead)
                                <a href="{{ url('/leads/'.$lead->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $lead->name }}</h6>
                                            <div class="text-muted font-size-12">{{ $lead->lead_no }} &middot; {{ $lead->phone ?? 'No phone' }} &middot; {{ $lead->email ?? 'No email' }}</div>
                                        </div>
                                        @include('partials.lead-status', ['status' => $lead->status])
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($results['sales']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bx-cart me-1"></i> Sales <span class="badge bg-light text-dark ms-1">{{ $results['sales']->count() }}</span></h5>
                            <a href="{{ url('/sales') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['sales'] as $sale)
                                <a href="{{ url('/sales/'.$sale->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $sale->sale_no }} &middot; {{ $sale->customer_name }}</h6>
                                            <div class="text-muted font-size-12">{{ $sale->customer_phone ?? 'No phone' }} &middot; {{ $money($sale->total) }}</div>
                                        </div>
                                        @include('partials.sale-status', ['status' => $sale->status])
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($results['customers']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bx-user-voice me-1"></i> Customers <span class="badge bg-light text-dark ms-1">{{ $results['customers']->count() }}</span></h5>
                            <a href="{{ url('/customers') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['customers'] as $customer)
                                <a href="{{ url('/customers/'.$customer->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $customer->name }}</h6>
                                            <div class="text-muted font-size-12">{{ $customer->company ?? 'No company' }} &middot; {{ $customer->email ?? 'No email' }} &middot; {{ $customer->phone ?? 'No phone' }}</div>
                                        </div>
                                        @if ($customer->status)
                                            <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($customer->status) }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($results['products']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bx-package me-1"></i> Products <span class="badge bg-light text-dark ms-1">{{ $results['products']->count() }}</span></h5>
                            <a href="{{ url('/products') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['products'] as $product)
                                <a href="{{ url('/products/'.$product->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $product->name }}</h6>
                                            <div class="text-muted font-size-12">{{ $product->sku ?? 'No SKU' }} &middot; {{ $money($product->price) }}</div>
                                        </div>
                                        @if ($product->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($results['employees']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bx-group me-1"></i> Employees <span class="badge bg-light text-dark ms-1">{{ $results['employees']->count() }}</span></h5>
                            <a href="{{ url('/employees') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['employees'] as $employee)
                                <a href="{{ url('/employees/'.$employee->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $employee->avatarUrl() }}" class="rounded-circle avatar-xs me-3" alt="">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $employee->name }}</h6>
                                            <div class="text-muted font-size-12">{{ $employee->employee_code ?? 'No code' }} &middot; {{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($results['whatsapp']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bxl-whatsapp me-1"></i> WhatsApp <span class="badge bg-light text-dark ms-1">{{ $results['whatsapp']->count() }}</span></h5>
                            <a href="{{ url('/whatsapp/contacts') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['whatsapp'] as $contact)
                                <a href="{{ url('/whatsapp/'.$contact->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $contact->name ?? $contact->profile_name ?? 'Unknown contact' }}</h6>
                                            <div class="text-muted font-size-12">{{ $contact->phone ?? 'No phone' }} &middot; {{ $contact->wa_id ?? 'No WA ID' }}</div>
                                        </div>
                                        @if ($contact->is_blocked)
                                            <span class="badge bg-danger-subtle text-danger">Blocked</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($results['invoices']->isNotEmpty())
                <div class="col-xl-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="bx bx-receipt me-1"></i> Invoices <span class="badge bg-light text-dark ms-1">{{ $results['invoices']->count() }}</span></h5>
                            <a href="{{ url('/invoices') }}" class="btn btn-sm btn-link">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($results['invoices'] as $invoice)
                                <a href="{{ url('/invoices/'.$invoice->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h6 class="mb-1 font-size-14">{{ $invoice->invoice_no }} &middot; {{ $invoice->billing_name }}</h6>
                                            <div class="text-muted font-size-12">{{ $money($invoice->amount) }}</div>
                                        </div>
                                        <span class="badge bg-{{ $invoice->statusColor() }}-subtle text-{{ $invoice->statusColor() }}">{{ ucfirst((string) $invoice->status) }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection
