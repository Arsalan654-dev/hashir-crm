<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('bot_conversations')]
#[Fillable([
    'external_id', 'whatsapp_contact_id', 'lead_id', 'sale_id', 'invoice_id', 'contact_name',
    'contact_phone', 'status', 'language', 'summary', 'tags', 'transcript', 'order_data',
    'started_at', 'ended_at', 'escalated_at', 'resolved_at', 'handled_by',
])]
class BotConversation extends Model
{
    use BelongsToOrganization;

    public const STATUSES = ['active', 'converted', 'escalated', 'resolved'];

    public const SUGGESTED_TAGS = ['complaint', 'inquiry', 'order', 'urgent', 'feedback', 'payment'];

    protected function casts(): array
    {
        return [
            'transcript' => 'array',
            'order_data' => 'array',
            'tags' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'whatsapp_contact_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isEscalated(): bool
    {
        return $this->status === 'escalated';
    }
}
