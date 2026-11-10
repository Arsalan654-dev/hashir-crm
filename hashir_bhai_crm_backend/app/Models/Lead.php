<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('leads')]
#[Fillable([
    'lead_no', 'name', 'company', 'email', 'phone', 'whatsapp_number', 'city', 'country',
    'source', 'status', 'priority', 'estimated_value', 'currency', 'message', 'notes',
    'product_interest', 'assigned_to', 'customer_id', 'next_follow_up_at', 'last_contacted_at',
    'converted_at', 'lost_reason', 'whatsapp_contact_id', 'bot_conversation_id', 'created_by', 'meta',
])]
class Lead extends Model
{
    use BelongsToOrganization;

    public const SOURCES = ['manual', 'whatsapp', 'whatsapp_bot', 'website', 'facebook', 'instagram', 'referral', 'call', 'email', 'walk_in'];

    public const STATUSES = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];

    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'next_follow_up_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function whatsappContact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class);
    }

    public function botConversation(): BelongsTo
    {
        return $this->belongsTo(BotConversation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['won', 'lost']);
    }

    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if ($user === null || $user->isSuperAdmin() || $user->can('leads.view_all')) {
            return $query;
        }

        return $query->where('assigned_to', $user->id);
    }

    public static function generateNumber(): string
    {
        $sequence = static::query()->whereYear('created_at', now()->year)->count() + 1;

        return 'LD-'.now()->format('Y').'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
