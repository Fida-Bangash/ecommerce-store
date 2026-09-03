<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAddition extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'stock_before',
        'stock_after',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    /**
     * Get the product that this stock addition belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who added the stock.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
