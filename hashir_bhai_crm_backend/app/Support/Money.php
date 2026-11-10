<?php

namespace App\Support;

class Money
{
    public static function format(float|int|string|null $amount, ?string $symbol = null, int $decimals = 2): string
    {
        $symbol ??= Branding::currencySymbol();

        return $symbol.number_format((float) $amount, $decimals);
    }

    public static function compact(float|int|string|null $amount): string
    {
        $value = (float) $amount;
        $symbol = Branding::currencySymbol();

        return match (true) {
            abs($value) >= 1_000_000_000 => $symbol.number_format($value / 1_000_000_000, 2).'B',
            abs($value) >= 1_000_000 => $symbol.number_format($value / 1_000_000, 2).'M',
            abs($value) >= 1_000 => $symbol.number_format($value / 1_000, 1).'K',
            default => $symbol.number_format($value, 2),
        };
    }

    public static function percent(float|int|string|null $value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals).'%';
    }
}
