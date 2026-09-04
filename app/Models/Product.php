<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The stock quantity at or below which a product is considered
     * low on stock (but not yet out of stock).
     */
    public const LOW_STOCK_THRESHOLD = 10;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'regular_price',
        'discount_price',
        'stock_quantity',
        'description',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'status' => 'string',
        ];
    }

    /**
     * Get the category that the product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the images belonging to the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the size/color variants belonging to the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the cart items referencing this product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the stock addition history for this product, newest first.
     */
    public function stockAdditions(): HasMany
    {
        return $this->hasMany(StockAddition::class)->latest();
    }

    /**
     * Get every review submitted for this product, regardless of
     * approval status.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * Get only the approved reviews for this product, the ones
     * customers are allowed to see on the product page.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->approved()->latest();
    }

    /**
     * Get the price that should actually be charged: the discount
     * price when one is set, otherwise the regular price.
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price ?? $this->regular_price);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter products by stock status.
     *
     * Accepts "in_stock", "low_stock" or "out_of_stock".
     */
    public function scopeStockStatus($query, string $status)
    {
        return match ($status) {
            'out_of_stock' => $query->where('stock_quantity', 0),
            'low_stock' => $query->whereBetween('stock_quantity', [1, self::LOW_STOCK_THRESHOLD]),
            'in_stock' => $query->where('stock_quantity', '>', self::LOW_STOCK_THRESHOLD),
            default => $query,
        };
    }

    /**
     * Determine whether the product is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the current stock status of the product.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= self::LOW_STOCK_THRESHOLD) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Get a human readable label for the current stock status.
     */
    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            default => 'In Stock',
        };
    }

    /**
     * Get the URL of the product's primary (first) image.
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->images->first()?->image_url;
    }

    /**
     * Get the average star rating across all approved reviews,
     * rounded to one decimal place. Returns 0 when there are no
     * approved reviews yet.
     */
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->approvedReviews->avg('rating'), 1);
    }

    /**
     * Get the total number of approved reviews for this product.
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews->count();
    }

    /**
     * Determine whether the product has any size/color variants
     * configured.
     */
    public function getHasVariantsAttribute(): bool
    {
        return $this->variants->isNotEmpty();
    }

    /**
     * Get the distinct list of available sizes across all variants,
     * in the order they were first added.
     *
     * @return array<int, string>
     */
    public function getAvailableSizesAttribute(): array
    {
        return $this->variants
            ->pluck('size')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get the distinct list of available colors across all variants,
     * in the order they were first added.
     *
     * @return array<int, array<string, ?string>>
     */
    public function getAvailableColorsAttribute(): array
    {
        return $this->variants
            ->filter(fn (ProductVariant $variant) => $variant->color)
            ->unique('color')
            ->values()
            ->map(fn (ProductVariant $variant) => [
                'name' => $variant->color,
                'hex' => $variant->color_hex,
            ])
            ->all();
    }
}
