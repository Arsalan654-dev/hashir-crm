<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('chat_channels')]
#[Fillable(['type', 'name', 'avatar', 'description', 'created_by', 'whatsapp_contact_id', 'last_message_at', 'is_archived', 'meta'])]
class ChatChannel extends Model
{
    use BelongsToOrganization;

    public const TYPE_DIRECT = 'direct';

    public const TYPE_GROUP = 'group';

    public const TYPE_WHATSAPP = 'whatsapp';

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_archived' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_channel_members')
            ->withPivot(['role', 'last_read_at', 'joined_at', 'is_muted'])
            ->withTimestamps();
    }

    public function memberRecords(): HasMany
    {
        return $this->hasMany(ChatChannelMember::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function whatsappContact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class);
    }

    public function isGroup(): bool
    {
        return $this->type === self::TYPE_GROUP;
    }

    public function displayNameFor(?User $user): string
    {
        if ($this->isGroup() || $this->type === self::TYPE_WHATSAPP) {
            return (string) ($this->name ?: 'Chat');
        }

        $other = $this->members->first(fn (User $member) => $member->id !== $user?->id);

        return $other?->name ?? (string) $this->name;
    }

    public static function findDirectChannel(int $userId, int $otherUserId): ?self
    {
        return static::query()
            ->where('type', self::TYPE_DIRECT)
            ->whereHas('memberRecords', fn ($query) => $query->where('user_id', $userId))
            ->whereHas('memberRecords', fn ($query) => $query->where('user_id', $otherUserId))
            ->withCount('memberRecords')
            ->having('member_records_count', 2)
            ->first();
    }
}
