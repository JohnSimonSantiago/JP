<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'required_purchases',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'required_purchases' => 'integer'
    ];

    /**
     * Get the shop that owns this loyalty card
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get all progress records for this loyalty card
     */
    public function progress()
    {
        return $this->hasMany(LoyaltyCardProgress::class);
    }

    /**
     * Get all rewards for this loyalty card
     */
    public function rewards()
    {
        return $this->hasMany(LoyaltyCardReward::class);
    }

    /**
     * Get progress for a specific user
     */
    public function getProgressForUser($userId)
    {
        return $this->progress()->where('user_id', $userId)->first();
    }

    /**
     * Check if user can claim a reward
     */
    public function canUserClaimReward($userId)
    {
        $progress = $this->getProgressForUser($userId);
        if (!$progress) {
            return false;
        }

        return $progress->current_purchases >= $this->required_purchases;
    }

    /**
     * Get available rewards count for user
     */
    public function getAvailableRewardsCount($userId)
    {
        $progress = $this->getProgressForUser($userId);
        if (!$progress) {
            return 0;
        }

        $completedCards = floor($progress->current_purchases / $this->required_purchases);
        $claimedRewards = $this->rewards()->where('user_id', $userId)->whereIn('status', ['approved', 'claimed'])->count();
        
        return max(0, $completedCards - $claimedRewards);
    }
}