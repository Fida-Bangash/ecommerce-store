<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'rating',
        'comment',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the product that the review belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the registered user who submitted the review (null for
     * reviews submitted by guests).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin user who approved the review.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include reviews awaiting approval.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Determine whether the review has been approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Get a human readable label for the current review status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Approved',
            default => 'Pending',
        };
    }
}
