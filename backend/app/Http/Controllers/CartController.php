<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function cart(): View
    {
        $cart = $this->userCart();
        $items = $cart?->items()->with('product')->get() ?? collect();

        return view('pages.ecommerce-cart', [
            'items' => $items,
            'subtotal' => $items->sum(fn ($item) => $item->price * $item->quantity),
            'tax' => $items->sum(fn ($item) => $item->price * $item->quantity) * 0.1,
        ]);
    }

    public function checkout(): View
    {
        $cart = $this->userCart();
        $items = $cart?->items()->with('product')->get() ?? collect();
        $subtotal = $items->sum(fn ($item) => $item->price * $item->quantity);

        return view('pages.ecommerce-checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $subtotal * 0.1,
            'shipping' => 0,
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $cart = $this->userCart();

        if ($cart === null) {
            $cart = Cart::create(['user_id' => auth()->id() ?? 1]);
        }

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item !== null) {
            $item->increment('quantity');
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        return redirect('/ecommerce-cart')->with('status', 'Added to cart.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->update(['quantity' => $request->integer('quantity')]);

        return back();
    }

    public function remove(CartItem $item): RedirectResponse
    {
        if (auth()->id() === null || $item->cart?->user_id === auth()->id()) {
            $item->delete();
        }

        return back()->with('status', 'Removed from cart.');
    }

    private function userCart(): ?Cart
    {
        $userId = auth()->id() ?? 1;

        return Cart::query()->with('items')->where('user_id', $userId)->first();
    }
}
