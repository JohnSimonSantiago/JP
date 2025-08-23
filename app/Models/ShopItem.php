<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ShopItem extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'cash_price',
        'image',
        'is_active',
        'is_active_in_point_shop', // NEW FIELD
        'stock'
    ];

    protected $casts = [
        'price' => 'integer',
        'cash_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_active_in_point_shop' => 'boolean', // NEW CAST
        'stock' => 'integer'
    ];

    protected $appends = ['image_url'];

    /**
     * Get the shop that owns the item
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get all purchases for this item
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    /**
     * Check if user can purchase this item
     */
    public function getPurchaseBlockReason($user, $quantity = 1): ?string
    {
        if (!$user) {
            return 'User must be logged in';
        }

        if (!$this->is_active) {
            return 'This item is not available';
        }

        // For point shop items, check if active in point shop
        if ($this->shop_id === null && !$this->is_active_in_point_shop) {
            return 'This item is not available in the point shop';
        }

        if ($this->stock !== null && $this->stock < $quantity) {
            return 'Not enough stock available';
        }

        $totalCost = $this->price * $quantity;
        if ($user->points < $totalCost) {
            return 'Insufficient points';
        }

        return null;
    }

    /**
     * Scope for point shop items only
     */
    public function scopePointShopOnly($query)
    {
        return $query->where('shop_id', null)
                    ->where('is_active', true)
                    ->where('is_active_in_point_shop', true)
                    ->where('price', '>', 0);
    }

    /**
     * Scope for regular shop items only
     */
    public function scopeShopItemsOnly($query)
    {
        return $query->whereNotNull('shop_id')
                    ->where('is_active', true);
    }

    /**
     * Check if this is a point shop item
     */
    public function isPointShopItem(): bool
    {
        return $this->shop_id === null && $this->price > 0;
    }

    /**
     * Check if this is a regular shop item
     */
    public function isShopItem(): bool
    {
        return $this->shop_id !== null;
    }
}