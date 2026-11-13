<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Compensation;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\Analytics;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $analytics = new Analytics($request->user());

        return view('pages.reports', [
            'from' => $from,
            'to' => $to,
            'kpis' => $analytics->kpis(),
            'breakdowns' => $analytics->breakdowns(),
            'monthly' => $analytics->salesTrends()['monthly'],
            'salesByUser' => Sale::query()->completed()
                ->whereBetween('sale_date', [$from, $to])
                ->selectRaw('salesperson_id, count(*) as orders, sum(total) as total, sum(commission_amount) as commission, sum(cost) as cost')
                ->with('salesperson:id,name')
                ->groupBy('salesperson_id')->orderByDesc('total')->get(),
            'leadsByUser' => Lead::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw("assigned_to, count(*) as total, sum(case when status = 'won' then 1 else 0 end) as won")
                ->with('assignedUser:id,name')
                ->groupBy('assigned_to')->orderByDesc('total')->get(),
            'payrollSummary' => Payroll::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('period_label, sum(net_salary) as net, sum(commission) as commission, sum(bonus + incentive) as bonus')
                ->groupBy('period_label')->orderBy('period_label')->get(),
            'compensationSummary' => Compensation::query()
                ->whereBetween('date', [$from, $to])
                ->selectRaw('type, sum(amount) as total')->groupBy('type')->get(),
            'outstanding' => Invoice::query()
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->orderByDesc('amount')->limit(15)->get(),
            'attendanceSummary' => AttendanceRecord::query()
                ->whereBetween('date', [$from, $to])
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'topProducts' => Product::query()->orderByDesc('rating')->limit(8)->get(),
            'employees' => User::query()->whereNotNull('role_id')->count(),
            'customers' => Customer::query()->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:sales,leads,payroll,attendance,customers'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($data['from']) ? Carbon::parse($data['from']) : now()->startOfMonth();
        $to = isset($data['to']) ? Carbon::parse($data['to']) : now()->endOfMonth();

        return response()->streamDownload(function () use ($data, $from, $to) {
            $handle = fopen('php://output', 'w');

            match ($data['type']) {
                'sales' => $this->exportSales($handle, $from, $to),
                'leads' => $this->exportLeads($handle, $from, $to),
                'payroll' => $this->exportPayroll($handle, $from, $to),
                'attendance' => $this->exportAttendance($handle, $from, $to),
                'customers' => $this->exportCustomers($handle),
            };

            fclose($handle);
        }, $data['type'].'-report-'.now()->format('Y-m-d').'.csv');
    }

    private function exportSales($handle, Carbon $from, Carbon $to): void
    {
        fputcsv($handle, ['Sale No', 'Date', 'Salesperson', 'Customer', 'Total', 'Cost', 'Profit', 'Commission', 'Status']);

        Sale::query()->with('salesperson:id,name')->completed()
            ->whereBetween('sale_date', [$from, $to])
            ->chunk(500, function ($sales) use ($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->sale_no, $sale->sale_date->toDateString(), $sale->salesperson?->name,
                        $sale->customer_name, $sale->total, $sale->cost, $sale->profit(), $sale->commission_amount, $sale->status,
                    ]);
                }
            });
    }

    private function exportLeads($handle, Carbon $from, Carbon $to): void
    {
        fputcsv($handle, ['Lead No', 'Name', 'Phone', 'Source', 'Status', 'Value', 'Assigned To', 'Created']);

        Lead::query()->with('assignedUser:id,name')
            ->whereBetween('created_at', [$from, $to])
            ->chunk(500, function ($leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->lead_no, $lead->name, $lead->phone, $lead->source, $lead->status,
                        $lead->estimated_value, $lead->assignedUser?->name, $lead->created_at->toDateString(),
                    ]);
                }
            });
    }

    private function exportPayroll($handle, Carbon $from, Carbon $to): void
    {
        fputcsv($handle, ['Employee', 'Period', 'Basic', 'Commission', 'Bonus', 'Incentive', 'Deductions', 'Net', 'Status']);

        Payroll::query()->with('user:id,name')
            ->whereBetween('created_at', [$from, $to])
            ->chunk(500, function ($payrolls) use ($handle) {
                foreach ($payrolls as $payroll) {
                    fputcsv($handle, [
                        $payroll->user?->name, $payroll->period_label, $payroll->basic_salary, $payroll->commission,
                        $payroll->bonus, $payroll->incentive, $payroll->deductions + $payroll->absent_deduction,
                        $payroll->net_salary, $payroll->status,
                    ]);
                }
            });
    }

    private function exportAttendance($handle, Carbon $from, Carbon $to): void
    {
        fputcsv($handle, ['Employee', 'Date', 'Status', 'Check In', 'Check Out', 'Hours']);

        AttendanceRecord::query()->with('user:id,name')
            ->whereBetween('date', [$from, $to])
            ->chunk(500, function ($records) use ($handle) {
                foreach ($records as $record) {
                    fputcsv($handle, [
                        $record->user?->name, $record->date->toDateString(), $record->status,
                        $record->check_in, $record->check_out, $record->worked_hours,
                    ]);
                }
            });
    }

    private function exportCustomers($handle): void
    {
        fputcsv($handle, ['Name', 'Company', 'Email', 'Phone', 'City', 'Country', 'Status', 'Created']);

        Customer::query()->chunk(500, function ($customers) use ($handle) {
            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->name, $customer->company, $customer->email, $customer->phone,
                    $customer->city, $customer->country, $customer->status ?? 'active', $customer->created_at->toDateString(),
                ]);
            }
        });
    }
}
