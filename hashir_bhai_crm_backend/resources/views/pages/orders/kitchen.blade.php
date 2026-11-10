@extends('layouts.app')

@section('title', 'Kitchen / Orders')
@section('page-title', 'Kitchen — Order Desk')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Kitchen / Orders</li>
@endsection

@section('page-actions')
    <a href="{{ url('/kitchen?status=today') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-calendar-check me-1"></i> Today's Orders</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-h-100 border-danger">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block mb-1">New Orders</span>
                    <h4 class="mb-0 text-danger">{{ number_format($counts['pending']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-h-100">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block mb-1">Preparing</span>
                    <h4 class="mb-0 text-warning">{{ number_format($counts['preparing']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-h-100">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block mb-1">Ready</span>
                    <h4 class="mb-0 text-info">{{ number_format($counts['ready']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-h-100">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block mb-1">Out for Delivery</span>
                    <h4 class="mb-0">{{ number_format($counts['dispatched']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-h-100">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block mb-1">Delivered Today</span>
                    <h4 class="mb-0 text-success">{{ number_format($counts['delivered_today']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-h-100">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block mb-1">Today's Revenue</span>
                    <h4 class="mb-0">{{ number_format($counts['revenue_today'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if ($counts['pending_payments'] > 0)
        <div class="alert alert-warning d-flex align-items-center">
            <i class="bx bx-credit-card font-size-20 me-2"></i>
            <div><strong>{{ $counts['pending_payments'] }} payment(s)</strong> are awaiting verification. Open the orders below to confirm.</div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                @foreach ([
                    'open' => 'Open ('.$counts['open'].')',
                    'pending' => 'New ('.$counts['pending'].')',
                    'confirmed' => 'Confirmed ('.$counts['confirmed'].')',
                    'preparing' => 'Preparing ('.$counts['preparing'].')',
                    'ready' => 'Ready ('.$counts['ready'].')',
                    'dispatched' => 'Out for Delivery ('.$counts['dispatched'].')',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ] as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ url('/kitchen?status='.$key) }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="text-end">Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="{{ $order->status === 'pending' ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('kitchen.show', $order) }}" class="fw-semibold text-body">{{ $order->order_no }}</a>
                                    @if ($order->source === 'whatsapp_bot')
                                        <span class="badge bg-success-subtle text-success font-size-10">Bot</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="d-block">{{ $order->customer_name }}</span>
                                    <span class="text-muted font-size-12">{{ $order->customer_phone }}</span>
                                </td>
                                <td class="font-size-13">{{ \Illuminate\Support\Str::limit($order->itemsSummary(), 60) }}</td>
                                <td class="text-end fw-medium">{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $order->paymentMethodLabel() }}</span>
                                    @if ($order->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success d-block mt-1">Paid</span>
                                    @elseif ($order->payment_status === 'pending_verification')
                                        <span class="badge bg-warning-subtle text-warning d-block mt-1">Verify payment</span>
                                    @else
                                        <span class="badge bg-light text-dark d-block mt-1">Unpaid</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $order->statusColor() }}-subtle text-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                                <td class="text-muted font-size-12">{{ $order->created_at->diffForHumans() }}</td>
                                <td class="text-end cds-table-actions">
                                    @if ($canManage && $order->isOpen())
                                        @if ($order->nextStatus())
                                            <form action="{{ route('kitchen.status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $order->nextStatus() }}">
                                                <button class="btn btn-sm btn-primary">
                                                    @if ($order->status === 'pending') Confirm
                                                    @elseif ($order->status === 'confirmed') Start Preparing
                                                    @elseif ($order->status === 'preparing') Mark Ready
                                                    @elseif ($order->status === 'ready') Dispatch
                                                    @elseif ($order->status === 'dispatched') Delivered
                                                    @endif
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('kitchen.status', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-sm btn-outline-danger" title="Cancel"><i class="bx bx-x"></i></button>
                                        </form>
                                    @endif
                                    <a href="{{ route('kitchen.show', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-show"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No orders here. New WhatsApp orders will appear automatically with a ring.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var lastId = {{ $lastId }};
            var feedUrl = '{{ route('kitchen.feed') }}';

            function beep() {
                try {
                    var ctx = new (window.AudioContext || window.webkitAudioContext)();
                    [0, 0.35, 0.7].forEach(function (offset) {
                        var osc = ctx.createOscillator();
                        var gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 880;
                        gain.gain.value = 0.25;
                        osc.start(ctx.currentTime + offset);
                        osc.stop(ctx.currentTime + offset + 0.22);
                    });
                } catch (e) {}
            }

            function poll() {
                fetch(feedUrl + '?after=' + lastId, { headers: { 'Accept': 'application/json' } })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data.orders && data.orders.length > 0) {
                            beep();
                            var names = data.orders.map(function (order) { return order.order_no + ' — ' + order.customer; }).join(', ');
                            var toast = document.createElement('div');
                            toast.className = 'position-fixed top-0 end-0 m-3 p-3 text-bg-danger rounded shadow';
                            toast.style.zIndex = '2000';
                            toast.innerHTML = '<strong><i class="bx bx-bell bx-tada me-1"></i> New order(s) received!</strong><br>' + names;
                            document.body.appendChild(toast);
                            setTimeout(function () { window.location.reload(); }, 2500);
                        }
                        lastId = data.last_id || lastId;
                    })
                    .catch(function () {});
            }

            setInterval(poll, 15000);
        })();
    </script>
@endpush
