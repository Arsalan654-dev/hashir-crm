<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('subscription_payments')]
#[Fillable([
    'organization_id', 'amount', 'currency', 'method', 'reference', 'status',
    'period_start', 'period_end', 'paid_at', 'proof_path', 'recorded_by', 'notes', 'meta',
])]
class SubscriptionPayment extends Model
{
    use HasFactory;

    public const METHODS = ['bank_transfer', 'cash', 'card', 'stripe', 'paypal', 'other'];

    public const METHOD_LABELS = [
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'card' => 'Card',
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
        'other' => 'Other',
    ];

    public const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function methodLabel(): string
    {
        return self::METHOD_LABELS[$this->method] ?? ucfirst((string) $this->method);
    }
}
