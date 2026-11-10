<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()->with('customer')->orderByDesc('order_date')->get();

        return view('pages.ecommerce-orders', [
            'orders' => $orders,
            'activeOrders' => Order::query()->whereNotIn('order_status', ['Delivered', 'Cancelled'])->count(),
            'unfulfilled' => Order::query()->where('order_status', 'Processing')->count(),
            'pendingReplace' => Order::query()->where('payment_status', 'Pending')->count(),
            'fulfilled' => Order::query()->where('order_status', 'Delivered')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'billing_name' => ['required', 'string', 'max:255'],
            'order_date' => ['nullable', 'date'],
            'total' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        Order::create($data + [
            'order_id' => '#DS'.random_int(1000, 9999),
            'order_status' => 'Processing',
        ]);

        return redirect('/ecommerce-orders')->with('status', 'Order created.');
    }
}
