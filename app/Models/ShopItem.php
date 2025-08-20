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
        'price',           // Points price
        'cash_price',      // Cash price
        'image',
        'is_active',
        'stock',
        'properties'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'properties' => 'array',
        'price' => 'integer',
        'cash_price' => 'decimal:2'
    ];

    protected $appends = [
        'image_url',
        'total_sold',
        'is_in_stock',
        'available_payment_methods',
        'formatted_prices'
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
            $q->where('is_active', true)->where('is_verified', true);
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

    public function getAvailablePaymentMethodsAttribute()
    {
        $methods = [];
        
        if ($this->price > 0) {
            $methods[] = 'points';
        }
        
        if ($this->cash_price > 0) {
            $methods[] = 'cash';
        }
        
        return $methods;
    }

    public function getFormattedPricesAttribute()
    {
        $prices = [];
        
        if ($this->price > 0) {
            $prices['points'] = [
                'amount' => $this->price,
                'formatted' => number_format($this->price) . ' points'
            ];
        }
        
        if ($this->cash_price > 0) {
            $prices['cash'] = [
                'amount' => $this->cash_price,
                'formatted' => '$' . number_format($this->cash_price, 2)
            ];
        }
        
        return $prices;
    }

    /**
     * Payment Methods
     */
    public function acceptsPoints()
    {
        return $this->price > 0;
    }

    public function acceptsCash()
    {
        return $this->cash_price > 0;
    }

    public function acceptsBothPayments()
    {
        return $this->acceptsPoints() && $this->acceptsCash();
    }

    /**
     * Stock and Purchase Methods
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

    public function canBePurchasedBy(User $user, $quantity = 1, $paymentMethod = 'points')
    {
        // Check if item is active and in stock
        if (!$this->is_active || !$this->isInStock($quantity)) {
            return false;
        }

        // Check if shop is active and verified
        if (!$this->shop || !$this->shop->is_active || !$this->shop->is_verified) {
            return false;
        }

        // Check if payment method is accepted
        if (!in_array($paymentMethod, $this->available_payment_methods)) {
            return false;
        }

        // Check if user can afford it
        if ($paymentMethod === 'points') {
            $totalCost = $this->price * $quantity;
            return $user->points >= $totalCost;
        }

        // For cash payments, we'll assume they can pay (you might want to add wallet system)
        return true;
    }

    public function getPurchaseBlockReason(User $user, $quantity = 1, $paymentMethod = 'points')
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

        if (!in_array($paymentMethod, $this->available_payment_methods)) {
            return "This item doesn't accept {$paymentMethod} payments";
        }

        if ($paymentMethod === 'points') {
            $totalCost = $this->price * $quantity;
            if ($user->points < $totalCost) {
                return "Insufficient points. You need " . number_format($totalCost) . " points.";
            }
        }

        return null;
    }

    /**
     * Admin/Owner Permission Methods
     */
    public function canSetPointsPrice(User $user)
    {
        return $user->isAdmin();
    }

    public function canSetCashPrice(User $user)
    {
        return $user->isAdmin() || 
               ($user->isShopOwner() && $this->shop && $this->shop->isOwnedBy($user));
    }

    /**
     * Get default icon for items
     */
    public static function getDefaultIcon()
    {
        return 'pi pi-box';
    }
}