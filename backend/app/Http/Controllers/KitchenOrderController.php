<?php

namespace App\Http\Controllers;

use App\Models\KitchenOrder;
use App\Services\KitchenOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitchenOrderController extends Controller
{
    public function __construct(private readonly KitchenOrderService $orders) {}

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'open');

        $query = KitchenOrder::query()->with(['items', 'lead:id,name,lead_no', 'handler:id,name']);

        $query = match (true) {
            $status === 'open' => $query->open(),
            $status === 'today' => $query->whereDate('created_at', today()),
            in_array($status, KitchenOrder::STATUSES, true) => $query->where('status', $status),
            default => $query->open(),
        };

        $orders = $query
            ->orderByRaw("case status when 'pending' then 0 when 'confirmed' then 1 when 'preparing' then 2 when 'ready' then 3 when 'dispatched' then 4 else 5 end")
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('pages.orders.kitchen', [
            'orders' => $orders,
            'status' => $status,
            'counts' => [
                'pending' => KitchenOrder::query()->where('status', 'pending')->count(),
                'confirmed' => KitchenOrder::query()->where('status', 'confirmed')->count(),
                'preparing' => KitchenOrder::query()->where('status', 'preparing')->count(),
                'ready' => KitchenOrder::query()->where('status', 'ready')->count(),
                'dispatched' => KitchenOrder::query()->where('status', 'dispatched')->count(),
                'open' => KitchenOrder::query()->open()->count(),
                'delivered_today' => KitchenOrder::query()->where('status', 'delivered')->whereDate('delivered_at', today())->count(),
                'revenue_today' => (float) KitchenOrder::query()->whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total'),
                'pending_payments' => KitchenOrder::query()->where('payment_status', 'pending_verification')->count(),
            ],
            'canManage' => $request->user()->isSuperAdmin() || $request->user()->can('orders.manage'),
            'lastId' => (int) KitchenOrder::query()->max('id'),
        ]);
    }

    public function show(KitchenOrder $order): View
    {
        $order->load(['items.product', 'lead', 'contact', 'handler:id,name']);

        return view('pages.orders.kitchen-show', [
            'order' => $order,
            'canManage' => auth()->user()?->isSuperAdmin() || auth()->user()?->can('orders.manage'),
        ]);
    }

    /**
     * Polling endpoint that powers the new-order bell on the kitchen screen.
     */
    public function feed(Request $request): JsonResponse
    {
        $after = (int) $request->query('after', 0);

        $newOrders = KitchenOrder::query()
            ->where('id', '>', $after)
            ->where('status', 'pending')
            ->with('items')
            ->orderBy('id')
            ->get();

        return response()->json([
            'orders' => $newOrders->map(fn (KitchenOrder $order): array => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'customer' => $order->customer_name,
                'summary' => $order->itemsSummary(),
                'total' => number_format((float) $order->total, 2).' '.$order->currency,
            ]),
            'pending_count' => KitchenOrder::query()->where('status', 'pending')->count(),
            'last_id' => (int) KitchenOrder::query()->max('id'),
        ]);
    }

    public function updateStatus(Request $request, KitchenOrder $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(KitchenOrder::STATUSES)],
        ]);

        $this->orders->updateStatus($order, $data['status'], $request->user());

        return back()->with('status', 'Order '.$order->order_no.' marked '.$order->refresh()->statusLabel().' — customer notified on WhatsApp.');
    }

    public function sendInvoice(Request $request, KitchenOrder $order): RedirectResponse
    {
        $result = $this->orders->sendInvoice($order);

        return $result['ok']
            ? back()->with('status', 'Invoice PDF sent to '.$order->customer_name.' on WhatsApp.')
            : back()->withErrors(['order' => $result['error'] ?? 'Invoice could not be sent.']);
    }

    public function markPayment(Request $request, KitchenOrder $order): RedirectResponse
    {
        $data = $request->validate([
            'payment_status' => ['required', Rule::in(KitchenOrder::PAYMENT_STATUSES)],
            'reference' => ['nullable', 'string', 'max:150'],
        ]);

        $this->orders->markPayment($order, $data['payment_status'], $data['reference'] ?? null, $request->user());

        return back()->with('status', 'Payment updated for '.$order->order_no.'.');
    }
}
