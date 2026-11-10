<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\WhatsappContact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));
        $user = $request->user();

        $results = [
            'leads' => collect(),
            'sales' => collect(),
            'customers' => collect(),
            'products' => collect(),
            'employees' => collect(),
            'whatsapp' => collect(),
            'invoices' => collect(),
        ];

        if (mb_strlen($term) >= 2) {
            $like = '%'.$term.'%';

            if ($user->can('leads.view')) {
                $results['leads'] = Lead::query()
                    ->forUser($user)
                    ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('email', 'like', $like)->orWhere('lead_no', 'like', $like))
                    ->limit(10)->get();
            }

            if ($user->can('sales.view')) {
                $results['sales'] = Sale::query()
                    ->forUser($user)
                    ->where(fn ($query) => $query->where('sale_no', 'like', $like)->orWhere('customer_name', 'like', $like)->orWhere('customer_phone', 'like', $like))
                    ->limit(10)->get();
            }

            if ($user->can('customers.view')) {
                $results['customers'] = Customer::query()
                    ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('company', 'like', $like))
                    ->limit(10)->get();
            }

            if ($user->can('products.view')) {
                $results['products'] = Product::query()
                    ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('sku', 'like', $like))
                    ->limit(10)->get();
            }

            if ($user->can('employees.view')) {
                $results['employees'] = User::query()
                    ->whereNotNull('role_id')
                    ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('employee_code', 'like', $like))
                    ->limit(10)->get();
            }

            if ($user->can('whatsapp.view')) {
                $results['whatsapp'] = WhatsappContact::query()
                    ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('wa_id', 'like', $like))
                    ->limit(10)->get();
            }

            if ($user->can('invoices.view')) {
                $results['invoices'] = Invoice::query()
                    ->where(fn ($query) => $query->where('invoice_no', 'like', $like)->orWhere('billing_name', 'like', $like))
                    ->limit(10)->get();
            }
        }

        return view('pages.search', [
            'term' => $term,
            'results' => $results,
            'total' => collect($results)->sum(fn ($collection) => $collection->count()),
        ]);
    }
}
