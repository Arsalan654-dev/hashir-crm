<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\CommissionRule;
use App\Models\Compensation;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = env('CRM_DEMO_PASSWORD');

        if (! is_string($demoPassword) || $demoPassword === '') {
            throw new \RuntimeException('Set CRM_DEMO_PASSWORD in the environment before seeding CRM demo users.');
        }

        $roles = Role::query()->pluck('id', 'slug');
        $departments = Department::query()->pluck('id', 'name');
        $designations = Designation::query()->pluck('id', 'title');

        $employees = [
            ['Sarah Mitchell', 'sarah', 'sarah@example.com', 'sales-person', 'Sales', 'Senior Sales Executive', 2400, 5],
            ['Daniel Osei', 'daniel', 'daniel@example.com', 'sales-person', 'Sales', 'Sales Executive', 1800, 4],
            ['Ayesha Khan', 'ayesha', 'ayesha@example.com', 'sales-person', 'Sales', 'Sales Executive', 1600, 4],
            ['Michael Petrou', 'michael', 'michael@example.com', 'sales-manager', 'Sales', 'Sales Manager', 3600, 2],
            ['Elena Georgiou', 'elena', 'elena@example.com', 'hr-manager', 'Human Resources', 'HR Manager', 3200, 0],
            ['Ravi Sharma', 'ravi', 'ravi@example.com', 'employee', 'Development', 'Software Engineer', 2800, 0],
            ['Nadia Hassan', 'nadia', 'nadia@example.com', 'employee', 'Support', 'Support Agent', 1500, 0],
            ['James Carter', 'james', 'james@example.com', 'admin', 'Management', 'Operations Manager', 4000, 0],
        ];

        foreach ($employees as $index => [$name, $username, $email, $role, $department, $designation, $salary, $commission]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => Hash::make($demoPassword),
                    'phone' => '+357 99 '.str_pad((string) (100 + $index), 3, '0').' '.str_pad((string) (200 + $index), 3, '0'),
                    'role' => in_array($role, ['admin', 'super-admin'], true) ? 'admin' : 'user',
                    'role_id' => $roles[$role] ?? null,
                    'department_id' => $departments[$department] ?? null,
                    'designation_id' => $designations[$designation] ?? null,
                    'designation' => $designation,
                    'employee_code' => 'EMP-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'employment_type' => 'full-time',
                    'joining_date' => now()->subMonths(18 - $index)->startOfMonth(),
                    'basic_salary' => $salary,
                    'salary_currency' => 'USD',
                    'commission_rate' => $commission,
                    'whatsapp_number' => '+3579812'.str_pad((string) (10 + $index), 3, '0'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'avatar' => 'assets/images/users/avatar-'.(($index % 8) + 1).'.jpg',
                ],
            );
        }

        $salespeople = User::query()->whereHas('assignedRole', fn ($query) => $query->whereIn('slug', ['sales-person', 'sales-manager']))->get();

        if ($salespeople->isEmpty()) {
            return;
        }

        $this->seedCommissionRules($salespeople);
        $this->seedTargets($salespeople);
        $this->seedAttendance();
        $this->seedLeaves();
        $this->seedSales($salespeople);
        $this->seedCompensations($salespeople);
    }

    private function seedCommissionRules($salespeople): void
    {
        CommissionRule::query()->updateOrCreate(
            ['name' => 'Standard Sales Commission'],
            ['basis' => 'percentage', 'value' => 5, 'applies_to' => 'all', 'is_active' => true],
        );

        if ($salespeople->first()) {
            CommissionRule::query()->updateOrCreate(
                ['name' => 'Senior Executive Bonus Rate'],
                [
                    'basis' => 'percentage',
                    'value' => 7,
                    'applies_to' => 'all',
                    'user_id' => $salespeople->first()->id,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedTargets($salespeople): void
    {
        foreach ($salespeople as $person) {
            foreach ([0, 1] as $monthsAgo) {
                $start = now()->subMonths($monthsAgo)->startOfMonth();
                $end = now()->subMonths($monthsAgo)->endOfMonth();

                Target::query()->updateOrCreate(
                    ['user_id' => $person->id, 'period_start' => $start->toDateString()],
                    [
                        'period_type' => 'monthly',
                        'period_end' => $end->toDateString(),
                        'target_amount' => 20000 + ($monthsAgo * 2000),
                        'target_orders' => 10,
                        'bonus_amount' => 500,
                        'title' => $start->format('F Y').' Target',
                    ],
                );
            }
        }
    }

    private function seedAttendance(): void
    {
        $users = User::query()->whereNotNull('role_id')->get();
        $statuses = ['present', 'present', 'present', 'present', 'late', 'remote', 'present', 'absent'];

        foreach ($users as $user) {
            for ($daysAgo = 0; $daysAgo < 45; $daysAgo++) {
                $date = now()->subDays($daysAgo);

                if ($date->isWeekend()) {
                    continue;
                }

                $status = $statuses[array_rand($statuses)];
                $checkIn = $status === 'absent' ? null : '0'.random_int(8, 9).':'.str_pad((string) random_int(0, 59), 2, '0', STR_PAD_LEFT);
                $checkOut = $status === 'absent' ? null : '1'.random_int(7, 9).':'.str_pad((string) random_int(0, 59), 2, '0', STR_PAD_LEFT);

                AttendanceRecord::query()->updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date->toDateString()],
                    [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'status' => $status,
                        'worked_hours' => $status === 'absent' ? 0 : random_int(6, 9),
                        'overtime_hours' => random_int(0, 2),
                    ],
                );
            }
        }
    }

    private function seedLeaves(): void
    {
        $users = User::query()->whereNotNull('role_id')->inRandomOrder()->take(4)->get();

        foreach ($users as $index => $user) {
            LeaveRequest::query()->create([
                'user_id' => $user->id,
                'type' => $index % 2 === 0 ? 'annual' : 'sick',
                'start_date' => now()->addDays($index + 3),
                'end_date' => now()->addDays($index + 5),
                'days' => 3,
                'reason' => 'Family commitment and personal errands.',
                'status' => $index % 2 === 0 ? 'pending' : 'approved',
                'approved_by' => $index % 2 === 0 ? null : User::query()->where('is_super_admin', true)->value('id'),
                'approved_at' => $index % 2 === 0 ? null : now()->subDay(),
                'is_paid' => true,
            ]);
        }
    }

    private function seedSales($salespeople): void
    {
        $products = Product::query()->get();
        $statuses = ['done', 'done', 'done', 'confirmed', 'pending', 'pending'];
        $sources = ['manual', 'whatsapp', 'whatsapp_bot', 'referral', 'website'];
        $customers = Customer::query()->pluck('name');

        if ($products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 90; $i++) {
            $person = $salespeople->random();
            $daysAgo = random_int(0, 120);
            $saleDate = now()->subDays($daysAgo);
            $status = $daysAgo < 3 ? 'pending' : $statuses[array_rand($statuses)];
            $product = $products->random();
            $quantity = random_int(1, 4);
            $unitPrice = (float) $product->price + random_int(-40, 120);

            $sale = Sale::query()->create([
                'sale_no' => 'SL-'.$saleDate->format('Y').'-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'salesperson_id' => $person->id,
                'customer_name' => $customers->isNotEmpty() && random_int(0, 2) === 0
                    ? $customers->random()
                    : fake()->name(),
                'customer_phone' => '+35798'.random_int(100000, 999999),
                'title' => $product->name,
                'discount' => random_int(0, 50),
                'tax' => 0,
                'currency' => 'USD',
                'status' => $status,
                'payment_method' => 'bank_transfer',
                'sale_date' => $saleDate->toDateString(),
                'confirmed_at' => $status !== 'pending' ? $saleDate->copy()->addHours(4) : null,
                'done_at' => $status === 'done' ? $saleDate->copy()->addDay() : null,
                'source' => $sources[array_rand($sources)],
            ]);

            SaleItem::query()->create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'cost' => (float) ($product->cost ?: $unitPrice * 0.55),
                'total' => $quantity * $unitPrice,
            ]);

            $sale->refresh();
            $sale->recalculate();

            if ($status === 'done' && random_int(0, 1) === 1) {
                SalePayment::query()->create([
                    'sale_id' => $sale->id,
                    'amount' => $sale->total,
                    'method' => 'bank_transfer',
                    'paid_on' => $saleDate->copy()->addDays(2)->toDateString(),
                    'received_by' => $person->id,
                ]);

                $sale->recalculate();
            }
        }
    }

    private function seedCompensations($salespeople): void
    {
        $admin = User::query()->where('is_super_admin', true)->value('id');

        foreach ($salespeople as $index => $person) {
            Compensation::query()->create([
                'user_id' => $person->id,
                'type' => $index % 3 === 0 ? 'incentive' : 'bonus',
                'title' => $index % 3 === 0 ? 'Top performer incentive' : 'Monthly performance bonus',
                'amount' => 150 + ($index * 50),
                'date' => now()->subDays(5)->toDateString(),
                'period_month' => now()->format('Y-m'),
                'status' => 'approved',
                'created_by' => $admin,
            ]);
        }
    }
}
