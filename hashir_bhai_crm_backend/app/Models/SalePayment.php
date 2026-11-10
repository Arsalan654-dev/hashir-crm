<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('sale_payments')]
#[Fillable(['sale_id', 'amount', 'method', 'reference', 'paid_on', 'notes', 'received_by'])]
class SalePayment extends Model
{
    use BelongsToOrganization;

    public const METHODS = ['cash', 'bank_transfer', 'card', 'cheque', 'online', 'crypto', 'other'];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
