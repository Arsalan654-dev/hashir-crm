<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('leave_requests')]
#[Fillable([
    'user_id', 'type', 'start_date', 'end_date', 'days', 'reason', 'status',
    'approved_by', 'approved_at', 'admin_note', 'is_paid',
])]
class LeaveRequest extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['annual', 'sick', 'casual', 'unpaid', 'maternity', 'emergency'];

    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days' => 'decimal:1',
            'approved_at' => 'datetime',
            'is_paid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
