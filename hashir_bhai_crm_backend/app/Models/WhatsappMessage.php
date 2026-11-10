<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('whatsapp_messages')]
#[Fillable([
    'whatsapp_account_id', 'whatsapp_contact_id', 'chat_channel_id', 'wa_message_id', 'direction',
    'type', 'body', 'caption', 'media_id', 'media_url', 'mime_type', 'filename', 'status',
    'error_message', 'raw', 'sent_at',
])]
class WhatsappMessage extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'raw' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'whatsapp_contact_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'chat_channel_id');
    }

    public function isIncoming(): bool
    {
        return $this->direction === 'in';
    }
}
