<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('compensations')]
#[Fillable([
    'user_id', 'type', 'title', 'amount', 'date', 'period_month', 'notes',
    'status', 'created_by', 'payroll_id',
])]
class Compensation extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['bonus', 'incentive', 'appreciation', 'penalty', 'advance', 'deduction'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
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

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
