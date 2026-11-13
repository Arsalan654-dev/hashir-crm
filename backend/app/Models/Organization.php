<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('organizations')]
#[Fillable([
    'name', 'slug', 'business_type', 'logo', 'email', 'phone', 'whatsapp', 'website',
    'address', 'city', 'country', 'timezone', 'currency', 'owner_user_id', 'status',
    'payment_status', 'plan_name', 'plan_amount', 'billing_cycle', 'paid_until',
    'trial_ends_at', 'crm_blocked', 'bot_blocked', 'settings', 'notes', 'created_by',
])]
class Organization extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'suspended', 'blocked'];

    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    public const BILLING_CYCLES = ['monthly', 'quarterly', 'yearly', 'one_time'];

    public const BUSINESS_TYPES = [
        'general', 'restaurant', 'ecommerce', 'retail', 'services', 'healthcare', 'education', 'other',
    ];

    public const BUSINESS_TYPE_LABELS = [
        'general' => 'General Business',
        'restaurant' => 'Restaurant / Food',
        'ecommerce' => 'E-commerce',
        'retail' => 'Retail Store',
        'services' => 'Services',
        'healthcare' => 'Healthcare',
        'education' => 'Education',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'plan_amount' => 'decimal:2',
            'paid_until' => 'date',
            'trial_ends_at' => 'date',
            'crm_blocked' => 'boolean',
            'bot_blocked' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isPaid(): bool
    {
        if ($this->payment_status !== 'paid') {
            return false;
        }

        return $this->paid_until === null || $this->paid_until->isToday() || $this->paid_until->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function crmIsBlocked(): bool
    {
        return $this->crm_blocked || ! $this->isActive();
    }

    public function botIsBlocked(): bool
    {
        return $this->bot_blocked || ! $this->isActive();
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function businessTypeLabel(): string
    {
        return self::BUSINESS_TYPE_LABELS[$this->business_type] ?? ucfirst((string) $this->business_type);
    }

    public static function generateSlug(string $name): string
    {
        $base = str($name)->slug()->value() ?: 'organization';
        $slug = $base;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
