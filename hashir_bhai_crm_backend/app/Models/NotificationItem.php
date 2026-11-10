<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\NotificationItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title', 'body', 'avatar', 'icon', 'icon_bg',
    'time_label', 'is_read', 'user_id',
])]
class NotificationItem extends Model
{
    /** @use HasFactory<NotificationItemFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
