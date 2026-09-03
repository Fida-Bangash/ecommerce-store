<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'color_hex',
        'stock_quantity',
        'extra_price',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'extra_price' => 'decimal:2',
        ];
    }

    /**
     * Get the product that this variant belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the cart items referencing this variant.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    /**
     * A human friendly label combining size and color,
     * e.g. "Size: 42 / Color: Black".
     */
    public function getLabelAttribute(): string
    {
        $parts = [];

        if ($this->size) {
            $parts[] = "Size: {$this->size}";
        }

        if ($this->color) {
            $parts[] = "Color: {$this->color}";
        }

        return implode(' / ', $parts) ?: 'Default';
    }

    /**
     * Whether this variant currently has stock available.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }
}
