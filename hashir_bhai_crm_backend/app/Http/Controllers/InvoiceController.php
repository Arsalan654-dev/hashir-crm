<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use App\Services\GmailService;
use App\Services\WhatsappService;
use App\Support\Activity;
use App\Support\Branding;
use App\Support\SimplePdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with(['customer:id,name', 'sale:id,sale_no'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('invoice_no', 'like', $search)->orWhere('billing_name', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.invoices.index', [
            'invoices' => $invoices,
            'stats' => [
                'count' => Invoice::query()->count(),
                'billed' => (float) Invoice::query()->sum('amount'),
                'collected' => (float) Invoice::query()->sum('paid_amount'),
                'outstanding' => (float) Invoice::query()->sum(DB::raw('amount - paid_amount')),
                'overdue' => Invoice::query()->whereNotIn('status', ['paid', 'cancelled'])->whereDate('due_date', '<', today())->count(),
            ],
        ]);
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['items', 'customer', 'sale', 'creator:id,name']);

        return view('pages.invoices.show', [
            'invoice' => $invoice,
            'whatsappConfigured' => app(WhatsappService::class)->isConfigured(),
            'gmailConfigured' => GmailAccount::query()->where('is_active', true)->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'billing_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-'.now()->format('Y').'-'.str_pad((string) (Invoice::query()->whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT),
            'customer_id' => $data['customer_id'] ?? null,
            'billing_name' => $data['billing_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'tax' => $data['tax'] ?? 0,
            'discount' => $data['discount'] ?? 0,
            'shipping' => $data['shipping'] ?? 0,
            'currency' => $data['currency'] ?? Setting::getValue('company.currency', 'USD'),
            'terms' => $data['terms'] ?? null,
            'amount' => 0,
            'created_by' => $request->user()->id,
        ]);

        $this->syncItems($invoice, $data['items']);

        Activity::log('created', 'Invoice created: '.$invoice->invoice_no, $invoice, [], 'invoices');

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created.');
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'billing_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        $invoice->fill(collect($data)->except('items')->all());

        if ($request->has('paid_amount')) {
            $invoice->paid_amount = $data['paid_amount'];
        }

        $invoice->save();

        if (filled($data['items'] ?? null)) {
            $this->syncItems($invoice, $data['items']);
        } else {
            $this->recalculate($invoice);
        }

        Activity::log('updated', 'Invoice updated: '.$invoice->invoice_no, $invoice, [], 'invoices');

        return back()->with('status', 'Invoice updated.');
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        if (! $request->user()->can('invoices.delete')) {
            abort(403, '403 — You cannot access this.');
        }

        Activity::log('deleted', 'Invoice deleted: '.$invoice->invoice_no, null, ['invoice_id' => $invoice->id], 'invoices');
        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Invoice deleted.');
    }

    public function send(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'via' => ['required', 'in:whatsapp,email'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $invoice->load('items', 'customer');

        $pdf = $this->buildPdf($invoice);
        $filename = $invoice->invoice_no.'.pdf';
        $storedPath = 'invoices/'.$filename;

        Storage::disk('public')->put($storedPath, $pdf);

        if ($data['via'] === 'whatsapp') {
            $number = $invoice->phone ?? $invoice->customer?->whatsapp_number ?? $invoice->customer?->phone;

            if (blank($number)) {
                return back()->withErrors(['invoice' => 'No WhatsApp number available for this invoice.']);
            }

            $service = app(WhatsappService::class);

            if (! $service->isConfigured()) {
                return back()->withErrors(['invoice' => 'WhatsApp Cloud API is not configured.']);
            }

            $result = $service->sendDocument(
                $number,
                url('storage/'.$storedPath),
                $filename,
                $data['message'] ?? ('Invoice '.$invoice->invoice_no.' from '.Branding::name()),
            );

            if ($result['ok'] !== true) {
                return back()->withErrors(['invoice' => $result['error'] ?? 'WhatsApp send failed.']);
            }
        } else {
            $account = GmailAccount::query()->where('is_active', true)->orderByDesc('id')->first();

            if ($account === null || blank($invoice->email)) {
                return back()->withErrors(['invoice' => 'Connect Gmail and add a customer email before sending.']);
            }

            $result = app(GmailService::class)->sendMessage(
                $account,
                $invoice->email,
                'Invoice '.$invoice->invoice_no.' from '.Branding::name(),
                $data['message'] ?? 'Please find your invoice attached.',
                attachments: [[
                    'name' => $filename,
                    'mime' => 'application/pdf',
                    'data' => $pdf,
                ]],
            );

            if ($result['ok'] !== true) {
                return back()->withErrors(['invoice' => $result['error'] ?? 'Email send failed.']);
            }
        }

        $invoice->update(['sent_at' => now(), 'sent_via' => $data['via']]);

        Activity::log('sent', 'Invoice '.$invoice->invoice_no.' sent via '.$data['via'], $invoice, [], 'invoices');

        return back()->with('status', 'Invoice sent via '.$data['via'].'.');
    }

    public function pdf(Request $request, Invoice $invoice): Response
    {
        $invoice->load('items');

        return app(SimplePdf::class)->respond($this->buildPdf($invoice), $invoice->invoice_no.'.pdf');
    }

    private function buildPdf(Invoice $invoice): string
    {
        $currency = $invoice->currency ?? Setting::getValue('company.currency', 'USD');
        $money = fn (float|string|null $value) => $currency.' '.number_format((float) $value, 2);

        $rows = $invoice->items->map(fn (InvoiceItem $item) => [
            $item->name,
            (string) $item->quantity,
            $money($item->price),
            $money((float) $item->price * (float) $item->quantity),
        ])->values()->all();

        return app(SimplePdf::class)->render([
            ['type' => 'title', 'text' => (string) Setting::getValue('company.name', Branding::NAME)],
            ['type' => 'subtitle', 'text' => (string) Setting::getValue('company.address', '')],
            ['type' => 'subtitle', 'text' => trim((string) Setting::getValue('company.email', '').'  '.(string) Setting::getValue('company.phone', ''))],
            ['type' => 'hr'],
            ['type' => 'heading', 'text' => 'Invoice '.$invoice->invoice_no],
            ['type' => 'kv', 'columns' => 2, 'rows' => [
                ['Billed To', (string) $invoice->billing_name],
                ['Invoice Date', $invoice->invoice_date?->format('d M Y') ?? '-'],
                ['Email', (string) ($invoice->email ?? '-')],
                ['Due Date', $invoice->due_date?->format('d M Y') ?? '-'],
                ['Phone', (string) ($invoice->phone ?? '-')],
                ['Status', ucfirst((string) $invoice->status)],
            ]],
            ['type' => 'spacer', 'height' => 8],
            ['type' => 'table', 'headers' => ['Description', 'Qty', 'Rate', 'Amount'], 'widths' => [4, 1, 1.4, 1.6], 'rows' => $rows],
            ['type' => 'spacer', 'height' => 8],
            ['type' => 'kv', 'columns' => 2, 'rows' => [
                ['Subtotal', $money((float) $invoice->amount + (float) $invoice->discount - (float) $invoice->tax)],
                ['Discount', $money($invoice->discount)],
                ['Tax', $money($invoice->tax)],
                ['Shipping', $money($invoice->shipping)],
                ['Paid', $money($invoice->paid_amount)],
                ['Balance Due', $money($invoice->outstanding())],
            ]],
            ['type' => 'spacer', 'height' => 10],
            ['type' => 'total', 'label' => 'Total', 'value' => $money($invoice->amount)],
            ['type' => 'spacer', 'height' => 14],
            ['type' => 'text', 'text' => (string) ($invoice->terms ?: 'Thank you for your business.')],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'quantity' => (int) $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        $this->recalculate($invoice);
    }

    private function recalculate(Invoice $invoice): void
    {
        $invoice->load('items');

        $subtotal = (float) $invoice->items->sum(fn (InvoiceItem $item) => (float) $item->price * (float) $item->quantity);

        $invoice->amount = max(0, $subtotal - (float) $invoice->discount + (float) $invoice->tax + (float) $invoice->shipping);
        $invoice->balance = max(0, (float) $invoice->amount - (float) $invoice->paid_amount);

        $invoice->status = match (true) {
            $invoice->paid_amount > 0 && $invoice->balance <= 0.009 => 'paid',
            $invoice->due_date !== null && $invoice->due_date->isPast() && $invoice->balance > 0 => 'overdue',
            default => $invoice->status === 'draft' ? 'draft' : 'pending',
        };

        $invoice->save();
    }
}
