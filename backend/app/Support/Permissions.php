<?php

namespace App\Support;

class Permissions
{
    /**
     * The full permission registry grouped for the UI.
     *
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return [
            'Dashboard & Analytics' => [
                'dashboard.view' => 'View dashboard',
                'analytics.view_all' => 'View company-wide analytics',
                'analytics.view_own' => 'View own analytics',
                'reports.view' => 'View reports',
                'reports.export' => 'Export reports',
            ],
            'Users & Access' => [
                'users.view' => 'View users',
                'users.create' => 'Create users',
                'users.update' => 'Update users',
                'users.delete' => 'Delete users',
                'users.impersonate' => 'Login as another user',
                'roles.view' => 'View roles',
                'roles.create' => 'Create roles',
                'roles.update' => 'Update roles',
                'roles.delete' => 'Delete roles',
                'roles.assign' => 'Assign roles to users',
                'permissions.manage' => 'Manage user permissions',
            ],
            'HR & Employees' => [
                'hr.view' => 'View HR overview',
                'employees.view' => 'View employees',
                'employees.create' => 'Add employees',
                'employees.update' => 'Update employees',
                'employees.delete' => 'Delete employees',
                'attendance.view_all' => 'View all attendance',
                'attendance.view_own' => 'View own attendance',
                'attendance.manage' => 'Manage attendance',
                'attendance.mark_own' => 'Clock in / out',
                'leaves.view_all' => 'View all leave requests',
                'leaves.view_own' => 'View own leave requests',
                'leaves.request' => 'Request leave',
                'leaves.approve' => 'Approve leave requests',
                'holidays.view' => 'View holidays',
                'holidays.manage' => 'Manage holidays',
                'targets.view_all' => 'View all targets',
                'targets.view_own' => 'View own targets',
                'targets.manage' => 'Manage targets',
            ],
            'Payroll & Compensation' => [
                'payroll.view_all' => 'View all payroll',
                'payroll.view_own' => 'View own payslips',
                'payroll.manage' => 'Generate & edit payroll',
                'payroll.approve' => 'Approve payroll',
                'payroll.pay' => 'Mark payroll as paid',
                'allowances.view' => 'View salary allowances',
                'allowances.manage' => 'Manage salary allowances',
                'compensations.view' => 'View bonuses & incentives',
                'compensations.manage' => 'Manage bonuses & incentives',
                'commission_rules.view' => 'View commission rules',
                'commission_rules.manage' => 'Manage commission rules',
            ],
            'Sales' => [
                'sales.view' => 'View sales',
                'sales.view_all' => 'View all salespersons sales',
                'sales.create' => 'Create sale entries',
                'sales.update' => 'Update sale entries',
                'sales.delete' => 'Delete sale entries',
                'sales.confirm' => 'Confirm sales',
                'sales.mark_done' => 'Mark sales as done',
                'sales.payments' => 'Manage sale payments',
                'teams.view' => 'View sales teams',
                'teams.manage' => 'Manage sales teams',
                'teams.manage_members' => 'Manage own team members',
            ],
            'Leads' => [
                'leads.view' => 'View leads',
                'leads.view_all' => 'View all leads',
                'leads.view_team' => 'View team / daily leads',
                'leads.create' => 'Create leads',
                'leads.update' => 'Update leads',
                'leads.delete' => 'Delete leads',
                'leads.assign' => 'Assign leads',
                'leads.convert' => 'Convert leads to customers/sales',
            ],
            'CRM' => [
                'contacts.view' => 'View contacts',
                'contacts.manage' => 'Manage contacts',
                'customers.view' => 'View customers',
                'customers.create' => 'Create customers',
                'customers.update' => 'Update customers',
                'customers.delete' => 'Delete customers',
                'orders.view' => 'View orders',
                'orders.manage' => 'Manage orders',
                'invoices.view' => 'View invoices',
                'invoices.create' => 'Create invoices',
                'invoices.update' => 'Update invoices',
                'invoices.delete' => 'Delete invoices',
                'invoices.send' => 'Send invoices',
            ],
            'Products' => [
                'products.view' => 'View products',
                'products.create' => 'Create products',
                'products.update' => 'Update products',
                'products.delete' => 'Delete products',
                'categories.manage' => 'Manage categories',
                'cart.view' => 'View cart',
            ],
            'Communication' => [
                'chat.view' => 'Use internal chat',
                'chat.create_group' => 'Create chat groups',
                'chat.manage_members' => 'Manage chat group members',
                'email.view' => 'View mailbox',
                'email.send' => 'Send emails',
                'email.settings' => 'Configure mailbox connection',
                'whatsapp.view' => 'View WhatsApp inbox',
                'whatsapp.send' => 'Send WhatsApp messages',
                'whatsapp.settings' => 'Configure WhatsApp account',
                'whatsapp.import_contacts' => 'Import WhatsApp contacts',
                'whatsapp.broadcast' => 'Send WhatsApp broadcasts',
                'knowledge.view' => 'View knowledge base',
                'knowledge.manage' => 'Manage knowledge base',
                'bot.view' => 'View bot conversations',
                'bot.settings' => 'Configure bot behaviour',
                'support.view' => 'View support inbox',
                'support.manage' => 'Handle support conversations',
                'calendar.view' => 'View calendar',
                'calendar.manage' => 'Manage calendar',
                'todo.view' => 'View todos',
                'todo.manage' => 'Manage todos',
            ],
            'Files & System' => [
                'files.view' => 'View file manager',
                'files.manage' => 'Manage files',
                'notifications.view' => 'View notifications',
                'settings.view' => 'View settings',
                'settings.manage' => 'Manage company settings',
                'settings.theme' => 'Manage dashboard theme & branding',
                'settings.mail' => 'Manage email / SMTP settings',
                'integrations.manage' => 'Manage integrations & API keys',
                'activity_logs.view' => 'View activity logs',
            ],
            'Organizations' => [
                'organizations.view' => 'View organizations',
                'organizations.create' => 'Create organizations',
                'organizations.update' => 'Update organizations',
                'organizations.delete' => 'Delete organizations',
                'organizations.payments' => 'Record subscription payments',
                'organizations.block' => 'Block / unblock organization access',
                'billing.view' => 'View own billing & subscription',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        $keys = [];

        foreach (static::all() as $group) {
            foreach ($group as $key => $label) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @return array{key: string, label: string, group: string}[]
     */
    public static function rows(): array
    {
        $rows = [];
        $sort = 0;

        foreach (static::all() as $group => $items) {
            foreach ($items as $key => $label) {
                $rows[] = [
                    'key' => $key,
                    'label' => $label,
                    'group' => $group,
                    'module' => explode('.', $key)[0],
                    'sort_order' => $sort++,
                ];
            }
        }

        return $rows;
    }

    public static function groupOf(string $key): string
    {
        return explode('.', $key)[0];
    }
}
