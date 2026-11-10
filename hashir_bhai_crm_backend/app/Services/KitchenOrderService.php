<?php

namespace App\Services;

use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Support\Activity;
use App\Support\Notifier;
use App\Support\SimplePdf;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KitchenOrderService
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    /**
     * @param  array<string, mixed>  $order
     */
    public function createFromBot(WhatsappAccount $account, WhatsappContact $contact, ?Lead $lead, array $order): KitchenOrder
    {
        $organization = $account->organization;

        $items = collect($order['items'] ?? [])
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['name'] ?? null))
            ->values();

        $subtotal = $items->sum(
            fn (array $item): float => (float) ($item['unit_price'] ?? 0) * max(1, (float) ($item['quantity'] ?? 1)),
        );

        $total = (float) ($order['total'] ?? $subtotal);
        $currency = (string) ($order['currency'] ?? $organization?->currency ?? 'USD');

        $attributes = [
            'organization_id' => $organization?->id,
            'lead_id' => $lead?->id,
            'whatsapp_contact_id' => $contact->id,
            'customer_name' => $contact->displayName(),
            'customer_phone' => $contact->phone ?? $contact->wa_id,
            'subtotal' => $subtotal,
            'total' => $total,
            'currency' => $currency,
            'status' => 'pending',
            'source' => 'whatsapp_bot',
            'meta' => ['bot_order' => $order],
        ];

        try {
            $kitchenOrder = KitchenOrder::query()->create($attributes + ['order_no' => KitchenOrder::generateNumber()]);
        } catch (QueryException) {
            $kitchenOrder = KitchenOrder::query()->create($attributes + [
                'order_no' => KitchenOrder::generateNumber().'-'.strtoupper(Str::random(3)),
            ]);
        }

        foreach ($items as $item) {
            $quantity = max(1, (float) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $kitchenOrder->items()->create([
                'organization_id' => $organization?->id,
                'product_id' => $item['product_id'] ?? null,
                'name' => (string) $item['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
            ]);
        }

        Activity::log('kitchen_order_created', 'New kitchen order '.$kitchenOrder->order_no, $kitchenOrder, ['total' => $total], 'orders');

        $this->notifyStaff(
            $organization,
            $kitchenOrder,
            'New order received — '.$kitchenOrder->order_no,
            $contact->displayName().' ordered '.$kitchenOrder->itemsSummary().' ('.number_format($total, 2).' '.$currency.')',
        );

        return $kitchenOrder;
    }

    public function updateStatus(KitchenOrder $order, string $status, ?User $actor = null, bool $notifyCustomer = true): KitchenOrder
    {
        if (! in_array($status, KitchenOrder::STATUSES, true)) {
            return $order;
        }

        $column = KitchenOrder::TIMESTAMP_COLUMNS[$status] ?? null;

        $order->fill([
            'status' => $status,
            'handled_by' => $actor?->id ?? $order->handled_by,
        ]);

        if ($column !== null) {
            $order->{$column} = now();
        }

        $order->save();

        Activity::log('kitchen_order_status', 'Order '.$order->order_no.' marked '.$status, $order, ['status' => $status], 'orders');

        if ($notifyCustomer && filled($order->customer_phone)) {
            $message = $order->messageFor($status);

            if ($message !== '') {
                $this->whatsapp->for($this->accountFor($order))->sendText((string) $order->customer_phone, $message);
            }
        }

        if ($status === 'delivered') {
            $this->sendInvoice($order);
        }

        return $order->refresh();
    }

    /**
     * @return array{ok: bool, url?: string, error?: string}
     */
    public function sendInvoice(KitchenOrder $order): array
    {
        $order->loadMissing(['items', 'organization']);

        $pdf = app(SimplePdf::class)->render($this->invoiceLines($order));
        $path = 'invoices/'.$order->order_no.'.pdf';

        Storage::disk('public')->put($path, $pdf);

        $url = url('storage/'.$path);

        $order->update(['meta' => array_merge($order->meta ?? [], ['invoice_url' => $url])]);

        $account = $this->accountFor($order);

        if (blank($order->customer_phone) || $account === null) {
            return ['ok' => false, 'error' => 'No customer phone or WhatsApp account available.'];
        }

        $result = $this->whatsapp->for($account)->sendDocument(
            (string) $order->customer_phone,
            $url,
            'invoice-'.$order->order_no.'.pdf',
            'Invoice for order '.$order->order_no,
        );

        Activity::log('kitchen_order_invoice', 'Invoice sent for '.$order->order_no, $order, [], 'orders');

        return $result['ok']
            ? ['ok' => true, 'url' => $url]
            : ['ok' => false, 'error' => $result['error'] ?? 'Invoice could not be sent.'];
    }

    public function markPayment(KitchenOrder $order, string $status, ?string $reference = null, ?User $actor = null): KitchenOrder
    {
        if (! in_array($status, KitchenOrder::PAYMENT_STATUSES, true)) {
            return $order;
        }

        $order->update([
            'payment_status' => $status,
            'payment_reference' => $reference ?? $order->payment_reference,
            'handled_by' => $actor?->id ?? $order->handled_by,
        ]);

        Activity::log('kitchen_order_payment', 'Payment '.$status.' for '.$order->order_no, $order, [], 'orders');

        if ($status === 'pending_verification') {
            $this->notifyStaff(
                $order->organization,
                $order,
                'Payment submitted — '.$order->order_no,
                $order->customer_name.' says they paid '.number_format((float) $order->total, 2).' '.$order->currency.'. Please verify.',
            );
        }

        return $order->refresh();
    }

    public function cancel(KitchenOrder $order, ?User $actor = null): KitchenOrder
    {
        return $this->updateStatus($order, 'cancelled', $actor);
    }

    private function accountFor(KitchenOrder $order): ?WhatsappAccount
    {
        $account = $order->contact?->account;

        return $account ?? WhatsappAccount::default();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function invoiceLines(KitchenOrder $order): array
    {
        $currency = $order->currency;
        $money = fn (float|string|null $value): string => number_format((float) $value, 2).' '.$currency;

        $rows = $order->items->map(fn (KitchenOrderItem $item): array => [
            $item->name,
            rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.').' x '.$money($item->unit_price),
            $money($item->total),
        ])->values()->all();

        return [
            ['type' => 'title', 'text' => $order->organization?->name ?? (string) config('app.name')],
            ['type' => 'subtitle', 'text' => 'Invoice — '.$order->order_no],
            ['type' => 'hr'],
            ['type' => 'kv', 'columns' => 2, 'rows' => [
                ['Customer', $order->customer_name],
                ['Phone', (string) ($order->customer_phone ?? '-')],
                ['Payment', $order->paymentMethodLabel().' ('.str_replace('_', ' ', $order->payment_status).')'],
                ['Date', $order->created_at->format('d M Y, h:i A')],
            ]],
            ['type' => 'spacer', 'height' => 6],
            ['type' => 'table', 'headers' => ['Item', 'Details', 'Amount'], 'widths' => [3, 2, 1], 'rows' => $rows, 'align' => 'left'],
            ['type' => 'spacer', 'height' => 6],
            ['type' => 'total', 'label' => 'Total', 'value' => $money($order->total)],
            ['type' => 'spacer', 'height' => 14],
            ['type' => 'text', 'text' => 'Thank you for your order!'],
        ];
    }

    private function notifyStaff(?Organization $organization, KitchenOrder $order, string $title, string $message): void
    {
        Notifier::send(
            Notifier::admins()
                ->merge(Notifier::withRole('kitchen-staff'))
                ->merge($organization?->owner !== null ? [$organization->owner] : []),
            $title,
            $message,
            route('kitchen.show', $order),
            'bx bx-bell',
        );
    }
}
