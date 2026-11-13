<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('whatsapp_contacts')]
#[Fillable([
    'whatsapp_account_id', 'wa_id', 'phone', 'name', 'profile_name', 'avatar_url', 'email',
    'company', 'source', 'is_blocked', 'is_business', 'last_message_at', 'lead_id',
    'customer_id', 'chat_channel_id', 'tags', 'meta',
])]
class WhatsappContact extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_blocked' => 'boolean',
            'is_business' => 'boolean',
            'tags' => 'array',
            'meta' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function chatChannel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function displayName(): string
    {
        return (string) ($this->name ?: $this->profile_name ?: $this->phone ?: $this->wa_id);
    }
}
