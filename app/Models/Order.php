<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'session_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'city',
        'notes',
        'payment_method',
        'status',
        'subtotal',
        'total',
        'refunded_at',
        'refunded_by',
        'cancelled_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Get the user that placed the order (null for guest orders).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the line items belonging to the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the admin user who refunded this order, if any.
     */
    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    /**
     * Get the admin user who cancelled this order, if any.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Generate a unique, human friendly order number,
     * e.g. "ORD-8F3K2Q1A".
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.strtoupper(Str::random(8));
        } while (self::query()->where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Get the effective status for display purposes: "refunded"
     * takes priority once the whole order has been refunded, and
     * "partially_refunded" shows while only some line items have.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->refunded_at) {
            return 'refunded';
        }

        if ($this->hasAnyItemRefund()) {
            return 'partially_refunded';
        }

        return $this->status;
    }

    /**
     * Get a human readable label for the current (effective) status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->display_status) {
            'processing' => 'Processing',
            'dispatched' => 'Dispatched',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially Refunded',
            default => 'Pending',
        };
    }

    /**
     * Whether at least one line item on this order has had any
     * quantity refunded (used to show the "Partially Refunded" state
     * before every item has been fully refunded).
     */
    public function hasAnyItemRefund(): bool
    {
        return $this->items()->where('refunded_quantity', '>', 0)->exists();
    }

    /**
     * Whether every line item on this order has now been fully
     * refunded, meaning the order as a whole should count as refunded.
     */
    public function allItemsFullyRefunded(): bool
    {
        return $this->items()->count() > 0
            && ! $this->items()->whereColumn('refunded_quantity', '<', 'quantity')->exists();
    }

    /**
     * Whether individual line items on this order can currently be
     * refunded by an admin: same eligibility window as a full-order
     * refund (completed, and not already fully refunded).
     */
    public function canRefundItems(): bool
    {
        return $this->status === 'completed' && ! $this->refunded_at;
    }

    /**
     * Determine whether this order can still be cancelled: only
     * orders that haven't been completed, cancelled or refunded yet.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'dispatched'], true) && ! $this->refunded_at;
    }

    /**
     * Determine whether this order is eligible for a refund: only
     * completed orders that haven't already been refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' && ! $this->refunded_at;
    }

    /**
     * Get the map of statuses this order can currently be moved to,
     * keyed by value with a human readable label. Used to build the
     * "Change Status" dropdown on the admin orders page.
     *
     * @return array<string, string>
     */
    public function availableStatusOptions(): array
    {
        if ($this->refunded_at || $this->status === 'cancelled') {
            return [];
        }

        return match ($this->status) {
            'pending' => [
                'processing' => 'Processing',
                'cancelled' => 'Cancelled',
            ],
            'processing' => [
                'dispatched' => 'Dispatched',
                'cancelled' => 'Cancelled',
            ],
            'dispatched' => [
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ],
            'completed' => [
                'refunded' => 'Refunded',
            ],
            default => [],
        };
    }

    /**
     * Scope a query to filter orders by their effective status
     * ("refunded" is checked via the refunded_at column).
     */
    public function scopeOrderStatus(Builder $query, string $status): Builder
    {
        if ($status === 'refunded') {
            return $query->whereNotNull('refunded_at');
        }

        if ($status === 'partially_refunded') {
            return $query->whereNull('refunded_at')
                ->whereHas('items', fn ($q) => $q->where('refunded_quantity', '>', 0));
        }

        return $query->where('status', $status)->whereNull('refunded_at');
    }
}

