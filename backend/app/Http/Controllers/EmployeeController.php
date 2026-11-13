<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = User::query()
            ->whereNotNull('role_id')
            ->with(['assignedRole:id,name,color', 'department:id,name', 'designationRecord:id,title'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('employee_code', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->filled('department'), fn ($query) => $query->where('department_id', $request->integer('department')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('pages.hr.employees', [
            'employees' => $employees,
            'departments' => Department::query()->orderBy('name')->get(),
            'stats' => [
                'total' => User::query()->whereNotNull('role_id')->count(),
                'active' => User::query()->whereNotNull('role_id')->where('is_active', true)->count(),
                'salaries' => (float) User::query()->whereNotNull('role_id')->sum('basic_salary'),
                'onLeave' => LeaveRequest::query()->where('status', 'approved')
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today())->count(),
                'presentToday' => AttendanceRecord::query()->whereDate('date', today())->whereIn('status', ['present', 'late', 'remote'])->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.hr.employee-form', [
            'employee' => new User(['employment_type' => 'full-time', 'joining_date' => now(), 'is_active' => true]),
            'roles' => Role::query()->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'designations' => Designation::query()->with('department')->orderBy('title')->get(),
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEmployee($request);

        $employee = User::query()->create($data + [
            'role' => 'user',
            'password' => $data['password'],
            'email_verified_at' => now(),
            'avatar' => 'assets/images/users/avatar-'.random_int(1, 8).'.jpg',
        ]);

        Activity::log('created', 'Employee created: '.$employee->name, $employee, [], 'employees');

        return redirect()->route('employees.index')->with('status', 'Employee added successfully.');
    }

    public function show(User $employee): View
    {
        abort_unless($employee->role_id !== null, 404);

        $employee->load(['assignedRole', 'department', 'designationRecord', 'manager', 'subordinates:id,name,reports_to']);

        $sales = Sale::query()->where('salesperson_id', $employee->id);

        return view('pages.hr.employee-show', [
            'employee' => $employee,
            'stats' => [
                'sales_total' => (float) (clone $sales)->completed()->sum('total'),
                'sales_count' => (clone $sales)->completed()->count(),
                'commission' => (float) (clone $sales)->completed()->sum('commission_amount'),
                'leads' => $employee->leads()->count(),
                'attendance_rate' => $this->attendanceRate($employee),
                'payroll_total' => (float) Payroll::query()->where('user_id', $employee->id)->sum('net_salary'),
            ],
            'recentSales' => (clone $sales)->with('items')->orderByDesc('sale_date')->limit(6)->get(),
            'recentAttendance' => $employee->attendanceRecords()->orderByDesc('date')->limit(10)->get(),
            'leaveRequests' => $employee->leaveRequests()->orderByDesc('created_at')->limit(6)->get(),
            'payrolls' => Payroll::query()->where('user_id', $employee->id)->orderByDesc('period_year')->orderByDesc('period_month')->limit(6)->get(),
        ]);
    }

    public function edit(User $employee): View
    {
        abort_unless($employee->role_id !== null, 404);

        return view('pages.hr.employee-form', [
            'employee' => $employee,
            'roles' => Role::query()->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'designations' => Designation::query()->with('department')->orderBy('title')->get(),
            'managers' => User::query()->where('is_active', true)->whereKeyNot($employee->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->role_id !== null, 404);

        $data = $this->validateEmployee($request, $employee);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $employee->fill($data)->save();

        Activity::log('updated', 'Employee updated: '.$employee->name, $employee, [], 'employees');

        return redirect()->route('employees.show', $employee)->with('status', 'Employee updated.');
    }

    public function destroy(Request $request, User $employee): RedirectResponse
    {
        if ($employee->is_super_admin) {
            return back()->withErrors(['employee' => 'The super admin account cannot be deleted.']);
        }

        if ($employee->is($request->user())) {
            return back()->withErrors(['employee' => 'You cannot delete your own employee record.']);
        }

        Activity::log('deleted', 'Employee deleted: '.$employee->name, null, ['user_id' => $employee->id], 'employees');
        $employee->delete();

        return redirect()->route('employees.index')->with('status', 'Employee removed.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee Code', 'Name', 'Email', 'Phone', 'Role', 'Department', 'Designation',
                'Employment Type', 'Joining Date', 'Basic Salary', 'Commission Rate', 'Status',
            ]);

            User::query()
                ->whereNotNull('role_id')
                ->with(['assignedRole:id,name', 'department:id,name', 'designationRecord:id,title'])
                ->chunk(500, function ($employees) use ($handle) {
                    foreach ($employees as $employee) {
                        fputcsv($handle, [
                            $employee->employee_code,
                            $employee->name,
                            $employee->email,
                            $employee->phone,
                            $employee->assignedRole?->name,
                            $employee->department?->name,
                            $employee->designation ?? $employee->designationRecord?->title,
                            $employee->employment_type,
                            $employee->joining_date?->toDateString(),
                            number_format((float) $employee->basic_salary, 2, '.', ''),
                            number_format((float) $employee->commission_rate, 2, '.', ''),
                            $employee->is_active ? 'active' : 'inactive',
                        ]);
                    }
                });

            fclose($handle);
        }, 'employees-export-'.now()->format('Y-m-d').'.csv');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEmployee(Request $request, ?User $employee = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($employee?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee?->id)],
            'password' => [$employee === null ? 'required' : 'nullable', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'reports_to' => ['nullable', 'exists:users,id'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_code')->ignore($employee?->id)],
            'employment_type' => ['required', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'marital_status' => ['nullable', 'string', 'max:20'],
            'national_id' => ['nullable', 'string', 'max:60'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_currency' => ['nullable', 'string', 'max:10'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:60'],
            'bank_iban' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function attendanceRate(User $employee): float
    {
        $total = $employee->attendanceRecords()->count();

        if ($total === 0) {
            return 0.0;
        }

        $present = $employee->attendanceRecords()->whereIn('status', ['present', 'late', 'remote', 'half-day'])->count();

        return round($present / $total * 100, 1);
    }
}
