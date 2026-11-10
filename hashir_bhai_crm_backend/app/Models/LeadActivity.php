<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('lead_activities')]
#[Fillable(['lead_id', 'user_id', 'type', 'title', 'body', 'happened_at', 'meta'])]
class LeadActivity extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['note', 'call', 'email', 'whatsapp', 'meeting', 'status_change', 'follow_up', 'bot'];

    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
