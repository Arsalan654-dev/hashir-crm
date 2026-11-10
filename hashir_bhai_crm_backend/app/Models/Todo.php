<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\TodoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title', 'team', 'status', 'is_completed', 'comments',
    'members', 'due_date', 'user_id',
])]
class Todo extends Model
{
    /** @use HasFactory<TodoFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'members' => 'array',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
