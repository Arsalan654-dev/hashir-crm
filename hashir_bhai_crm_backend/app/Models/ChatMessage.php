<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chat_contact_id', 'body', 'is_outgoing', 'day_label', 'sent_at'])]
class ChatMessage extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'is_outgoing' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ChatContact::class, 'chat_contact_id');
    }
}
