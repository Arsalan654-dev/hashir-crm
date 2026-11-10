<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Pay for Order {{ $order->order_no }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="authentication-bg min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h4 class="mb-1">{{ $order->organization?->name ?? config('app.name') }}</h4>
                            <p class="text-muted mb-0">Payment for order <strong>{{ $order->order_no }}</strong></p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="table-responsive mb-3">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                                            <td class="text-end">{{ number_format((float) $item->total, 2) }} {{ $order->currency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Total</td>
                                        <td class="text-end fw-bold">{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if (filled($bankDetails) || filled($instructions))
                            <div class="alert alert-info">
                                @if (filled($instructions))
                                    <p class="mb-2">{{ $instructions }}</p>
                                @endif
                                @if (filled($bankDetails))
                                    <pre class="mb-0" style="white-space: pre-wrap;">{{ $bankDetails }}</pre>
                                @endif
                            </div>
                        @endif

                        @if ($order->payment_status === 'pending_verification')
                            <div class="alert alert-warning mb-0">
                                <i class="bx bx-time me-1"></i> Your payment is being verified. We will confirm it shortly.
                            </div>
                        @elseif ($order->payment_status === 'paid')
                            <div class="alert alert-success mb-0">
                                <i class="bx bx-check-circle me-1"></i> This order is already paid. Thank you!
                            </div>
                        @else
                            <h6 class="mb-3">Already paid? Let us know:</h6>
                            <form action="{{ route('pay.submit', $order) }}" method="POST" class="row g-3">
                                @csrf
                                <input type="hidden" name="t" value="{{ $token }}">
                                <div class="col-md-6">
                                    <label class="form-label">Payment Reference / TXN ID</label>
                                    <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="e.g. bank transaction ID">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Note (optional)</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-success w-100"><i class="bx bx-check me-1"></i> I have paid</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
                <p class="text-center text-muted font-size-13">Need help? Message us on WhatsApp.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
