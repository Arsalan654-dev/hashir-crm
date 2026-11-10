<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Models\Setting;
use App\Models\WhatsappAccount;
use App\Support\Activity;
use App\Support\Branding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('pages.settings', [
            'settings' => [
                'company' => Setting::query()->where('group', 'company')->pluck('value', 'key'),
                'brand' => Setting::query()->where('group', 'brand')->pluck('value', 'key'),
                'sales' => Setting::query()->where('group', 'sales')->pluck('value', 'key'),
                'payroll' => Setting::query()->where('group', 'payroll')->pluck('value', 'key'),
            ],
            'timezones' => timezone_identifiers_list(),
            'currencies' => ['USD', 'EUR', 'GBP', 'AED', 'PKR', 'INR', 'SAR', 'QAR', 'TRY', 'AUD', 'CAD'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_currency' => ['nullable', 'string', 'max:10'],
            'company_currency_symbol' => ['nullable', 'string', 'max:5'],
            'company_timezone' => ['nullable', 'string', 'max:80'],
            'company_date_format' => ['nullable', 'string', 'max:20'],
            'sales_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sales_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payroll_working_days_per_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'payroll_late_deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        $map = [
            'company_name' => 'company.name',
            'company_email' => 'company.email',
            'company_phone' => 'company.phone',
            'company_website' => 'company.website',
            'company_address' => 'company.address',
            'company_currency' => 'company.currency',
            'company_currency_symbol' => 'company.currency_symbol',
            'company_timezone' => 'company.timezone',
            'company_date_format' => 'company.date_format',
            'sales_commission_rate' => 'sales.commission_rate',
            'sales_tax_rate' => 'sales.tax_rate',
            'payroll_working_days_per_month' => 'payroll.working_days_per_month',
            'payroll_late_deduction' => 'payroll.late_deduction',
        ];

        foreach ($map as $input => $key) {
            if ($request->filled($input)) {
                Setting::setValue($key, $request->input($input), str_starts_with($key, 'sales') ? 'sales' : (str_starts_with($key, 'payroll') ? 'payroll' : 'company'));
            }
        }

        Activity::log('settings_updated', 'Company settings updated.', null, [], 'settings');

        return back()->with('status', 'Settings saved successfully.');
    }

    public function theme(): View
    {
        return view('pages.settings-theme', [
            'branding' => Branding::toArray(),
        ]);
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand_name' => ['nullable', 'string', 'max:100'],
            'brand_tagline' => ['nullable', 'string', 'max:150'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'sidebar_style' => ['nullable', 'in:dark,light,brand'],
            'topbar_style' => ['nullable', 'in:light,dark'],
            'theme_layout' => ['nullable', 'in:vertical,horizontal'],
            'logo_dark' => ['nullable', 'image', 'max:2048'],
            'logo_light' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
        ]);

        foreach ([
            'brand_name' => 'brand.name',
            'brand_tagline' => 'brand.tagline',
            'primary_color' => 'theme.primary_color',
            'sidebar_style' => 'theme.sidebar_style',
            'topbar_style' => 'theme.topbar_style',
            'theme_layout' => 'theme.layout',
        ] as $input => $key) {
            if ($request->filled($input)) {
                Setting::setValue($key, $request->input($input), str_starts_with($key, 'theme') ? 'theme' : 'brand');
            }
        }

        foreach (['logo_dark' => 'brand.logo_dark', 'logo_light' => 'brand.logo_light', 'favicon' => 'brand.favicon'] as $input => $key) {
            if ($request->hasFile($input)) {
                Setting::setValue($key, $request->file($input)->store('branding', 'public'), 'brand');
            }
        }

        Activity::log('theme_updated', 'Theme & branding updated.', null, [], 'settings');

        return back()->with('status', 'Theme & branding updated.');
    }

    public function integrations(): View
    {
        return view('pages.settings-integrations', [
            'settings' => Setting::query()->where('group', 'integrations')->pluck('value', 'key'),
            'whatsappAccount' => WhatsappAccount::query()->orderByDesc('is_default')->first(),
            'gmailAccounts' => GmailAccount::query()->orderByDesc('is_active')->get(),
        ]);
    }

    public function updateIntegrations(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
            'google_redirect_uri' => ['nullable', 'string', 'max:255'],
            'n8n_webhook_url' => ['nullable', 'string', 'max:255'],
            'n8n_api_key' => ['nullable', 'string', 'max:255'],
            'bot_shared_secret' => ['nullable', 'string', 'max:255'],
            'maps_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                Setting::setValue('integrations.'.$key, $value, 'integrations');
            }
        }

        Activity::log('integrations_updated', 'Integration keys updated.', null, [], 'settings');

        return back()->with('status', 'Integration settings saved.');
    }
}
