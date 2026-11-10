<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('payrolls')]
#[Fillable([
    'user_id', 'period_year', 'period_month', 'period_label', 'currency', 'basic_salary',
    'allowances', 'commission', 'bonus', 'incentive', 'deductions', 'tax', 'absent_deduction',
    'gross_salary', 'net_salary', 'present_days', 'absent_days', 'leave_days', 'working_days',
    'status', 'paid_on', 'payment_method', 'notes', 'created_by', 'approved_at', 'approved_by',
])]
class Payroll extends Model
{
    use BelongsToOrganization;

    public const STATUSES = ['draft', 'approved', 'paid'];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'approved_at' => 'datetime',
            'basic_salary' => 'decimal:2',
            'allowances' => 'decimal:2',
            'commission' => 'decimal:2',
            'bonus' => 'decimal:2',
            'incentive' => 'decimal:2',
            'deductions' => 'decimal:2',
            'tax' => 'decimal:2',
            'absent_deduction' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
