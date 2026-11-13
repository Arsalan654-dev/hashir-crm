<?php

namespace App\Http\Controllers;

use App\Models\KitchenOrder;
use App\Models\Setting;
use App\Services\KitchenOrderService;
use App\Support\BotSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentPageController extends Controller
{
    public function show(Request $request, KitchenOrder $order): View
    {
        $this->authorizeToken($request, $order);

        $order->load(['items', 'organization']);

        return view('pages.pay', [
            'order' => $order,
            'bankDetails' => (string) BotSettings::get($order->organization, 'billing.bank_details', (string) Setting::getValue('billing.bank_details', '')),
            'instructions' => (string) BotSettings::get($order->organization, 'billing.instructions', (string) Setting::getValue('billing.instructions', '')),
            'token' => (string) $order->payment_token,
        ]);
    }

    public function submit(Request $request, KitchenOrder $order): RedirectResponse
    {
        $this->authorizeToken($request, $order);

        $data = $request->validate([
            'reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        app(KitchenOrderService::class)->markPayment(
            $order,
            'pending_verification',
            $data['reference'] ?? null,
        );

        return back()->with('status', 'Thank you! Your payment has been submitted and is being verified.');
    }

    private function authorizeToken(Request $request, KitchenOrder $order): void
    {
        $token = (string) $request->input('t', $request->query('t', ''));

        abort_unless(
            filled($order->payment_token) && hash_equals((string) $order->payment_token, $token),
            404,
        );
    }
}
