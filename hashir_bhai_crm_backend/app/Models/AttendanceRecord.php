<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('attendance_records')]
#[Fillable(['user_id', 'date', 'check_in', 'check_out', 'status', 'worked_hours', 'overtime_hours', 'notes', 'marked_by'])]
class AttendanceRecord extends Model
{
    use BelongsToOrganization;

    public const STATUSES = ['present', 'absent', 'late', 'half-day', 'leave', 'holiday', 'remote'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'worked_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
