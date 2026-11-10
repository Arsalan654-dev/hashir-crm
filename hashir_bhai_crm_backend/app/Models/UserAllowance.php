<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('user_allowances')]
#[Fillable(['organization_id', 'user_id', 'label', 'type', 'amount', 'recurrence', 'is_active', 'notes', 'created_by'])]
class UserAllowance extends Model
{
    use BelongsToOrganization, HasFactory;

    public const TYPES = ['allowance', 'deduction'];

    public const TYPE_LABELS = [
        'allowance' => 'Allowance / Earning',
        'deduction' => 'Deduction',
    ];

    public const RECURRENCES = ['monthly', 'one_time'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
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
}
