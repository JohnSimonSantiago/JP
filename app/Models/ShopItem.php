<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ShopItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'image',
        'is_active',
        'stock',
        'properties'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'properties' => 'array'
    ];

    protected $appends = [
        'image_url',
        'total_sold',
        'is_in_stock'
    ];

    /**
     * Relationships
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function completedPurchases()
    {
        return $this->hasMany(Purchase::class)->where('status', 'completed');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock')->orWhere('stock', '>', 0);
        });
    }

    public function scopeByCategory($query, $category)
    {
        // Since we don't have categories anymore, this scope does nothing
        return $query;
    }

    public function scopeByShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeForShop($query, Shop $shop)
    {
        return $query->where('shop_id', $shop->id);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->inStock()->whereHas('shop', function ($q) {
            $q->active()->verified();
        });
    }

    /**
     * Accessors
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function getTotalSoldAttribute()
    {
        return $this->completedPurchases()->sum('quantity');
    }

    public function getIsInStockAttribute()
    {
        return $this->stock === null || $this->stock > 0;
    }

    /**
     * Methods
     */
    public function isInStock($quantity = 1)
    {
        return $this->stock === null || $this->stock >= $quantity;
    }

    public function decrementStock($quantity = 1)
    {
        if ($this->stock !== null) {
            $this->decrement('stock', $quantity);
        }
    }

    public function canBePurchasedBy(User $user, $quantity = 1)
    {
        // Check if item is active and in stock
        if (!$this->is_active || !$this->isInStock($quantity)) {
            return false;
        }

        // Check if shop is active and verified
        if (!$this->shop || !$this->shop->is_active || !$this->shop->is_verified) {
            return false;
        }

        // Check if user has enough points
        $totalCost = $this->price * $quantity;
        return $user->points >= $totalCost;
    }

    public function getPurchaseBlockReason(User $user, $quantity = 1)
    {
        if (!$this->is_active) {
            return 'This item is no longer available';
        }

        if (!$this->shop || !$this->shop->is_active) {
            return 'The shop is currently unavailable';
        }

        if (!$this->shop->is_verified) {
            return 'The shop is not yet verified';
        }

        if (!$this->isInStock($quantity)) {
            return $this->stock === 0 ? 'Out of stock' : "Only {$this->stock} items available";
        }

        $totalCost = $this->price * $quantity;
        if ($user->points < $totalCost) {
            return "Insufficient points. You need " . number_format($totalCost) . " points.";
        }

        return null;
    }

    /**
     * Get default icon for items (since no categories)
     */
    public static function getDefaultIcon()
    {
        return 'pi pi-box';
    }

    /**
     * Remove category-based methods since we don't use categories
     */
}