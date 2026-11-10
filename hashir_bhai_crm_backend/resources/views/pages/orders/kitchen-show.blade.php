@extends('layouts.app')

@section('title', 'Order '.$order->order_no)
@section('page-title', 'Order — '.$order->order_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/kitchen') }}">Kitchen / Orders</a></li>
    <li class="breadcrumb-item active">{{ $order->order_no }}</li>
@endsection

@section('page-actions')
    <a href="{{ url('/kitchen') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Back to Kitchen</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Order Items</h5>
                    <span class="badge bg-{{ $order->statusColor() }}-subtle text-{{ $order->statusColor() }} font-size-13">{{ $order->statusLabel() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                                        <td class="text-end">{{ number_format((float) $item->unit_price, 2) }} {{ $order->currency }}</td>
                                        <td class="text-end fw-medium">{{ number_format((float) $item->total, 2) }} {{ $order->currency }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">Subtotal</td>
                                    <td class="text-end">{{ number_format((float) $order->subtotal, 2) }} {{ $order->currency }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">Delivery Fee</td>
                                    <td class="text-end">{{ number_format((float) $order->delivery_fee, 2) }} {{ $order->currency }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold">{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Fulfilment Timeline</h5></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 font-size-14">
                        @foreach ([
                            'confirmed_at' => 'Confirmed',
                            'preparing_at' => 'Preparing started',
                            'ready_at' => 'Ready',
                            'dispatched_at' => 'Out for delivery',
                            'delivered_at' => 'Delivered',
                            'cancelled_at' => 'Cancelled',
                        ] as $column => $label)
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ $label }}</span>
                                <span>{{ $order->{$column}?->format('d M Y, h:i A') ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Customer</h5></div>
                <div class="card-body font-size-14">
                    <p class="mb-1 fw-medium">{{ $order->customer_name }}</p>
                    <p class="mb-1 text-muted">{{ $order->customer_phone }}</p>
                    @if ($order->delivery_address)
                        <p class="mb-1 text-muted">{{ $order->delivery_address }}</p>
                    @endif
                    @if ($order->lead)
                        <a href="{{ route('leads.show', $order->lead) }}" class="btn btn-sm btn-outline-primary mt-2"><i class="bx bx-target-lock me-1"></i> Open lead {{ $order->lead->lead_no }}</a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Payment</h5>
                    @if ($order->payment_status === 'paid')
                        <span class="badge bg-success-subtle text-success">Paid</span>
                    @elseif ($order->payment_status === 'pending_verification')
                        <span class="badge bg-warning-subtle text-warning">Verify</span>
                    @else
                        <span class="badge bg-light text-dark">Unpaid</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="mb-2 font-size-14">Method: <strong>{{ $order->paymentMethodLabel() }}</strong></p>
                    @if ($order->payment_reference)
                        <p class="mb-2 font-size-13 text-muted">Reference: {{ $order->payment_reference }}</p>
                    @endif
                    @if ($canManage)
                        <form action="{{ route('kitchen.payment', $order) }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <select name="payment_status" class="form-select form-select-sm">
                                    @foreach (['unpaid' => 'Unpaid', 'pending_verification' => 'Pending verification', 'paid' => 'Paid'] as $value => $label)
                                        <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <input type="text" name="reference" class="form-control form-control-sm" placeholder="Reference / TXN ID" value="{{ $order->payment_reference }}">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-outline-primary w-100">Update Payment</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            @if ($canManage)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Actions</h5></div>
                    <div class="card-body d-grid gap-2">
                        @if ($order->isOpen() && $order->nextStatus())
                            <form action="{{ route('kitchen.status', $order) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="{{ $order->nextStatus() }}">
                                <button class="btn btn-primary w-100">
                                    @if ($order->status === 'pending') Confirm Order
                                    @elseif ($order->status === 'confirmed') Start Preparing
                                    @elseif ($order->status === 'preparing') Mark Ready
                                    @elseif ($order->status === 'ready') Dispatch (Out for Delivery)
                                    @elseif ($order->status === 'dispatched') Mark Delivered
                                    @endif
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('kitchen.invoice', $order) }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-success w-100"><i class="bx bx-file me-1"></i> Send Invoice PDF on WhatsApp</button>
                        </form>
                        @if ($order->isOpen())
                            <form action="{{ route('kitchen.status', $order) }}" method="POST" onsubmit="return confirm('Cancel this order and notify the customer?');">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Cancel Order</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if ($order->notes)
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Notes</h5></div>
                    <div class="card-body">{{ $order->notes }}</div>
                </div>
            @endif
        </div>
    </div>
@endsection
