<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCardProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_card_id',
        'user_id',
        'current_purchases',
        'completed_cards',
        'last_purchase_at'
    ];

    protected $casts = [
        'current_purchases' => 'integer',
        'completed_cards' => 'integer',
        'last_purchase_at' => 'datetime'
    ];

    /**
     * Get the loyalty card this progress belongs to
     */
    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }

    /**
     * Get the user this progress belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment purchase count
     */
    public function incrementPurchase()
    {
        $this->increment('current_purchases');
        $this->update(['last_purchase_at' => now()]);

        // Check if user completed a card
        if ($this->current_purchases >= $this->loyaltyCard->required_purchases) {
            $completedCards = floor($this->current_purchases / $this->loyaltyCard->required_purchases);
            $this->update(['completed_cards' => $completedCards]);
        }
    }

    /**
     * Decrement purchase count (for manual adjustments)
     */
    public function decrementPurchase()
    {
        if ($this->current_purchases > 0) {
            $this->decrement('current_purchases');
            $this->update(['last_purchase_at' => now()]);
            
            // Recalculate completed cards
            $completedCards = floor($this->current_purchases / $this->loyaltyCard->required_purchases);
            $this->update(['completed_cards' => $completedCards]);
        }
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        $currentInCard = $this->current_purchases % $this->loyaltyCard->required_purchases;
        return ($currentInCard / $this->loyaltyCard->required_purchases) * 100;
    }

    /**
     * Get current card purchases (within current card cycle)
     */
    public function getCurrentCardPurchases()
    {
        return $this->current_purchases % $this->loyaltyCard->required_purchases;
    }

    /**
     * Get how many more purchases needed for next reward
     */
    public function getPurchasesNeededForReward()
    {
        $currentInCard = $this->getCurrentCardPurchases();
        return $this->loyaltyCard->required_purchases - $currentInCard;
    }

    /**
     * Check if user can claim a reward right now
     */
    public function canClaimReward()
    {
        return $this->current_purchases >= $this->loyaltyCard->required_purchases;
    }
}