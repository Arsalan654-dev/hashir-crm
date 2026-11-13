<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('gmail_accounts')]
#[Fillable([
    'user_id', 'email', 'display_name', 'access_token', 'refresh_token', 'expires_at',
    'scope', 'history_id', 'last_synced_at', 'is_active', 'is_shared', 'sync_enabled',
    'last_error', 'meta',
])]
class GmailAccount extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
            'is_shared' => 'boolean',
            'sync_enabled' => 'boolean',
            'meta' => 'array',
        ];
    }

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : decrypt($value),
            set: fn (?string $value) => $value === null ? null : encrypt($value),
        );
    }

    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : decrypt($value),
            set: fn (?string $value) => $value === null ? null : encrypt($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
