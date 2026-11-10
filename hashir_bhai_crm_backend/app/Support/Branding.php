<?php

namespace App\Support;

use App\Models\Setting;

class Branding
{
    public const NAME = 'Cyprus Digital Solution';

    public static function name(): string
    {
        return (string) Setting::getValue('brand.name', self::NAME);
    }

    public static function tagline(): string
    {
        return (string) Setting::getValue('brand.tagline', 'CRM & ERP Suite');
    }

    public static function logo(bool $light = false): string
    {
        $key = $light ? 'brand.logo_light' : 'brand.logo_dark';

        return (string) Setting::getValue($key, $light ? 'assets/images/logo-cyprus-light.svg' : 'assets/images/logo-cyprus.svg');
    }

    public static function logoMark(): string
    {
        return (string) Setting::getValue('brand.logo_mark', 'assets/images/logo-cyprus-mark.svg');
    }

    public static function favicon(): string
    {
        return (string) Setting::getValue('brand.favicon', 'assets/images/logo-cyprus-mark.svg');
    }

    public static function primaryColor(): string
    {
        return (string) Setting::getValue('theme.primary_color', '#1f58c7');
    }

    public static function sidebarStyle(): string
    {
        return (string) Setting::getValue('theme.sidebar_style', 'light');
    }

    public static function topbarStyle(): string
    {
        return (string) Setting::getValue('theme.topbar_style', 'light');
    }

    public static function layout(): string
    {
        return (string) Setting::getValue('theme.layout', 'vertical');
    }

    public static function currency(): string
    {
        return (string) Setting::getValue('company.currency', 'USD');
    }

    public static function currencySymbol(): string
    {
        return (string) Setting::getValue('company.currency_symbol', '$');
    }

    public static function timezone(): string
    {
        return (string) Setting::getValue('company.timezone', config('app.timezone', 'UTC'));
    }

    public static function dateFormat(): string
    {
        return (string) Setting::getValue('company.date_format', 'd M Y');
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        return [
            'name' => self::name(),
            'tagline' => self::tagline(),
            'logo' => self::logo(),
            'logo_light' => self::logo(true),
            'logo_mark' => self::logoMark(),
            'favicon' => self::favicon(),
            'primary_color' => self::primaryColor(),
            'sidebar_style' => self::sidebarStyle(),
            'topbar_style' => self::topbarStyle(),
            'layout' => self::layout(),
            'currency' => self::currency(),
            'currency_symbol' => self::currencySymbol(),
            'timezone' => self::timezone(),
            'date_format' => self::dateFormat(),
        ];
    }
}
