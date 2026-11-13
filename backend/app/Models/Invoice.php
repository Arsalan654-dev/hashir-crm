<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no', 'customer_id', 'sale_id', 'billing_name', 'email', 'phone',
    'address', 'invoice_date', 'due_date', 'status', 'amount', 'paid_amount', 'balance',
    'currency', 'payment_method', 'description', 'tax', 'discount', 'shipping',
    'sent_at', 'sent_via', 'notes', 'terms', 'created_by',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isPaid(): bool
    {
        return in_array(strtolower((string) $this->status), ['paid', 'settled'], true);
    }

    public function outstanding(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function statusColor(): string
    {
        return match (strtolower((string) $this->status)) {
            'paid', 'settled' => 'success',
            'pending', 'draft' => 'warning',
            'overdue', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
