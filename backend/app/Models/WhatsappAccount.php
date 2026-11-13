<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('whatsapp_accounts')]
#[Fillable([
    'name', 'provider', 'phone_number', 'phone_number_id', 'business_account_id', 'waba_id', 'access_token',
    'api_key', 'base_url', 'verify_token', 'app_id', 'app_secret', 'api_version', 'status', 'is_default',
    'bot_enabled', 'webhook_secret', 'last_synced_at', 'last_error', 'meta',
])]
class WhatsappAccount extends Model
{
    use BelongsToOrganization;

    public const PROVIDERS = ['meta_cloud', 'whatchimp'];

    public const PROVIDER_LABELS = [
        'meta_cloud' => 'Meta WhatsApp Cloud API',
        'whatchimp' => 'WhatChimp (Meta Business Partner)',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'is_default' => 'boolean',
            'bot_enabled' => 'boolean',
            'meta' => 'array',
        ];
    }

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : decrypt($value),
            set: fn (?string $value) => $value === null ? null : encrypt($value),
        );
    }

    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : decrypt($value),
            set: fn (?string $value) => $value === null ? null : encrypt($value),
        );
    }

    protected function appSecret(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : decrypt($value),
            set: fn (?string $value) => $value === null ? null : encrypt($value),
        );
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(WhatsappContact::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function isWhatchimp(): bool
    {
        return $this->provider === 'whatchimp';
    }

    public function isMetaCloud(): bool
    {
        return $this->provider === 'meta_cloud' || blank($this->provider);
    }

    public function providerLabel(): string
    {
        return self::PROVIDER_LABELS[$this->provider] ?? ucfirst((string) $this->provider);
    }

    public function isConfigured(): bool
    {
        if ($this->isWhatchimp()) {
            return filled($this->api_key) && filled($this->phone_number_id);
        }

        return filled($this->phone_number_id) && filled($this->access_token);
    }

    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->first()
            ?? static::query()->first();
    }
}
