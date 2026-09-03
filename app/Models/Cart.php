<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Get the user that owns the cart (null for guest carts).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items belonging to the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total number of units across all cart items.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get the cart subtotal (sum of price * quantity for every item).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->line_total);
    }

    /**
     * Get the cart grand total.
     *
     * Kept separate from the subtotal so taxes, shipping, or discounts
     * can be layered on in one place later without touching callers.
     */
    public function getTotalAttribute(): float
    {
        return $this->subtotal;
    }

    /**
     * Determine whether the cart has no items.
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }
}
