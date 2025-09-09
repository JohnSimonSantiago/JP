<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCardReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_card_id',
        'user_id',
        'shop_item_id',
        'card_completion_number',
        'status',
        'rejection_reason',
        'claimed_at',
        'approved_at'
    ];

    protected $casts = [
        'card_completion_number' => 'integer',
        'claimed_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the loyalty card this reward belongs to
     */
    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }

    /**
     * Get the user this reward belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop item that was claimed
     */
    public function shopItem()
    {
        return $this->belongsTo(ShopItem::class);
    }

    /**
     * Mark reward as claimed
     */
    public function markAsClaimed()
    {
        $this->update([
            'status' => 'claimed',
            'claimed_at' => now()
        ]);
    }

    /**
     * Approve the reward
     */
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);
    }

    /**
     * Reject the reward
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Check if reward is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if reward is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if reward is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if reward is claimed
     */
    public function isClaimed()
    {
        return $this->status === 'claimed';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'claimed' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get formatted status text
     */
    public function getStatusText()
    {
        return match($this->status) {
            'pending' => 'Waiting for Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'claimed' => 'Claimed',
            default => 'Unknown'
        };
    }

    /**
     * Scope: Only pending rewards
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Only approved rewards
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Only rejected rewards
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Only claimed rewards
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Scope: For a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: For a specific shop
     */
    public function scopeForShop($query, $shopId)
    {
        return $query->whereHas('loyaltyCard', function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        });
    }
}