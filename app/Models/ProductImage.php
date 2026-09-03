<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
    ];

    /**
     * Get the product that the image belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full public URL of the image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return Storage::disk('public')->url($this->path);
    }
}
