<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Support\BotSettings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Table('kitchen_orders')]
#[Fillable([
    'organization_id', 'order_no', 'lead_id', 'whatsapp_contact_id', 'customer_name', 'customer_phone',
    'delivery_address', 'subtotal', 'delivery_fee', 'discount', 'total', 'currency', 'status',
    'payment_method', 'payment_status', 'payment_token', 'payment_reference', 'confirmed_at',
    'preparing_at', 'ready_at', 'dispatched_at', 'delivered_at', 'cancelled_at', 'handled_by',
    'notes', 'source', 'meta',
])]
class KitchenOrder extends Model
{
    use BelongsToOrganization, HasFactory;

    public const STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'dispatched', 'delivered', 'cancelled'];

    public const STATUS_LABELS = [
        'pending' => 'New',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'dispatched' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'pending' => 'danger',
        'confirmed' => 'primary',
        'preparing' => 'warning',
        'ready' => 'info',
        'dispatched' => 'secondary',
        'delivered' => 'success',
        'cancelled' => 'dark',
    ];

    /**
     * Next forward status in the fulfilment flow.
     *
     * @var array<string, string>
     */
    public const NEXT_STATUS = [
        'pending' => 'confirmed',
        'confirmed' => 'preparing',
        'preparing' => 'ready',
        'ready' => 'dispatched',
        'dispatched' => 'delivered',
    ];

    public const PAYMENT_METHODS = ['cod', 'online'];

    public const PAYMENT_METHOD_LABELS = [
        'cod' => 'Cash on Delivery',
        'online' => 'Online Payment',
    ];

    public const PAYMENT_STATUSES = ['unpaid', 'pending_verification', 'paid'];

    public const TIMESTAMP_COLUMNS = [
        'confirmed' => 'confirmed_at',
        'preparing' => 'preparing_at',
        'ready' => 'ready_at',
        'dispatched' => 'dispatched_at',
        'delivered' => 'delivered_at',
        'cancelled' => 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'whatsapp_contact_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(KitchenOrderItem::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['delivered', 'cancelled'], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function nextStatus(): ?string
    {
        return self::NEXT_STATUS[$this->status] ?? null;
    }

    public function paymentMethodLabel(): string
    {
        if (blank($this->payment_method)) {
            return 'Not selected';
        }

        return self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    public function itemsSummary(): string
    {
        $this->loadMissing('items');

        return $this->items
            ->map(fn (KitchenOrderItem $item): string => rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.').' x '.$item->name)
            ->implode(', ');
    }

    public function paymentLink(): string
    {
        $this->ensurePaymentToken();

        return url('/pay/'.$this->order_no.'?t='.$this->payment_token);
    }

    public function ensurePaymentToken(): string
    {
        if (blank($this->payment_token)) {
            $this->forceFill(['payment_token' => Str::random(48)])->save();
        }

        return (string) $this->payment_token;
    }

    public function messageFor(string $status): string
    {
        $template = (string) BotSettings::get(
            $this->organization,
            'kitchen.msg_'.$status,
            '',
        );

        if ($template === '') {
            $template = (string) (BotSettings::DEFAULTS['kitchen.msg_'.$status] ?? '');
        }

        return str_replace(
            ['{name}', '{order_no}', '{total}', '{payment_link}', '{items}'],
            [
                $this->customer_name,
                $this->order_no,
                number_format((float) $this->total, 2).' '.$this->currency,
                $this->paymentLink(),
                $this->itemsSummary(),
            ],
            $template,
        );
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $sequence = static::query()->whereYear('created_at', now()->year)->count() + 1;

        do {
            $number = 'ORD-'.$year.'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::query()->where('order_no', $number)->exists());

        return $number;
    }
}
