<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('sales')]
#[Fillable([
    'sale_no', 'salesperson_id', 'lead_id', 'customer_id', 'invoice_id', 'customer_name',
    'customer_phone', 'customer_email', 'title', 'description', 'subtotal', 'discount', 'tax',
    'total', 'cost', 'commission_amount', 'currency', 'status', 'payment_status', 'paid_amount',
    'payment_method', 'sale_date', 'confirmed_at', 'done_at', 'done_by', 'confirmed_by',
    'source', 'notes', 'admin_note', 'created_by', 'meta',
])]
class Sale extends Model
{
    use BelongsToOrganization;

    public const STATUSES = ['pending', 'confirmed', 'done', 'rejected', 'cancelled'];

    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    public const SOURCES = ['manual', 'whatsapp', 'whatsapp_bot', 'website', 'referral', 'phone'];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'confirmed_at' => 'datetime',
            'done_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'cost' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function doneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'done']);
    }

    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if ($user === null || $user->isSuperAdmin() || $user->can('sales.view_all')) {
            return $query;
        }

        return $query->whereIn('salesperson_id', $user->teamMemberIds());
    }

    public function profit(): float
    {
        return (float) $this->total - (float) $this->cost;
    }

    public function balance(): float
    {
        return (float) $this->total - (float) $this->paid_amount;
    }

    public function recalculate(): void
    {
        $this->subtotal = (float) $this->items->sum('total');
        $this->total = max(0, $this->subtotal - (float) $this->discount + (float) $this->tax);
        $this->cost = (float) $this->items->sum(fn (SaleItem $item) => (float) $item->cost * (float) $item->quantity);
        $this->paid_amount = (float) $this->payments->sum('amount');
        $this->payment_status = match (true) {
            $this->paid_amount <= 0 => 'unpaid',
            $this->paid_amount + 0.009 < (float) $this->total => 'partial',
            default => 'paid',
        };
        $this->save();
    }

    public static function generateNumber(): string
    {
        $sequence = static::query()->whereYear('created_at', now()->year)->count() + 1;

        return 'SL-'.now()->format('Y').'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
