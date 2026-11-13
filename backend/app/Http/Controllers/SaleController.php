<?php

namespace App\Http\Controllers;

use App\Models\CommissionRule;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Setting;
use App\Models\User;
use App\Support\Activity;
use App\Support\Notifier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $sales = $this->filteredQuery($request)
            ->with(['salesperson:id,name,avatar', 'items'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.sales.index', [
            'sales' => $sales,
            'salespeople' => User::query()->whereIn('role_id', $this->salesRoleIds())->orderBy('name')->get(['id', 'name']),
            'totals' => $this->totals($request),
            'filters' => $request->only(['search', 'status', 'payment_status', 'salesperson', 'from', 'to']),
        ]);
    }

    public function my(Request $request): View
    {
        $sales = Sale::query()
            ->where('salesperson_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('sale_date')
            ->paginate(20);

        return view('pages.sales.mine', [
            'sales' => $sales,
            'summary' => [
                'total' => (float) Sale::query()->where('salesperson_id', $request->user()->id)->completed()->sum('total'),
                'month' => (float) Sale::query()->where('salesperson_id', $request->user()->id)->completed()
                    ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
                'today' => (float) Sale::query()->where('salesperson_id', $request->user()->id)->completed()
                    ->whereDate('sale_date', today())->sum('total'),
                'commission' => (float) Sale::query()->where('salesperson_id', $request->user()->id)->completed()->sum('commission_amount'),
                'pending' => Sale::query()->where('salesperson_id', $request->user()->id)->where('status', 'pending')->count(),
                'count' => Sale::query()->where('salesperson_id', $request->user()->id)->completed()->count(),
            ],
            'targets' => $request->user()->targets()->orderByDesc('period_start')->limit(3)->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.sales.form', [
            'sale' => new Sale(['sale_date' => now()->toDateString(), 'currency' => Setting::getValue('company.currency', 'USD')]),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(),
            'leads' => Lead::query()->open()->orderByDesc('created_at')->limit(300)->get(),
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSale($request);

        $sale = DB::transaction(function () use ($data, $request) {
            $sale = Sale::query()->create([
                'sale_no' => Sale::generateNumber(),
                'salesperson_id' => $data['salesperson_id'] ?? $request->user()->id,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
                'status' => 'pending',
                'sale_date' => $data['sale_date'],
                'source' => $data['source'] ?? 'manual',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discount = (float) ($item['discount'] ?? 0);
                $product = filled($item['product_id'] ?? null) ? Product::query()->find($item['product_id']) : null;

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product?->id,
                    'name' => $item['name'] ?? $product?->name ?? 'Item',
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost' => (float) ($item['cost'] ?? $product?->cost ?? 0),
                    'discount' => $discount,
                    'total' => max(0, ($quantity * $unitPrice) - $discount),
                ]);
            }

            $sale->refresh()->load('items');
            $sale->recalculate();
            $sale->update(['commission_amount' => $this->commissionFor($sale)]);

            if ($sale->lead_id) {
                $sale->lead?->update(['last_contacted_at' => now()]);
            }

            return $sale;
        });

        Activity::log('created', 'Sale entry created: '.$sale->sale_no, $sale, [], 'sales');

        $salesperson = User::query()->find($sale->salesperson_id);

        if ($salesperson !== null) {
            Notifier::send(
                Notifier::managersOf($salesperson),
                'New sale entry: '.$sale->sale_no,
                $salesperson->name.' added a sale of '.number_format((float) $sale->total, 2).' '.$sale->currency,
                route('sales.show', $sale),
                'bx bx-cart-add',
            );
        }

        if ($request->user()->can('sales.confirm') || $request->boolean('confirm_now')) {
            return redirect()->route('sales.show', $sale)->with('status', 'Sale entry created. You can confirm it now.');
        }

        return redirect()->route('sales.my')->with('status', 'Sale entry submitted for approval.');
    }

    public function show(Sale $sale): View
    {
        $this->authorizeView($sale);

        $sale->load(['salesperson:id,name,avatar,email', 'items.product', 'payments.receiver', 'customer', 'lead', 'invoice', 'doneBy:id,name', 'confirmedBy:id,name']);

        return view('pages.sales.show', [
            'sale' => $sale,
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit(Sale $sale): View
    {
        $this->authorizeView($sale);

        return view('pages.sales.form', [
            'sale' => $sale->load('items'),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(),
            'leads' => Lead::query()->orderByDesc('created_at')->limit(300)->get(),
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $this->authorizeView($sale);

        if ($sale->status === 'done' && ! $request->user()->can('sales.mark_done')) {
            return back()->withErrors(['sale' => 'Completed sales can only be edited by an administrator.']);
        }

        $data = $this->validateSale($request);

        DB::transaction(function () use ($data, $sale) {
            $sale->update([
                'salesperson_id' => $data['salesperson_id'] ?? $sale->salesperson_id,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'currency' => $data['currency'] ?? $sale->currency,
                'sale_date' => $data['sale_date'],
                'source' => $data['source'] ?? $sale->source,
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->items()->delete();

            foreach ($data['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discount = (float) ($item['discount'] ?? 0);
                $product = filled($item['product_id'] ?? null) ? Product::query()->find($item['product_id']) : null;

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product?->id,
                    'name' => $item['name'] ?? $product?->name ?? 'Item',
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost' => (float) ($item['cost'] ?? $product?->cost ?? 0),
                    'discount' => $discount,
                    'total' => max(0, ($quantity * $unitPrice) - $discount),
                ]);
            }

            $sale->refresh()->load(['items', 'payments']);
            $sale->recalculate();
            $sale->update(['commission_amount' => $this->commissionFor($sale)]);
        });

        Activity::log('updated', 'Sale updated: '.$sale->sale_no, $sale, [], 'sales');

        return redirect()->route('sales.show', $sale)->with('status', 'Sale updated successfully.');
    }

    public function destroy(Request $request, Sale $sale): RedirectResponse
    {
        $this->authorizeView($sale);

        Activity::log('deleted', 'Sale deleted: '.$sale->sale_no, null, ['sale_id' => $sale->id], 'sales');
        $sale->delete();

        return redirect()->route('sales.index')->with('status', 'Sale deleted.');
    }

    public function confirm(Request $request, Sale $sale): RedirectResponse
    {
        $sale->forceFill([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $request->user()->id,
            'commission_amount' => $this->commissionFor($sale),
        ])->save();

        $this->syncCustomer($sale);
        $this->logLeadActivity($sale, 'status_change', 'Sale '.$sale->sale_no.' confirmed.');

        Activity::log('confirmed', 'Sale confirmed: '.$sale->sale_no, $sale, [], 'sales');

        return back()->with('status', 'Sale confirmed and commission calculated.');
    }

    public function markDone(Request $request, Sale $sale): RedirectResponse
    {
        $sale->forceFill([
            'status' => 'done',
            'done_at' => now(),
            'done_by' => $request->user()->id,
            'confirmed_at' => $sale->confirmed_at ?? now(),
            'confirmed_by' => $sale->confirmed_by ?? $request->user()->id,
            'commission_amount' => $this->commissionFor($sale),
        ])->save();

        $this->syncCustomer($sale);
        $this->logLeadActivity($sale, 'status_change', 'Sale '.$sale->sale_no.' marked as done.');

        Activity::log('done', 'Sale marked done: '.$sale->sale_no, $sale, [], 'sales');

        return back()->with('status', 'Sale marked as done. This sale now belongs to the salesperson.');
    }

    public function reject(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $sale->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'],
        ]);

        $this->logLeadActivity($sale, 'status_change', 'Sale '.$sale->sale_no.' rejected: '.$data['admin_note']);

        Activity::log('rejected', 'Sale rejected: '.$sale->sale_no, $sale, [], 'sales');

        return back()->with('status', 'Sale rejected.');
    }

    public function storePayment(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:30'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        SalePayment::query()->create($data + [
            'sale_id' => $sale->id,
            'received_by' => $request->user()->id,
        ]);

        $sale->refresh()->load(['items', 'payments']);
        $sale->recalculate();

        Activity::log('payment', 'Payment recorded for '.$sale->sale_no, $sale, ['amount' => $data['amount']], 'sales');

        return back()->with('status', 'Payment recorded.');
    }

    public function generateInvoice(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->invoice_id) {
            return back()->with('status', 'This sale already has an invoice.');
        }

        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-'.now()->format('Y').'-'.str_pad((string) (Invoice::query()->whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT),
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'billing_name' => $sale->customer_name,
            'email' => $sale->customer_email,
            'phone' => $sale->customer_phone,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'pending',
            'amount' => $sale->total,
            'paid_amount' => $sale->paid_amount,
            'balance' => $sale->balance(),
            'currency' => $sale->currency,
            'tax' => $sale->tax,
            'discount' => $sale->discount,
            'description' => $sale->title,
            'created_by' => $request->user()->id,
        ]);

        $sale->load('items');

        foreach ($sale->items as $item) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->unit_price,
                'quantity' => (int) $item->quantity,
            ]);
        }

        $sale->update(['invoice_id' => $invoice->id]);

        Activity::log('invoice', 'Invoice generated for '.$sale->sale_no, $invoice, [], 'invoices');

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice generated.');
    }

    public function payments(Request $request): View
    {
        $payments = SalePayment::query()
            ->with(['sale:id,sale_no,customer_name,salesperson_id,total,currency', 'receiver:id,name'])
            ->when(! $request->user()->isSuperAdmin() && ! $request->user()->can('sales.view_all'),
                fn ($query) => $query->whereHas('sale', fn ($q) => $q->where('salesperson_id', $request->user()->id)))
            ->orderByDesc('paid_on')
            ->paginate(25);

        return view('pages.sales.payments', [
            'payments' => $payments,
            'totalReceived' => (float) SalePayment::query()->sum('amount'),
            'receivedThisMonth' => (float) SalePayment::query()->whereBetween('paid_on', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'outstanding' => (float) Sale::query()->completed()->sum(DB::raw('total - paid_amount')),
        ]);
    }

    public function reports(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();

        $base = Sale::query()->completed()->whereBetween('sale_date', [$from, $to]);

        $bySalesperson = (clone $base)
            ->selectRaw('salesperson_id, count(*) as orders, sum(total) as total, sum(commission_amount) as commission')
            ->with('salesperson:id,name,avatar')
            ->groupBy('salesperson_id')
            ->orderByDesc('total')
            ->get();

        $byDay = (clone $base)
            ->selectRaw('date(sale_date) as bucket, sum(total) as total, count(*) as orders')
            ->groupBy('bucket')->orderBy('bucket')->get();

        $byStatus = (clone $base)->selectRaw('status, count(*) as total, sum(total) as amount')
            ->groupBy('status')->pluck('total', 'status');

        $bySource = (clone $base)->selectRaw('source, count(*) as total, sum(total) as amount')
            ->groupBy('source')->orderByDesc('amount')->get();

        return view('pages.sales.reports', [
            'from' => $from,
            'to' => $to,
            'totals' => [
                'revenue' => (float) (clone $base)->sum('total'),
                'cost' => (float) (clone $base)->sum('cost'),
                'commission' => (float) (clone $base)->sum('commission_amount'),
                'orders' => (clone $base)->count(),
                'average' => (float) round((clone $base)->avg('total') ?? 0, 2),
            ],
            'bySalesperson' => $bySalesperson,
            'byDay' => $byDay,
            'byStatus' => $byStatus,
            'bySource' => $bySource,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request)->with('salesperson:id,name')->orderByDesc('sale_date');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sale No', 'Date', 'Salesperson', 'Customer', 'Phone', 'Total', 'Paid', 'Balance', 'Payment Status', 'Status', 'Source']);

            $query->chunk(500, function (Collection $sales) use ($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->sale_no,
                        $sale->sale_date->toDateString(),
                        $sale->salesperson?->name,
                        $sale->customer_name,
                        $sale->customer_phone,
                        number_format((float) $sale->total, 2, '.', ''),
                        number_format((float) $sale->paid_amount, 2, '.', ''),
                        number_format($sale->balance(), 2, '.', ''),
                        $sale->payment_status,
                        $sale->status,
                        $sale->source,
                    ]);
                }
            });

            fclose($handle);
        }, 'sales-export-'.now()->format('Y-m-d').'.csv');
    }

    /**
     * @return Builder<Sale>
     */
    private function filteredQuery(Request $request): Builder
    {
        return Sale::query()
            ->forUser($request->user())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($search) {
                    $query->where('sale_no', 'like', $search)
                        ->orWhere('customer_name', 'like', $search)
                        ->orWhere('customer_phone', 'like', $search)
                        ->orWhere('title', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('salesperson'), fn ($query) => $query->where('salesperson_id', $request->integer('salesperson')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('sale_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('sale_date', '<=', $request->date('to')));
    }

    /**
     * @return array<string, mixed>
     */
    private function totals(Request $request): array
    {
        $query = $this->filteredQuery($request);

        return [
            'count' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('total'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'done' => (clone $query)->where('status', 'done')->count(),
            'commission' => (float) (clone $query)->sum('commission_amount'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSale(Request $request): array
    {
        $data = $request->validate([
            'salesperson_id' => ['nullable', 'exists:users,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'sale_date' => ['required', 'date'],
            'source' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! $request->user()->can('sales.view_all') && ! $request->user()->isSuperAdmin()) {
            $data['salesperson_id'] = $request->user()->id;
        }

        return $data;
    }

    private function commissionFor(Sale $sale): float
    {
        $amount = (float) $sale->total;

        $rules = CommissionRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($sale) {
                $query->whereNull('user_id')->orWhere('user_id', $sale->salesperson_id);
            })
            ->get()
            ->filter(fn (CommissionRule $rule) => $rule->isActiveOn($sale->sale_date->toDateString()));

        $rule = $rules->firstWhere('user_id', $sale->salesperson_id)
            ?? $rules->firstWhere('applies_to', 'all');

        if ($rule !== null) {
            return $rule->calculate($amount);
        }

        $person = $sale->salesperson;
        $rate = (float) ($person?->commission_rate ?? 0);

        if ($rate <= 0) {
            $rate = (float) Setting::getValue('sales.commission_rate', 0);
        }

        return round($amount * ($rate / 100), 2);
    }

    private function syncCustomer(Sale $sale): void
    {
        if ($sale->customer_id || blank($sale->customer_phone)) {
            return;
        }

        $customer = Customer::query()->firstOrCreate(
            ['phone' => $sale->customer_phone],
            [
                'name' => $sale->customer_name,
                'email' => $sale->customer_email,
                'whatsapp_number' => $sale->customer_phone,
                'assigned_to' => $sale->salesperson_id,
                'joining_date' => now()->toDateString(),
            ],
        );

        $sale->update(['customer_id' => $customer->id]);
    }

    private function logLeadActivity(Sale $sale, string $type, string $body): void
    {
        if (! $sale->lead_id) {
            return;
        }

        LeadActivity::query()->create([
            'lead_id' => $sale->lead_id,
            'user_id' => auth()->id(),
            'type' => $type,
            'title' => 'Sale '.$sale->sale_no,
            'body' => $body,
            'happened_at' => now(),
        ]);

        if (in_array($sale->status, ['confirmed', 'done'], true)) {
            $sale->lead?->update([
                'status' => 'won',
                'converted_at' => now(),
                'last_contacted_at' => now(),
            ]);
        }
    }

    private function authorizeView(Sale $sale): void
    {
        $user = auth()->user();

        if ($user === null) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->can('sales.view_all') || $sale->salesperson_id === $user->id) {
            return;
        }

        if ($user->teamMemberIds()->contains((int) $sale->salesperson_id)) {
            return;
        }

        abort(403, '403 — You cannot access this.');
    }

    /**
     * @return array<int, int>
     */
    private function salesRoleIds(): array
    {
        return Role::query()->whereIn('slug', ['sales-person', 'sales-manager'])->pluck('id')->all();
    }
}
