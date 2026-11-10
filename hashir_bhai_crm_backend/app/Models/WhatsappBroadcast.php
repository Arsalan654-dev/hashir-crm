<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('whatsapp_broadcasts')]
#[Fillable([
    'organization_id', 'whatsapp_account_id', 'user_id', 'title', 'message',
    'recipients_count', 'sent_count', 'failed_count', 'status', 'meta',
])]
class WhatsappBroadcast extends Model
{
    use BelongsToOrganization, HasFactory;

    public const AUDIENCES = ['all', 'leads', 'customers'];

    public const STATUSES = ['completed', 'partial', 'failed'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'partial' => 'warning',
            default => 'danger',
        };
    }
}
