<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\KnowledgeArticle;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\Branding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBrandingSettings();
        $departments = $this->seedDepartments();
        $this->seedSuperAdmin($departments);
        $this->seedHolidays();
        $this->seedKnowledgeBase();
    }

    private function seedBrandingSettings(): void
    {
        Setting::setValue('brand.name', Branding::NAME, 'brand');
        Setting::setValue('brand.tagline', 'Customer Relationship Management', 'brand');
        Setting::setValue('brand.logo_dark', 'assets/images/logo-jaffri.png', 'brand');
        Setting::setValue('brand.logo_light', 'assets/images/logo-jaffri-light.png', 'brand');
        Setting::setValue('brand.logo_mark', 'assets/images/logo-jaffri-mark.png', 'brand');
        Setting::setValue('brand.favicon', 'assets/images/favicon-jaffri.png', 'brand');
        Setting::setValue('theme.primary_color', '#312E81', 'theme');
        Setting::setValue('theme.sidebar_style', 'light', 'theme');
        Setting::setValue('theme.topbar_style', 'light', 'theme');
        Setting::setValue('theme.layout', 'vertical', 'theme');
        Setting::setValue('company.name', "Jaffri's Tech Solutions", 'company');
        Setting::setValue('company.currency', 'USD', 'company');
        Setting::setValue('company.currency_symbol', '$', 'company');
        Setting::setValue('company.timezone', 'Asia/Dubai', 'company');
        Setting::setValue('company.date_format', 'd M Y', 'company');
        Setting::setValue('company.email', 'info@example.com', 'company');
        Setting::setValue('company.phone', '', 'company');
        Setting::setValue('company.address', 'Cyprus', 'company');
        Setting::setValue('sales.commission_rate', '5', 'sales');
        Setting::setValue('payroll.working_days_per_month', '26', 'payroll');
        Setting::setValue('bot.enabled', '1', 'bot');
        Setting::setValue('bot.name', 'Jaffri Assistant', 'bot');
        Setting::setValue('bot.tone', 'friendly-professional', 'bot');
        Setting::setValue('bot.languages', 'en,ur,ar,hi', 'bot');
        Setting::setValue('bot.system_instructions', "You are the sales assistant for Jaffri's CRM. Qualify the lead, share product details from the knowledge base, collect order details and confirm before handoff.", 'bot');
        Setting::setValue('bot.send_products_media', '1', 'bot');
    }

    /**
     * @return array<string, Department>
     */
    private function seedDepartments(): array
    {
        $definitions = [
            'Management' => ['code' => 'MGMT', 'color' => 'primary', 'designations' => [
                ['Chief Executive Officer', 'C-level'], ['Operations Manager', 'Senior'],
            ]],
            'Sales' => ['code' => 'SALES', 'color' => 'success', 'designations' => [
                ['Sales Manager', 'Senior'], ['Senior Sales Executive', 'Mid'], ['Sales Executive', 'Junior'],
            ]],
            'Human Resources' => ['code' => 'HR', 'color' => 'info', 'designations' => [
                ['HR Manager', 'Senior'], ['HR Officer', 'Junior'],
            ]],
            'Finance' => ['code' => 'FIN', 'color' => 'warning', 'designations' => [
                ['Finance Manager', 'Senior'], ['Accountant', 'Mid'],
            ]],
            'Development' => ['code' => 'DEV', 'color' => 'danger', 'designations' => [
                ['Lead Developer', 'Senior'], ['Software Engineer', 'Mid'], ['QA Engineer', 'Mid'],
            ]],
            'Support' => ['code' => 'SUP', 'color' => 'secondary', 'designations' => [
                ['Support Lead', 'Senior'], ['Support Agent', 'Junior'],
            ]],
        ];

        $departments = [];

        foreach ($definitions as $name => $definition) {
            $department = Department::query()->updateOrCreate(
                ['code' => $definition['code']],
                ['name' => $name, 'color' => $definition['color'], 'is_active' => true],
            );

            foreach ($definition['designations'] as [$title, $level]) {
                Designation::query()->updateOrCreate(
                    ['department_id' => $department->id, 'title' => $title],
                    ['level' => $level, 'is_active' => true],
                );
            }

            $departments[$name] = $department;
        }

        return $departments;
    }

    /**
     * @param  array<string, Department>  $departments
     */
    private function seedSuperAdmin(array $departments): void
    {
        $adminPassword = env('CRM_ADMIN_PASSWORD');

        if (! is_string($adminPassword) || $adminPassword === '') {
            throw new \RuntimeException('Set CRM_ADMIN_PASSWORD in the environment before seeding the CRM super admin.');
        }

        $role = Role::query()->where('slug', Role::SUPER_ADMIN)->first();

        $superAdmin = User::query()->firstOrNew(['email' => 'admin@example.com']);

        $superAdmin->fill([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'phone' => '+357 99 000 000',
            'role' => 'admin',
            'role_id' => $role?->id,
            'department_id' => $departments['Management']->id ?? null,
            'designation' => 'Super Administrator',
            'employment_type' => 'full-time',
            'joining_date' => now()->startOfYear(),
            'basic_salary' => 0,
            'salary_currency' => 'USD',
            'is_active' => true,
            'is_super_admin' => true,
            'email_verified_at' => now(),
            'avatar' => 'assets/images/users/avatar-1.jpg',
        ]);

        if (! $superAdmin->exists) {
            $superAdmin->password = Hash::make($adminPassword);
        }

        $superAdmin->save();
    }

    private function seedHolidays(): void
    {
        $holidays = [
            ['New Year\'s Day', '01-01'],
            ['Cyprus Independence Day', '10-01'],
            ['Christmas Day', '12-25'],
            ['Boxing Day', '12-26'],
        ];

        foreach ($holidays as [$name, $md]) {
            Holiday::query()->updateOrCreate(
                ['date' => now()->format('Y').'-'.$md],
                ['name' => $name, 'is_paid' => true],
            );
        }
    }

    private function seedKnowledgeBase(): void
    {
        $articles = [
            ['Company Overview', 'company_info', 'instruction', "Jaffri's Tech Solutions is a software development and digital solutions company offering web development, mobile apps, ERP/CRM systems, and digital marketing services."],
            ['Greeting Script', 'script', 'script', 'Greet the customer warmly and ask how you can help. If they are interested in services, ask about their business requirements and budget.'],
            ['Pricing Policy', 'pricing', 'policy', 'Never confirm final pricing without approval. Share starting ranges from the product catalogue and offer a consultation call for a detailed quote.'],
            ['Order Confirmation Flow', 'sales', 'instruction', 'Collect: full name, company, contact number, email, service/product required, budget, timeline. Confirm details back to the customer before marking the order as confirmed.'],
        ];

        foreach ($articles as [$title, $category, $type, $content]) {
            KnowledgeArticle::query()->updateOrCreate(
                ['title' => $title],
                [
                    'category' => $category,
                    'type' => $type,
                    'content' => $content,
                    'is_active' => true,
                    'share_with_bot' => true,
                    'priority' => 10,
                ],
            );
        }
    }
}
