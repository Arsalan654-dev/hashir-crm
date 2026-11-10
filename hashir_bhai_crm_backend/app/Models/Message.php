<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('messages')]
#[Fillable([
    'chat_channel_id', 'user_id', 'type', 'body', 'attachment_path', 'attachment_name',
    'mime_type', 'reply_to_id', 'meta', 'sent_at',
])]
class Message extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['text', 'image', 'video', 'audio', 'document', 'system'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'chat_channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }
}
