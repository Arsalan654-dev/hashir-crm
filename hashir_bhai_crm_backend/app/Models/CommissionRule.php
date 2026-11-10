<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('commission_rules')]
#[Fillable([
    'name', 'basis', 'value', 'applies_to', 'product_id', 'category_id', 'user_id',
    'min_sale_amount', 'is_active', 'starts_on', 'ends_on', 'notes',
])]
class CommissionRule extends Model
{
    use BelongsToOrganization;

    public const BASIS_PERCENTAGE = 'percentage';

    public const BASIS_FIXED = 'fixed';

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_sale_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActiveOn(string $date): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_on && $this->starts_on->toDateString() > $date) {
            return false;
        }

        return ! ($this->ends_on && $this->ends_on->toDateString() < $date);
    }

    public function calculate(float $amount): float
    {
        return $this->basis === self::BASIS_FIXED
            ? (float) $this->value
            : round($amount * ((float) $this->value / 100), 2);
    }
}
