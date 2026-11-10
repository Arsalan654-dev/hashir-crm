<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\BotConversation;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Target;
use App\Models\Todo;
use App\Models\WhatsappContact;
use App\Support\Analytics;
use App\Support\Branding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $analytics = new Analytics($user);

        $data = [
            'kpis' => $analytics->kpis(),
            'charts' => $analytics->salesChart('monthly'),
            'daily' => $analytics->salesTrends()['daily'],
            'breakdowns' => $analytics->breakdowns(),
            'companyWide' => $analytics->company_wide ?? true,
            'branding' => Branding::toArray(),
        ];

        if ($user->can('sales.view')) {
            $data['recentSales'] = Sale::query()
                ->with('salesperson:id,name,avatar')
                ->forUser($user)
                ->orderByDesc('sale_date')
                ->limit(6)
                ->get();
        }

        if ($user->can('leads.view')) {
            $data['recentLeads'] = Lead::query()
                ->with('assignedUser:id,name,avatar')
                ->forUser($user)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();
        }

        if ($user->can('whatsapp.view')) {
            $data['recentWhatsapp'] = WhatsappContact::query()
                ->orderByDesc('last_message_at')
                ->limit(5)
                ->get();
        }

        if ($user->can('bot.view')) {
            $data['recentBots'] = BotConversation::query()->orderByDesc('created_at')->limit(5)->get();
        }

        if ($user->can('customers.view')) {
            $data['newCustomers'] = Customer::query()->orderByDesc('created_at')->limit(5)->get();
        }

        if ($user->can('attendance.view_own', 'attendance.view_all', 'attendance.manage')) {
            $data['myAttendanceToday'] = AttendanceRecord::query()
                ->where('user_id', $user->id)
                ->whereDate('date', today())
                ->first();
        }

        if ($user->can('targets.view_own', 'targets.view_all', 'targets.manage')) {
            $data['myTargets'] = Target::query()
                ->where('user_id', $user->id)
                ->where('period_end', '>=', now()->startOfMonth())
                ->orderByDesc('period_start')
                ->limit(3)
                ->get();
        }

        if ($user->can('leaves.approve', 'leaves.view_all')) {
            $data['pendingLeaves'] = LeaveRequest::query()
                ->with('user:id,name,avatar')
                ->where('status', 'pending')
                ->orderBy('start_date')
                ->limit(5)
                ->get();
        }

        if ($user->can('invoices.view')) {
            $data['outstandingInvoices'] = Invoice::query()
                ->whereNotIn('status', ['paid', 'Paid', 'cancelled'])
                ->orderBy('due_date')
                ->limit(5)
                ->get();
        }

        if ($user->can('products.view')) {
            $data['lowStockProducts'] = Product::query()
                ->where('type', 'product')
                ->orderBy('stock')
                ->limit(5)
                ->get();
        }

        return view('pages.dashboard', $data);
    }

    public function sales(Request $request): View
    {
        $user = $request->user();
        $analytics = new Analytics($user);

        return view('pages.dashboard-sales', [
            'topProducts' => Product::query()->orderByDesc('rating')->limit(5)->get(),
            'recentOrders' => Sale::query()
                ->with(['salesperson:id,name,avatar', 'items'])
                ->forUser($user)
                ->orderByDesc('sale_date')
                ->limit(8)
                ->get(),
            'kpis' => $analytics->kpis(),
            'charts' => $analytics->salesChart('weekly'),
            'monthly' => $analytics->salesTrends()['monthly'],
            'breakdowns' => $analytics->breakdowns(),
        ]);
    }

    public function ecommerce(): View
    {
        return view('pages.index', [
            'totalSales' => '$'.number_format((float) Order::query()->sum('total'), 2),
            'totalOrders' => number_format(Order::query()->count()),
            'totalCustomers' => number_format(Customer::query()->count()),
            'totalProducts' => number_format(Product::query()->count()),
            'totalContacts' => number_format(Contact::query()->count()),
            'openInvoices' => number_format(Invoice::query()->whereIn('status', ['Pending', 'pending'])->count()),
            'pendingTasks' => number_format(Todo::query()->where('status', 'Waiting')->count()),
            'completedTasks' => number_format(Todo::query()->where('status', 'Completed')->count()),
            'revenue' => '$'.number_format((float) Invoice::query()->sum('amount'), 2),
            'popularProducts' => Product::query()->where('is_featured', true)->orderBy('id')->limit(5)->get(),
            'loyalCustomers' => Customer::query()->orderByDesc('rating')->limit(6)->get(),
        ]);
    }

    public function horizontal(Request $request): View
    {
        return $this->index($request);
    }
}
