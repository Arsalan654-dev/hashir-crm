<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->with('assignee:id,name')
            ->withCount(['sales', 'invoices', 'leads'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('name', 'like', $search)
                    ->orWhere('company', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.customers.index', [
            'customers' => $customers,
            'stats' => [
                'total' => Customer::query()->count(),
                'active' => Customer::query()->where('status', 'active')->count(),
                'newThisMonth' => Customer::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'withWhatsapp' => Customer::query()->whereNotNull('whatsapp_number')->count(),
            ],
        ]);
    }

    public function show(Customer $customer): View
    {
        $customer->load(['assignee:id,name', 'sales' => fn ($query) => $query->orderByDesc('sale_date')->limit(10), 'leads', 'invoices']);

        return view('pages.customers.show', [
            'customer' => $customer,
            'totals' => [
                'lifetime' => $customer->lifetimeValue(),
                'orders' => $customer->sales()->count(),
                'outstanding' => (float) $customer->invoices()->sum(DB::raw('amount - paid_amount')),
                'openLeads' => $customer->leads()->open()->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCustomer($request);

        $customer = Customer::query()->create($data + [
            'joining_date' => now()->toDateString(),
            'rating' => 0,
        ]);

        Activity::log('created', 'Customer created: '.$customer->name, $customer, [], 'customers');

        return back()->with('status', 'Customer created.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validateCustomer($request, $customer);

        $customer->update($data);

        Activity::log('updated', 'Customer updated: '.$customer->name, $customer, [], 'customers');

        return back()->with('status', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        Activity::log('deleted', 'Customer deleted: '.$customer->name, null, ['customer_id' => $customer->id], 'customers');
        $customer->delete();

        return back()->with('status', 'Customer deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive,blocked'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
