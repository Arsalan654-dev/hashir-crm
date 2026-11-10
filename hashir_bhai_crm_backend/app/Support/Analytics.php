<?php

namespace App\Support;

use App\Models\AttendanceRecord;
use App\Models\BotConversation;
use App\Models\Compensation;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\WhatsappContact;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Analytics
{
    public function __construct(private ?User $user = null, private bool $companyWide = false)
    {
        $this->user ??= auth()->user();
        $this->companyWide = $companyWide
            || $this->user === null
            || $this->user->isSuperAdmin()
            || $this->user->can('analytics.view_all');
    }

    public function scopeSales(): Builder
    {
        $query = Sale::query()->completed();

        if (! $this->companyWide && $this->user !== null) {
            $query->where('salesperson_id', $this->user->id);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function kpis(): array
    {
        $sales = $this->scopeSales();

        $thisMonth = (clone $sales)->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()]);
        $lastMonth = (clone $sales)->whereBetween('sale_date', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()]);

        $thisMonthTotal = (float) $thisMonth->sum('total');
        $lastMonthTotal = (float) $lastMonth->sum('total');

        $growth = $lastMonthTotal > 0
            ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : ($thisMonthTotal > 0 ? 100.0 : 0.0);

        $leadsQuery = Lead::query()->when(! $this->companyWide && $this->user, fn ($q) => $q->where('assigned_to', $this->user->id));
        $totalLeads = (clone $leadsQuery)->count();
        $wonLeads = (clone $leadsQuery)->where('status', 'won')->count();
        $lostLeads = (clone $leadsQuery)->where('status', 'lost')->count();

        return [
            'sales_total' => (float) (clone $sales)->sum('total'),
            'sales_this_month' => $thisMonthTotal,
            'sales_last_month' => $lastMonthTotal,
            'sales_today' => (float) (clone $sales)->whereDate('sale_date', today())->sum('total'),
            'sales_this_week' => (float) (clone $sales)->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total'),
            'sales_growth' => $growth,
            'orders_total' => (clone $sales)->count(),
            'orders_this_month' => $thisMonth->count(),
            'pending_sales' => Sale::query()->where('status', 'pending')
                ->when(! $this->companyWide && $this->user, fn ($q) => $q->where('salesperson_id', $this->user->id))
                ->count(),
            'confirmed_sales' => Sale::query()->where('status', 'confirmed')
                ->when(! $this->companyWide && $this->user, fn ($q) => $q->where('salesperson_id', $this->user->id))
                ->count(),
            'avg_sale_value' => (float) round((clone $sales)->avg('total') ?? 0, 2),
            'profit_total' => (float) (clone $sales)->get()->sum(fn (Sale $sale) => $sale->profit()),
            'profit_this_month' => (float) $thisMonth->get()->sum(fn (Sale $sale) => $sale->profit()),
            'leads_total' => $totalLeads,
            'leads_new' => (clone $leadsQuery)->where('status', 'new')->count(),
            'leads_open' => (clone $leadsQuery)->open()->count(),
            'leads_won' => $wonLeads,
            'leads_lost' => $lostLeads,
            'leads_today' => (clone $leadsQuery)->whereDate('created_at', today())->count(),
            'conversion_rate' => $totalLeads > 0 ? round($wonLeads / $totalLeads * 100, 1) : 0.0,
            'customers_total' => Customer::query()->count(),
            'customers_new_month' => Customer::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'products_total' => Product::query()->count(),
            'products_active' => Product::query()->where('is_active', true)->count(),
            'employees_total' => User::query()->whereNotNull('role_id')->count(),
            'employees_active' => User::query()->whereNotNull('role_id')->where('is_active', true)->count(),
            'payroll_this_month' => (float) Payroll::query()->where('period_year', now()->year)->where('period_month', now()->month)->sum('net_salary'),
            'compensation_this_month' => (float) Compensation::query()
                ->where('period_month', now()->format('Y-m'))->whereIn('type', ['bonus', 'incentive'])->sum('amount'),
            'invoices_total' => (float) Invoice::query()->sum('amount'),
            'invoices_paid' => (float) Invoice::query()->sum('paid_amount'),
            'invoices_outstanding' => (float) Invoice::query()->sum(DB::raw('amount - paid_amount')),
            'invoices_overdue' => Invoice::query()->whereNotIn('status', ['paid', 'Paid', 'cancelled'])->whereDate('due_date', '<', today())->count(),
            'whatsapp_contacts' => WhatsappContact::query()->count(),
            'whatsapp_today' => WhatsappContact::query()->whereDate('last_message_at', today())->count(),
            'bot_conversations' => BotConversation::query()->count(),
            'bot_converted' => BotConversation::query()->whereNotNull('sale_id')->count(),
            'attendance_present_today' => AttendanceRecord::query()->whereDate('date', today())->whereIn('status', ['present', 'late', 'remote'])->count(),
            'attendance_absent_today' => AttendanceRecord::query()->whereDate('date', today())->where('status', 'absent')->count(),
        ];
    }

    /**
     * @return array{daily: array<string, mixed>, weekly: array<string, mixed>, monthly: array<string, mixed>}
     */
    public function salesTrends(): array
    {
        $daily = $this->scopeSales()
            ->whereBetween('sale_date', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->selectRaw('date(sale_date) as bucket, sum(total) as total, count(*) as orders')
            ->groupBy('bucket')->orderBy('bucket')->get();

        $weekly = $this->scopeSales()
            ->whereBetween('sale_date', [now()->subWeeks(11)->startOfWeek(), now()->endOfWeek()])
            ->selectRaw($this->weekBucket('sale_date').' as bucket, sum(total) as total, count(*) as orders')
            ->groupBy('bucket')->orderBy('bucket')->get();

        $monthly = $this->scopeSales()
            ->whereBetween('sale_date', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw($this->monthBucket('sale_date').' as bucket, sum(total) as total, count(*) as orders')
            ->groupBy('bucket')->orderBy('bucket')->get();

        return [
            'daily' => $this->fillDaily($daily, 30),
            'weekly' => $this->fillWeekly($weekly, 12),
            'monthly' => $this->fillMonthly($monthly, 12),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function salesChart(string $range = 'monthly'): array
    {
        $trends = $this->salesTrends();
        $series = $trends[$range] ?? $trends['monthly'];

        return [
            'categories' => $series['labels'],
            'sales' => $series['totals'],
            'orders' => $series['orders'],
            'range' => $range,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function breakdowns(): array
    {
        $leadsByStatus = Lead::query()
            ->when(! $this->companyWide && $this->user, fn ($q) => $q->where('assigned_to', $this->user->id))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $leadsBySource = Lead::query()
            ->when(! $this->companyWide && $this->user, fn ($q) => $q->where('assigned_to', $this->user->id))
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')->orderByDesc('total')->pluck('total', 'source');

        $topSalespeople = $this->companyWide
            ? Sale::query()->completed()
                ->selectRaw('salesperson_id, sum(total) as total, count(*) as orders')
                ->with('salesperson:id,name,avatar')
                ->groupBy('salesperson_id')->orderByDesc('total')->limit(8)->get()
            : collect();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sales.status', ['confirmed', 'done'])
            ->when(! $this->companyWide && $this->user, fn ($q) => $q->where('sales.salesperson_id', $this->user->id))
            ->selectRaw('sale_items.name, sum(sale_items.total) as total, sum(sale_items.quantity) as qty')
            ->groupBy('sale_items.name')->orderByDesc('total')->limit(6)->get();

        $attendanceToday = AttendanceRecord::query()->whereDate('date', today())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $payrollByMonth = Payroll::query()
            ->selectRaw('period_label, sum(net_salary) as total, sum(commission) as commission, sum(bonus) as bonus, sum(incentive) as incentive')
            ->groupBy('period_label')->orderBy('period_label')->limit(12)->get();

        return [
            'leads_by_status' => $leadsByStatus,
            'leads_by_source' => $leadsBySource,
            'top_salespeople' => $topSalespeople,
            'top_products' => $topProducts,
            'attendance_today' => $attendanceToday,
            'payroll_by_month' => $payrollByMonth,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'kpis' => $this->kpis(),
            'charts' => $this->salesChart('monthly'),
            'trends' => $this->salesTrends(),
            'breakdowns' => $this->breakdowns(),
            'company_wide' => $this->companyWide,
        ];
    }

    /**
     * @return array{labels: array<int, string>, totals: array<int, float>, orders: array<int, int>}
     */
    private function fillDaily(Collection $rows, int $days): array
    {
        $labels = [];
        $totals = [];
        $orders = [];
        $keyed = $rows->keyBy('bucket');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $row = $keyed->get($date->toDateString());
            $labels[] = $date->format('d M');
            $totals[] = round((float) ($row->total ?? 0), 2);
            $orders[] = (int) ($row->orders ?? 0);
        }

        return compact('labels', 'totals', 'orders');
    }

    /**
     * @return array{labels: array<int, string>, totals: array<int, float>, orders: array<int, int>}
     */
    private function fillWeekly(Collection $rows, int $weeks): array
    {
        $labels = [];
        $totals = [];
        $orders = [];
        $keyed = $rows->keyBy('bucket');

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $row = $keyed->get($start->format('Y-W'));
            $labels[] = 'W'.$start->format('W Y');
            $totals[] = round((float) ($row->total ?? 0), 2);
            $orders[] = (int) ($row->orders ?? 0);
        }

        return compact('labels', 'totals', 'orders');
    }

    /**
     * @return array{labels: array<int, string>, totals: array<int, float>, orders: array<int, int>}
     */
    private function fillMonthly(Collection $rows, int $months): array
    {
        $labels = [];
        $totals = [];
        $orders = [];
        $keyed = $rows->keyBy('bucket');

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonthsNoOverflow($i);
            $row = $keyed->get($date->format('Y-m'));
            $labels[] = $date->format('M Y');
            $totals[] = round((float) ($row->total ?? 0), 2);
            $orders[] = (int) ($row->orders ?? 0);
        }

        return compact('labels', 'totals', 'orders');
    }

    /**
     * @return array<int, array{name: string, total: float}>
     */
    public function monthlySeries(string $table, string $column, string $dateColumn, int $months = 12): array
    {
        $rows = DB::table($table)
            ->whereBetween($dateColumn, [now()->subMonthsNoOverflow($months - 1)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw($this->monthBucket($dateColumn).' as bucket, sum('.$column.') as total')
            ->groupBy('bucket')->pluck('total', 'bucket');

        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonthsNoOverflow($i);
            $series[] = [
                'name' => $date->format('M Y'),
                'total' => round((float) ($rows[$date->format('Y-m')] ?? 0), 2),
            ];
        }

        return $series;
    }

    public function periodLabel(Carbon $date): string
    {
        return $date->format('F Y');
    }

    private function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    private function monthBucket(string $column): string
    {
        return $this->isSqlite()
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    private function weekBucket(string $column): string
    {
        return $this->isSqlite()
            ? "strftime('%Y-%W', {$column})"
            : "DATE_FORMAT({$column}, '%x-%v')";
    }
}
