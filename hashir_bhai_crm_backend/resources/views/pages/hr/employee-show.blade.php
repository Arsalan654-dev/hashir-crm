@extends('layouts.app')

@php
    $saleColors = ['pending' => 'warning', 'confirmed' => 'primary', 'done' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
    $attendanceColors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half-day' => 'secondary', 'leave' => 'info', 'holiday' => 'info', 'remote' => 'primary'];
    $leaveColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
    $payrollColors = ['draft' => 'secondary', 'approved' => 'primary', 'paid' => 'success'];
@endphp

@section('title', $employee->name)
@section('page-title', 'Employee — '.$employee->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/employees') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('page-actions')
    @can('employees.update')
        <a href="{{ url('/employees/'.$employee->id.'/edit') }}" class="btn btn-primary btn-sm"><i class="bx bx-edit me-1"></i> Edit Employee</a>
    @endcan
    @can('employees.delete')
        @unless ($employee->is_super_admin || $employee->is(auth()->user()))
            <form action="{{ url('/employees/'.$employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this employee?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash me-1"></i> Delete</button>
            </form>
        @endunless
    @endcan
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-body text-center">
                    <img src="{{ $employee->avatarUrl() }}" class="rounded-circle avatar-xl mb-3" alt="">
                    <h5 class="mb-1">{{ $employee->name }}</h5>
                    <p class="text-muted mb-2">{{ $employee->designationRecord?->title ?? 'Team Member' }}</p>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-{{ $employee->assignedRole?->color ?? 'primary' }}-subtle text-{{ $employee->assignedRole?->color ?? 'primary' }}">{{ $employee->assignedRole?->name ?? 'No role' }}</span>
                        @if ($employee->is_active)
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                    </div>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2"><i class="bx bx-badge text-muted me-2"></i>{{ $employee->employee_code ?? 'No employee code' }}</p>
                        <p class="mb-2"><i class="bx bx-envelope text-muted me-2"></i>{{ $employee->email }}</p>
                        <p class="mb-2"><i class="bx bx-phone text-muted me-2"></i>{{ $employee->phone ?? 'Not provided' }}</p>
                        <p class="mb-2"><i class="bx bxl-whatsapp text-muted me-2"></i>{{ $employee->whatsapp_number ?? 'Not provided' }}</p>
                        <p class="mb-2"><i class="bx bx-buildings text-muted me-2"></i>{{ $employee->department?->name ?? 'No department' }}</p>
                        <p class="mb-2"><i class="bx bx-briefcase text-muted me-2"></i>{{ ucfirst($employee->employment_type ?? 'full-time') }}</p>
                        <p class="mb-2"><i class="bx bx-calendar text-muted me-2"></i>Joined {{ $employee->joining_date?->format('d M Y') ?? 'N/A' }}</p>
                        <p class="mb-0"><i class="bx bx-user-check text-muted me-2"></i>Reports to {{ $employee->manager?->name ?? 'Not assigned' }}</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Salary &amp; Bank</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Basic Salary</span>
                        <strong>{{ \App\Support\Money::format($employee->basic_salary) }} {{ $employee->salary_currency }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Commission Rate</span><strong>{{ number_format((float) $employee->commission_rate, 2) }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Hourly Rate</span><strong>{{ $employee->hourly_rate ? \App\Support\Money::format($employee->hourly_rate) : '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Bank</span><strong>{{ $employee->bank_name ?? '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Account No.</span><strong>{{ $employee->bank_account_number ?? '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">IBAN</span><strong>{{ $employee->bank_iban ?? '—' }}</strong>
                    </div>
                </div>
            </div>

            @if ($employee->subordinates->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Direct Reports</h5></div>
                    <div class="card-body">
                        @foreach ($employee->subordinates as $subordinate)
                            <div class="d-flex align-items-center {{ $loop->last ? '' : 'mb-2' }}">
                                <i class="bx bx-user text-muted me-2"></i>
                                <a href="{{ url('/employees/'.$subordinate->id) }}">{{ $subordinate->name }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-8">
            <div class="row g-3 mb-3">
                <div class="col-md-4 col-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-0">{{ \App\Support\Money::compact($stats['sales_total']) }}</h4>
                            <span class="text-muted font-size-13">Sales ({{ number_format($stats['sales_count']) }})</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-0 text-success">{{ \App\Support\Money::compact($stats['commission']) }}</h4>
                            <span class="text-muted font-size-13">Commission Earned</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-0 text-info">{{ number_format($stats['leads']) }}</h4>
                            <span class="text-muted font-size-13">Assigned Leads</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-0 text-warning">{{ \App\Support\Money::percent($stats['attendance_rate']) }}</h4>
                            <span class="text-muted font-size-13">Attendance Rate</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-0 text-primary">{{ \App\Support\Money::compact($stats['payroll_total']) }}</h4>
                            <span class="text-muted font-size-13">Payroll Paid</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#emp-sales" role="tab">Sales</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#emp-attendance" role="tab">Attendance</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#emp-leaves" role="tab">Leaves</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#emp-payslips" role="tab">Payslips</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="emp-sales" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sale</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Commission</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentSales as $sale)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('/sales/'.$sale->id) }}" class="fw-medium">{{ $sale->sale_no }}</a>
                                                    <span class="text-muted font-size-12 d-block">{{ $sale->items->count() }} items</span>
                                                </td>
                                                <td>{{ $sale->customer_name }}</td>
                                                <td>{{ $sale->sale_date?->format('d M Y') }}</td>
                                                <td class="text-end">{{ \App\Support\Money::format($sale->total) }}</td>
                                                <td class="text-end">{{ \App\Support\Money::format($sale->commission_amount) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $saleColors[$sale->status] ?? 'secondary' }}-subtle text-{{ $saleColors[$sale->status] ?? 'secondary' }}">{{ ucfirst($sale->status) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No sales recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="emp-attendance" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th class="text-end">Worked Hours</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentAttendance as $record)
                                            <tr>
                                                <td>{{ $record->date?->format('d M Y') }}</td>
                                                <td>{{ $record->check_in ?? '—' }}</td>
                                                <td>{{ $record->check_out ?? '—' }}</td>
                                                <td class="text-end">{{ number_format((float) $record->worked_hours, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $attendanceColors[$record->status] ?? 'secondary' }}-subtle text-{{ $attendanceColors[$record->status] ?? 'secondary' }}">{{ ucfirst($record->status) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No attendance records.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="emp-leaves" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th class="text-end">Days</th>
                                            <th>Paid</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($leaveRequests as $leave)
                                            <tr>
                                                <td>{{ ucfirst($leave->type) }}</td>
                                                <td>{{ $leave->start_date?->format('d M Y') }}</td>
                                                <td>{{ $leave->end_date?->format('d M Y') }}</td>
                                                <td class="text-end">{{ number_format((float) $leave->days, 1) }}</td>
                                                <td>
                                                    @if ($leave->is_paid)
                                                        <span class="badge bg-success-subtle text-success">Paid</span>
                                                    @else
                                                        <span class="badge bg-light text-dark">Unpaid</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $leaveColors[$leave->status] ?? 'secondary' }}-subtle text-{{ $leaveColors[$leave->status] ?? 'secondary' }}">{{ ucfirst($leave->status) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No leave requests.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="emp-payslips" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Period</th>
                                            <th class="text-end">Basic</th>
                                            <th class="text-end">Net Salary</th>
                                            <th>Status</th>
                                            <th>Paid On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payrolls as $payroll)
                                            <tr>
                                                <td>{{ $payroll->period_label }}</td>
                                                <td class="text-end">{{ \App\Support\Money::format($payroll->basic_salary) }}</td>
                                                <td class="text-end fw-medium">{{ \App\Support\Money::format($payroll->net_salary) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $payrollColors[$payroll->status] ?? 'secondary' }}-subtle text-{{ $payrollColors[$payroll->status] ?? 'secondary' }}">{{ ucfirst($payroll->status) }}</span>
                                                </td>
                                                <td>{{ $payroll->paid_on?->format('d M Y') ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No payslips generated.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
