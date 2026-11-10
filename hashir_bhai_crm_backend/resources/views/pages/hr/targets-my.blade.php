@extends('layouts.app')

@section('title', 'My Targets')
@section('page-title', 'My Targets')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/targets/my') }}">Targets</a></li>
    <li class="breadcrumb-item active">Mine</li>
@endsection

@php
    $labels = $targets->map(fn ($target) => $target->title ?: ucfirst($target->period_type).' · '.$target->period_start->format('d M'))->all();
    $targetValues = $targets->map(fn ($target) => (float) $target->target_amount)->all();
    $achievedValues = $targets->map(fn ($target) => $target->achievedAmount())->all();
    $activeTarget = (float) $targets
        ->filter(fn ($target) => $target->period_start->lte(now()) && $target->period_end->gte(now()))
        ->sum('target_amount');
    $overallProgress = $activeTarget > 0 ? min(100, round($achieved / $activeTarget * 100, 1)) : 0;
@endphp

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-4 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-primary-subtle text-primary"><i class="bx bx-dollar-circle"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">Achieved This Month</p>
                            <h4 class="mb-0">{{ \App\Support\Money::format($achieved) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-warning-subtle text-warning"><i class="bx bx-target-lock"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">Active Target</p>
                            <h4 class="mb-0">{{ \App\Support\Money::format($activeTarget) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="cds-kpi-icon bg-success-subtle text-success"><i class="bx bx-trending-up"></i></div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 font-size-13">Overall Progress</p>
                            <h4 class="mb-0">{{ \App\Support\Money::percent($overallProgress) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-4">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Overall Progress</h5>
                    <div id="cds-target-radial" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Target vs Achieved</h5>
                    <div id="cds-target-bars" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @forelse ($targets as $target)
            @php($targetAchieved = $target->achievedAmount())
            @php($progress = $target->progressPercent())
            <div class="col-xl-4 col-md-6">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-1">{{ $target->title ?: ucfirst($target->period_type).' Target' }}</h5>
                                <span class="text-muted font-size-13">{{ $target->period_start->format('d M Y') }} → {{ $target->period_end->format('d M Y') }}</span>
                            </div>
                            <span class="badge bg-{{ $progress >= 100 ? 'success' : 'primary' }}-subtle text-{{ $progress >= 100 ? 'success' : 'primary' }}">{{ \App\Support\Money::percent($progress) }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : 'primary' }}" style="width: {{ min(100, $progress) }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between font-size-13">
                            <span class="text-muted">Achieved</span>
                            <span class="fw-semibold">{{ \App\Support\Money::format($targetAchieved) }}</span>
                        </div>
                        <div class="d-flex justify-content-between font-size-13">
                            <span class="text-muted">Target</span>
                            <span class="fw-semibold">{{ \App\Support\Money::format($target->target_amount) }}</span>
                        </div>
                        @if ((float) $target->bonus_amount > 0)
                            <div class="d-flex justify-content-between font-size-13">
                                <span class="text-muted">Bonus on Completion</span>
                                <span class="text-success fw-semibold">{{ \App\Support\Money::format($target->bonus_amount) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card mb-0"><div class="card-body text-center text-muted py-4">You have no targets assigned yet.</div></div>
            </div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Target History</h5>
            <span class="text-muted font-size-13">{{ $targets->count() }} targets</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Period</th>
                            <th class="text-end">Target Amount</th>
                            <th class="text-end">Achieved</th>
                            <th style="min-width: 160px;">Progress</th>
                            <th class="text-end">Orders Target</th>
                            <th class="text-end">Bonus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($targets as $target)
                            <tr>
                                <td><span class="fw-medium">{{ $target->title ?: ucfirst($target->period_type).' Target' }}</span></td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ ucfirst($target->period_type) }}</span>
                                    <div class="text-muted font-size-12">{{ $target->period_start->format('d M Y') }} → {{ $target->period_end->format('d M Y') }}</div>
                                </td>
                                <td class="text-end fw-semibold">{{ \App\Support\Money::format($target->target_amount) }}</td>
                                <td class="text-end">{{ \App\Support\Money::format($target->achievedAmount()) }}</td>
                                <td>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $target->progressPercent() >= 100 ? 'success' : 'primary' }}" style="width: {{ min(100, $target->progressPercent()) }}%"></div>
                                    </div>
                                    <span class="text-muted font-size-12">{{ \App\Support\Money::percent($target->progressPercent()) }}</span>
                                </td>
                                <td class="text-end">{{ $target->target_orders ? number_format((float) $target->target_orders) : '—' }}</td>
                                <td class="text-end">{{ (float) $target->bonus_amount > 0 ? \App\Support\Money::format($target->bonus_amount) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No targets assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var labels = @json($labels);
            var targetSeries = @json($targetValues);
            var achievedSeries = @json($achievedValues);
            var overall = @json($overallProgress);

            if (document.querySelector('#cds-target-radial')) {
                new ApexCharts(document.querySelector('#cds-target-radial'), {
                    chart: { type: 'radialBar', height: 320 },
                    series: [overall],
                    labels: ['Achieved'],
                    colors: ['#1e7df0'],
                    plotOptions: {
                        radialBar: {
                            hollow: { size: '55%' },
                            dataLabels: {
                                name: { fontSize: '15px' },
                                value: { fontSize: '26px', formatter: function (v) { return v + '%'; } },
                            },
                        },
                    },
                }).render();
            }

            if (document.querySelector('#cds-target-bars') && labels.length) {
                new ApexCharts(document.querySelector('#cds-target-bars'), {
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    series: [
                        { name: 'Target', data: targetSeries },
                        { name: 'Achieved', data: achievedSeries },
                    ],
                    xaxis: { categories: labels, labels: { rotate: -35, style: { fontSize: '11px' } } },
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                    colors: ['#f1b44c', '#34c38f'],
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom' },
                    yaxis: { labels: { formatter: function (v) { return '{{ \App\Support\Branding::currencySymbol() }}' + Math.round(v); } } },
                }).render();
            }
        })();
    </script>
@endpush
