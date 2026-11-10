<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('chat_channel_members')]
#[Fillable(['chat_channel_id', 'user_id', 'role', 'last_read_at', 'joined_at', 'is_muted'])]
class ChatChannelMember extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'joined_at' => 'datetime',
            'is_muted' => 'boolean',
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
}
