<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',     
        'role',
        'level',
        'points',
        'stars',
        'is_premium',
        'profile_image',
        'gender',          // NEW
        'birthday',        // NEW
        'address',         // NEW
        'privacy_settings', // NEW
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',           // NEW: Cast birthday as date
        'is_premium' => 'boolean',
        'privacy_settings' => 'array',  // NEW: Cast privacy settings as array
    ];

    /**
     * Relationships
     */
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class, 'sender_id');
    }

    public function receivedTrades()
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'creator_id');
    }

    /**
     * MISSING SHOP-RELATED RELATIONSHIPS
     */
    public function shops()
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    // This is the key missing relationship that ShopController uses
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
     * Accessor for calculating age from birthday
     */
    public function getAgeAttribute()
    {
        if (!$this->birthday) {
            return null;
        }
        
        return \Carbon\Carbon::parse($this->birthday)->age;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is shop owner
     */
    public function isShopOwner()
    {
        return $this->role === 'shop_owner';
    }

    /**
     * MISSING SHOP-RELATED METHODS
     */
    
    // This is the key missing method that ShopController calls
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
     * Points management methods
     */
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

    public function addPoints($amount, $reason = null)
    {
        $this->increment('points', $amount);
    }

    public function deductPoints($amount, $reason = null)
    {
        if ($this->points >= $amount) {
            $this->decrement('points', $amount);
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

    /**
     * Get formatted gender
     */
    public function getFormattedGenderAttribute()
    {
        if (!$this->gender) {
            return null;
        }

        return match($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            default => ucfirst($this->gender)
        };
    }

    /**
     * Get default privacy settings
     */
    public function getDefaultPrivacySettings()
    {
        return [
            'gender' => 'public',     // public, private
            'birthday' => 'public',   // public, private
            'address' => 'private',   // public, private (default private for security)
        ];
    }

    /**
     * Get privacy settings with defaults
     */
    public function getPrivacySettingsAttribute($value)
    {
        $settings = $value ? json_decode($value, true) : [];
        return array_merge($this->getDefaultPrivacySettings(), $settings);
    }

    /**
     * Check if a field is private for other users
     */
    public function isFieldPrivate($field, $forUser = null)
    {
        // Owner can always see their own fields
        if ($forUser && $forUser->id === $this->id) {
            return false;
        }

        $privacySettings = $this->privacy_settings;
        return ($privacySettings[$field] ?? 'public') === 'private';
    }

    /**
     * Get public profile data (respecting privacy settings)
     */
    public function getPublicProfileData($forUser = null)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'stars' => $this->stars,
            'points' => $this->points,
            'is_premium' => $this->is_premium,
            'profile_image' => $this->profile_image,
            'role' => $this->role,
            'member_since' => $this->created_at,
        ];

        // Add fields based on privacy settings
        if (!$this->isFieldPrivate('gender', $forUser)) {
            $data['gender'] = $this->gender;
        }
        
        if (!$this->isFieldPrivate('birthday', $forUser)) {
            $data['birthday'] = $this->birthday;
            $data['age'] = $this->age;
        }
        
        if (!$this->isFieldPrivate('address', $forUser)) {
            $data['address'] = $this->address;
        }

        return $data;
    }
}