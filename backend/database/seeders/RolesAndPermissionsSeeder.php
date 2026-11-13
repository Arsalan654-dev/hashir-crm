<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Default permission bundles per seeded role.
     *
     * @var array<string, array{name: string, color: string, permissions: array<int, string>|string}>
     */
    private array $roles = [
        Role::SUPER_ADMIN => ['name' => 'Super Admin', 'color' => 'danger', 'permissions' => '*'],
        'admin' => ['name' => 'Admin', 'color' => 'primary', 'permissions' => [
            'dashboard.view', 'analytics.view_all', 'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.update', 'users.delete', 'roles.view', 'roles.assign',
            'hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'attendance.view_all', 'attendance.manage', 'leaves.view_all', 'leaves.approve',
            'holidays.view', 'holidays.manage', 'targets.view_all', 'targets.manage',
            'payroll.view_all', 'payroll.manage', 'payroll.approve', 'payroll.pay',
            'allowances.view', 'allowances.manage',
            'compensations.view', 'compensations.manage', 'commission_rules.view', 'commission_rules.manage',
            'sales.view', 'sales.view_all', 'sales.create', 'sales.update', 'sales.delete',
            'sales.confirm', 'sales.mark_done', 'sales.payments',
            'teams.view', 'teams.manage', 'teams.manage_members',
            'leads.view', 'leads.view_all', 'leads.view_team', 'leads.create', 'leads.update', 'leads.assign', 'leads.convert', 'leads.delete',
            'contacts.view', 'contacts.manage', 'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'orders.view', 'orders.manage', 'invoices.view', 'invoices.create', 'invoices.update', 'invoices.send',
            'products.view', 'products.create', 'products.update', 'categories.manage', 'cart.view',
            'chat.view', 'chat.create_group', 'email.view', 'email.send',
            'whatsapp.view', 'whatsapp.send', 'whatsapp.import_contacts', 'whatsapp.broadcast',
            'knowledge.view', 'bot.view', 'bot.settings', 'support.view', 'support.manage', 'calendar.view', 'calendar.manage', 'todo.view', 'todo.manage',
            'files.view', 'files.manage', 'notifications.view', 'settings.view', 'activity_logs.view',
        ]],
        'hr-manager' => ['name' => 'HR Manager', 'color' => 'success', 'permissions' => [
            'dashboard.view', 'analytics.view_all', 'reports.view',
            'users.view', 'employees.view', 'employees.create', 'employees.update',
            'hr.view', 'attendance.view_all', 'attendance.manage', 'leaves.view_all', 'leaves.approve',
            'holidays.view', 'holidays.manage', 'targets.view_all',
            'payroll.view_all', 'payroll.manage', 'compensations.view', 'compensations.manage',
            'allowances.view', 'allowances.manage',
            'commission_rules.view', 'customers.view', 'chat.view', 'calendar.view', 'calendar.manage',
            'todo.view', 'todo.manage', 'notifications.view', 'activity_logs.view',
        ]],
        'sales-manager' => ['name' => 'Sales Manager', 'color' => 'info', 'permissions' => [
            'dashboard.view', 'analytics.view_all', 'reports.view', 'reports.export',
            'sales.view', 'sales.view_all', 'sales.create', 'sales.update', 'sales.confirm', 'sales.mark_done', 'sales.payments',
            'teams.view', 'teams.manage', 'teams.manage_members',
            'allowances.view',
            'leads.view', 'leads.view_all', 'leads.view_team', 'leads.create', 'leads.update', 'leads.assign', 'leads.convert',
            'contacts.view', 'customers.view', 'customers.create', 'customers.update',
            'products.view', 'orders.view', 'invoices.view', 'invoices.create', 'invoices.send',
            'targets.view_all', 'targets.manage', 'commission_rules.view',
            'employees.view', 'attendance.view_all', 'chat.view', 'chat.create_group',
            'whatsapp.view', 'whatsapp.send', 'knowledge.view', 'calendar.view', 'calendar.manage',
            'todo.view', 'todo.manage', 'notifications.view',
        ]],
        'sales-person' => ['name' => 'Sales Person', 'color' => 'warning', 'permissions' => [
            'dashboard.view', 'analytics.view_own',
            'sales.view', 'sales.create', 'sales.update',
            'teams.view',
            'leads.view', 'leads.view_team', 'leads.create', 'leads.update', 'leads.convert',
            'customers.view', 'customers.create', 'products.view', 'cart.view',
            'invoices.view', 'targets.view_own', 'attendance.view_own', 'attendance.mark_own',
            'leaves.view_own', 'leaves.request', 'payroll.view_own',
            'chat.view', 'whatsapp.view', 'whatsapp.send', 'knowledge.view',
            'calendar.view', 'calendar.manage', 'todo.view', 'todo.manage', 'notifications.view',
        ]],
        'employee' => ['name' => 'Employee', 'color' => 'secondary', 'permissions' => [
            'dashboard.view', 'attendance.view_own', 'attendance.mark_own',
            'leaves.view_own', 'leaves.request', 'payroll.view_own', 'targets.view_own',
            'chat.view', 'calendar.view', 'todo.view', 'todo.manage', 'notifications.view',
        ]],
        Role::ORGANIZATION_ADMIN => ['name' => 'Organization Admin', 'color' => 'primary', 'permissions' => [
            'dashboard.view', 'analytics.view_all', 'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign', 'permissions.manage',
            'hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'attendance.view_all', 'attendance.manage', 'leaves.view_all', 'leaves.approve',
            'holidays.view', 'holidays.manage', 'targets.view_all', 'targets.manage',
            'payroll.view_all', 'payroll.manage', 'payroll.approve', 'payroll.pay',
            'allowances.view', 'allowances.manage',
            'compensations.view', 'compensations.manage', 'commission_rules.view', 'commission_rules.manage',
            'sales.view', 'sales.view_all', 'sales.create', 'sales.update', 'sales.delete',
            'sales.confirm', 'sales.mark_done', 'sales.payments',
            'teams.view', 'teams.manage', 'teams.manage_members',
            'leads.view', 'leads.view_all', 'leads.view_team', 'leads.create', 'leads.update', 'leads.delete', 'leads.assign', 'leads.convert',
            'contacts.view', 'contacts.manage',
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'orders.view', 'orders.manage',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete', 'invoices.send',
            'products.view', 'products.create', 'products.update', 'products.delete', 'categories.manage', 'cart.view',
            'chat.view', 'chat.create_group', 'chat.manage_members',
            'email.view', 'email.send', 'email.settings',
            'whatsapp.view', 'whatsapp.send', 'whatsapp.settings', 'whatsapp.import_contacts', 'whatsapp.broadcast',
            'knowledge.view', 'knowledge.manage', 'bot.view', 'bot.settings', 'support.view', 'support.manage',
            'calendar.view', 'calendar.manage', 'todo.view', 'todo.manage',
            'files.view', 'files.manage', 'notifications.view',
            'settings.view', 'settings.manage', 'settings.theme', 'activity_logs.view',
            'billing.view',
        ]],
        Role::SUPPORT_AGENT => ['name' => 'Support Agent', 'color' => 'info', 'permissions' => [
            'dashboard.view', 'analytics.view_own',
            'whatsapp.view', 'whatsapp.send', 'bot.view', 'knowledge.view',
            'support.view', 'support.manage',
            'chat.view', 'leads.view', 'leads.create', 'leads.update',
            'contacts.view', 'customers.view', 'orders.view',
            'calendar.view', 'todo.view', 'todo.manage', 'notifications.view',
        ]],
        Role::KITCHEN_STAFF => ['name' => 'Kitchen Staff', 'color' => 'warning', 'permissions' => [
            'dashboard.view', 'orders.view', 'orders.manage',
            'calendar.view', 'todo.view', 'todo.manage', 'notifications.view',
        ]],
    ];

    public function run(): void
    {
        foreach (Permissions::rows() as $row) {
            Permission::query()->updateOrCreate(['key' => $row['key']], $row);
        }

        $allPermissionIds = Permission::query()->pluck('id', 'key');

        foreach ($this->roles as $slug => $definition) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'color' => $definition['color'],
                    'is_system' => true,
                    'is_active' => true,
                ],
            );

            $keys = $definition['permissions'] === '*'
                ? $allPermissionIds->keys()->all()
                : $definition['permissions'];

            $ids = collect($keys)
                ->map(fn (string $key) => $allPermissionIds[$key] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($ids);
        }
    }
}
