<?php

namespace App\Http\Controllers;

use App\Support\Analytics;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $analytics = new Analytics($request->user());

        return view('pages.analytics', [
            'kpis' => $analytics->kpis(),
            'trends' => $analytics->salesTrends(),
            'breakdowns' => $analytics->breakdowns(),
            'companyWide' => $request->user()->isSuperAdmin() || $request->user()->can('analytics.view_all'),
            'payrollSeries' => $analytics->monthlySeries('payrolls', 'net_salary', 'created_at'),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $analytics = new Analytics($request->user());
        $range = (string) $request->query('range', 'monthly');

        if (! in_array($range, ['daily', 'weekly', 'monthly'], true)) {
            $range = 'monthly';
        }

        return response()->json([
            'chart' => $analytics->salesChart($range),
            'kpis' => $analytics->kpis(),
        ]);
    }
}
