<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('targets')]
#[Fillable([
    'user_id', 'period_type', 'period_start', 'period_end', 'target_amount',
    'target_orders', 'bonus_amount', 'title', 'notes', 'created_by',
])]
class Target extends Model
{
    use BelongsToOrganization;

    public const PERIODS = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'target_amount' => 'decimal:2',
            'target_orders' => 'decimal:0',
            'bonus_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function achievedAmount(): float
    {
        return (float) Sale::query()
            ->where('salesperson_id', $this->user_id)
            ->whereIn('status', ['confirmed', 'done'])
            ->whereBetween('sale_date', [$this->period_start, $this->period_end])
            ->sum('total');
    }

    public function progressPercent(): float
    {
        $target = (float) $this->target_amount;

        return $target > 0 ? min(100, round($this->achievedAmount() / $target * 100, 1)) : 0.0;
    }
}
