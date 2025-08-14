<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'logo',
        'banner',
        'is_active',
        'is_verified',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'settings' => 'array'
    ];

    protected $appends = [
        'logo_url',
        'banner_url',
        'total_items',
        'average_rating',
        'total_reviews',
        'follower_count'
    ];

    /**
     * Relationships
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items()
    {
        return $this->hasMany(ShopItem::class);
    }

    public function activeItems()
    {
        return $this->hasMany(ShopItem::class)->where('is_active', true);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function reviews()
    {
        return $this->hasMany(ShopReview::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'shop_followers');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePublic($query)
    {
        return $query->active()->verified();
    }

    /**
     * Accessors
     */
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

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getFollowerCountAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Methods
     */
    public function isOwnedBy(User $user)
    {
        return $this->owner_id === $user->id;
    }

    public function canBeViewedBy(?User $user = null)
    {
        // Shop owners and admins can always view
        if ($user && ($this->isOwnedBy($user) || $user->isAdmin())) {
            return true;
        }

        // Public users can only view active and verified shops
        return $this->is_active && $this->is_verified;
    }

    public function canBeEditedBy(?User $user = null)
    {
        if (!$user) {
            return false;
        }

        return $this->isOwnedBy($user) || $user->isAdmin();
    }

    public function toggleFollow(User $user)
    {
        if ($this->followers()->where('user_id', $user->id)->exists()) {
            $this->followers()->detach($user->id);
            return false; // Unfollowed
        } else {
            $this->followers()->attach($user->id);
            return true; // Followed
        }
    }

    public function isFollowedBy(User $user)
    {
        return $this->followers()->where('user_id', $user->id)->exists();
    }

    /**
     * Get shop statistics for dashboard
     */
    public function getStatistics()
    {
        return [
            'total_items' => $this->items()->count(),
            'active_items' => $this->activeItems()->count(),
            'total_sales' => $this->purchases()->where('status', 'completed')->sum('quantity'),
            'pending_orders' => $this->purchases()->where('status', 'pending')->count(),
            'total_revenue' => $this->purchases()->where('status', 'completed')->sum(\DB::raw('price_paid * quantity')),
            'average_rating' => round($this->average_rating, 1),
            'total_reviews' => $this->total_reviews,
            'followers' => $this->follower_count
        ];
    }
}