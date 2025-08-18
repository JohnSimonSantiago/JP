<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Keep this if you're using Sanctum for API auth

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email', // Add this if you need email authentication
        'password',
        'level',
        'points',
         'stars',
        'is_premium',
        'role',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_premium' => 'boolean',
        'points' => 'integer',
        'stars' => 'integer',  // Add this for consistency
        'level' => 'integer', // Add this for consistency
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Existing relationships (Trading/Betting system)
     */
    public function sentTrades()
    {
        return $this->hasMany(Trade::class, 'sender_id');
    }

    public function receivedTrades()
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }

    public function createdBets()
    {
        return $this->hasMany(Bet::class, 'creator_id');
    }

    public function opponentBets()
    {
        return $this->hasMany(Bet::class, 'opponent_id');
    }

    public function refereeBets()
    {
        return $this->hasMany(Bet::class, 'referee_id');
    }

    public function wonBets()
    {
        return $this->hasMany(Bet::class, 'winner_id');
    }

    /**
     * Shop-related relationships
     */
    public function ownedShop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }

    public function followedShops()
    {
        return $this->belongsToMany(Shop::class, 'shop_followers');
    }

    public function shopReviews()
    {
        return $this->hasMany(ShopReview::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Existing helper methods (keep your current logic)
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function canAfford($amount)
    {
        return $this->points >= $amount;
    }

    public function spendPoints($amount)
    {
        if (!$this->canAfford($amount)) {
            throw new \Exception('Insufficient points');
        }
        
        $this->decrement('points', $amount);
        return $this;
    }

    public function earnPoints($amount)
    {
        $this->increment('points', $amount);
        return $this;
    }

    /**
     * New role checking methods for shop system
     */
    public function isShopOwner()
    {
        return $this->role === 'shop_owner';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Shop owner specific methods
     */
    public function hasShop()
    {
        return $this->isShopOwner() && $this->ownedShop()->exists();
    }

    public function canCreateShop()
    {
        return $this->isShopOwner() && !$this->hasShop();
    }

    public function canManageShop(Shop $shop)
    {
        return $this->isAdmin() || ($this->isShopOwner() && $shop->isOwnedBy($this));
    }

    /**
     * Following shops methods
     */
    public function followShop(Shop $shop)
    {
        if (!$this->followedShops()->where('shop_id', $shop->id)->exists()) {
            $this->followedShops()->attach($shop->id);
            return true;
        }
        return false;
    }

    public function unfollowShop(Shop $shop)
    {
        if ($this->followedShops()->where('shop_id', $shop->id)->exists()) {
            $this->followedShops()->detach($shop->id);
            return true;
        }
        return false;
    }

    public function isFollowing(Shop $shop)
    {
        return $this->followedShops()->where('shop_id', $shop->id)->exists();
    }

    /**
     * Review methods
     */
    public function hasReviewedShop(Shop $shop)
    {
        return $this->shopReviews()->where('shop_id', $shop->id)->exists();
    }

    public function canReviewShop(Shop $shop)
    {
        // Users can review shops if they haven't already and have made a purchase
        return !$this->hasReviewedShop($shop) && 
               $this->purchases()->whereHas('shopItem', function($q) use ($shop) {
                   $q->where('shop_id', $shop->id);
               })->where('status', 'completed')->exists();
    }

    /**
     * Enhanced points management (builds on your existing methods)
     */
    public function addPoints($amount, $reason = null)
    {
        $this->increment('points', $amount);
        
        // You could add point logging here if needed
        // PointsLog::create([
        //     'user_id' => $this->id,
        //     'amount' => $amount,
        //     'type' => 'earned',
        //     'reason' => $reason
        // ]);
    }

    public function deductPoints($amount, $reason = null)
    {
        if ($this->points >= $amount) {
            $this->decrement('points', $amount);
            
            // You could add point logging here if needed
            // PointsLog::create([
            //     'user_id' => $this->id,
            //     'amount' => -$amount,
            //     'type' => 'spent',
            //     'reason' => $reason
            // ]);
            
            return true;
        }
        return false;
    }

    /**
     * Get user's shop statistics (for shop owners)
     */
    public function getShopStatistics()
    {
        if (!$this->hasShop()) {
            return null;
        }

        return $this->ownedShop->getStatistics();
    }

    /**
     * Get profile image URL
     */
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ? \Storage::url($this->profile_image) : null;
    }

    // Add these methods to your User.php model

// Add these methods to your User.php model if they're missing


}