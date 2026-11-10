<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value', 'group', 'type', 'is_public'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $values = static::all_cached();

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value),
                'group' => $group,
                'type' => $type,
            ],
        );

        static::forgetCache();
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value),
                    'group' => $group,
                ],
            );
        }

        static::forgetCache();
    }

    /**
     * @return array<string, string|null>
     */
    public static function all_cached(): array
    {
        try {
            return Cache::rememberForever('settings.values', fn () => static::query()->pluck('value', 'key')->all());
        } catch (\Throwable) {
            return static::query()->pluck('value', 'key')->all();
        }
    }

    public static function forgetCache(): void
    {
        Cache::forget('settings.values');
        Cache::forget('settings.all');
    }

    public static function boolean(string $key, bool $default = false): bool
    {
        $value = static::getValue($key);

        return $value === null ? $default : in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }
}
