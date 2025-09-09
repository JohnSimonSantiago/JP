<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description', 'logo', 'banner', 
        'is_active', 'is_verified', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    protected $appends = [  
        'logo_url',
        'banner_url',
        'total_items',
        'follower_count',
        'total_reviews',
        'average_rating'
    ];

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopItem::class);
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(ShopItem::class)->where('is_active', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ShopReview::class);
    }

    // IMPORTANT: Keep this as belongsToMany - your migration creates a pivot table
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_followers');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_active', true)->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    

    // Accessors (needed for frontend)
    public function getLogoUrlAttribute()
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? Storage::url($this->banner) : null;
    }


    public function getTotalItemsAttribute()
    {
        return $this->activeItems()->count();
    }

    public function getFollowerCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    // Authorization Methods
    public function canBeViewedBy($user = null): bool
    {
        // Public shops can be viewed by anyone
        if ($this->is_active && $this->is_verified) {
            return true;
        }

        // Non-public shops can only be viewed by owner or admin
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $this->owner_id === $user->id;
    }

    public function canBeEditedBy($user = null): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can edit any shop, owner can edit their own shop
        return $user->isAdmin() || $this->owner_id === $user->id;
    }

    // Helper Methods
    public function isOwnedBy($user): bool
    {
        return $this->owner_id === $user->id;
    }

    // IMPORTANT: Keep these methods - ShopController uses them
    public function toggleFollow($user)
    {
        if ($this->followers()->where('user_id', $user->id)->exists()) {
            $this->followers()->detach($user->id);
            return false; // Unfollowed
        } else {
            $this->followers()->attach($user->id);
            return true; // Followed
        }
    }

    public function isFollowedBy($user)
    {
        return $this->followers()->where('user_id', $user->id)->exists();
    }

    public function getStatistics(): array
    {
        return [
            'total_items' => $this->items()->count(),
            'active_items' => $this->activeItems()->count(),
            'total_orders' => $this->purchases()->count(),
            'pending_orders' => $this->purchases()->where('status', 'pending')->count(),
            'completed_orders' => $this->purchases()->where('status', 'completed')->count(),
            'total_revenue' => $this->purchases()->where('status', 'completed')->sum('price_paid'),
            'followers_count' => $this->followers()->count(),
            'reviews_count' => $this->reviews()->count(),
            'average_rating' => $this->reviews()->avg('rating') ?: 0,
        ];
    }
    public function loyaltyCard()
{
    return $this->hasOne(LoyaltyCard::class);
}

public function hasLoyaltyCard()
{
    return $this->loyaltyCard()->exists();
}
}