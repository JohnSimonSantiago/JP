<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_item_id',
        'shop_id', // Now nullable for point shop purchases
        'price_paid',
        'currency_type', // NEW: Track if paid with points or cash
        'quantity',
        'status',
        'rejection_reason',
         'counts_for_loyalty',    // ADD THIS
    'loyalty_card_id'      
    ];

    protected $casts = [
        'price_paid' => 'decimal:2', // Changed to decimal to handle cash
        'quantity' => 'integer',
        'currency_type' => 'string',
        'counts_for_loyalty' => 'boolean'  // ADD THIS
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopItem()
    {
        return $this->belongsTo(ShopItem::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeForPointShop($query)
    {
        return $query->whereNull('shop_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePaidWithPoints($query)
    {
        return $query->where('currency_type', 'points');
    }

    public function scopePaidWithCash($query)
    {
        return $query->where('currency_type', 'cash');
    }

    /**
     * Accessors
     */
    public function getTotalAmountAttribute()
    {
        return $this->price_paid * $this->quantity;
    }

    /**
     * Check if this is a point shop purchase
     */
    public function isPointShopPurchase(): bool
    {
        return $this->shop_id === null;
    }

    /**
     * Check if this purchase was paid with points
     */
    public function isPaidWithPoints(): bool
    {
        return $this->currency_type === 'points';
    }

    /**
     * Check if this purchase was paid with cash
     */
    public function isPaidWithCash(): bool
    {
        return $this->currency_type === 'cash';
    }

    /**
     * Get the source name (shop name or "Point Shop")
     */
    public function getSourceNameAttribute(): string
    {
        return $this->shop ? $this->shop->name : 'Point Shop';
    }

    /**
     * Get formatted price with currency symbol
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->currency_type === 'cash') {
            return '$' . number_format($this->price_paid, 2);
        } else {
            return number_format($this->price_paid, 0) . ' points';
        }
    }

    /**
     * Get formatted total amount with currency symbol
     */
    public function getFormattedTotalAttribute(): string
    {
        $total = $this->total_amount;
        if ($this->currency_type === 'cash') {
            return '$' . number_format($total, 2);
        } else {
            return number_format($total, 0) . ' points';
        }
    }
    public function loyaltyCard()
{
    return $this->belongsTo(LoyaltyCard::class);
}
}