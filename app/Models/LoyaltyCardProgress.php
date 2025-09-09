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
    /**
 * Increment purchase count and create pending rewards when cards are completed
 */
public function incrementPurchase()
{
    $oldCompletedCards = $this->completed_cards;
    
    // Increment purchase count
    $this->increment('current_purchases');
    $this->update(['last_purchase_at' => now()]);

    // Calculate new completed cards
    $newCompletedCards = floor($this->current_purchases / $this->loyaltyCard->required_purchases);
    
    // If completed cards increased, create pending reward records
    if ($newCompletedCards > $oldCompletedCards) {
        $this->update(['completed_cards' => $newCompletedCards]);
        
        // Create pending reward records for each newly completed card
        for ($cardNumber = $oldCompletedCards + 1; $cardNumber <= $newCompletedCards; $cardNumber++) {
            \App\Models\LoyaltyCardReward::create([
                'loyalty_card_id' => $this->loyalty_card_id,
                'user_id' => $this->user_id,
                'shop_item_id' => null, // Shop owner can specify which item later
                'card_completion_number' => $cardNumber,
                'status' => 'pending', // Needs shop owner approval
                'approved_at' => null
            ]);
        }
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