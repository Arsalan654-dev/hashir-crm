<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'company', 'email', 'phone', 'whatsapp_number', 'avatar', 'address', 'city', 'country',
    'rating', 'wallet_balance', 'joining_date', 'status', 'notes', 'assigned_to',
    'whatsapp_contact_id', 'tags',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'wallet_balance' => 'decimal:2',
            'joining_date' => 'date',
            'tags' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lifetimeValue(): float
    {
        return (float) $this->sales()->completed()->sum('total');
    }
}
