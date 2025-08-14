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
        'shop_id',
        'price_paid',
        'quantity',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'price_paid' => 'integer',
        'quantity' => 'integer'
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

    /**
     * Accessors
     */
    public function getTotalAmountAttribute()
    {
        return $this->price_paid * $this->quantity;
    }

    /**
     * Status checking methods
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Methods
     */
    public function canBeApproved()
    {
        return $this->isPending() && $this->shopItem && $this->shopItem->isInStock($this->quantity);
    }

    public function canBeRejected()
    {
        return $this->isPending();
    }

    public function approve()
    {
        if (!$this->canBeApproved()) {
            throw new \Exception('Purchase cannot be approved');
        }

        \DB::transaction(function () {
            // Decrement stock if limited
            $this->shopItem->decrementStock($this->quantity);
            
            // Update status
            $this->update(['status' => 'completed']);
            
            // Apply any item effects (if applicable)
            $this->applyItemEffects();
        });
    }

    public function reject($reason = null)
    {
        if (!$this->canBeRejected()) {
            throw new \Exception('Purchase cannot be rejected');
        }

        \DB::transaction(function () use ($reason) {
            // Refund points to user
            $this->user->increment('points', $this->total_amount);
            
            // Update status
            $this->update([
                'status' => 'rejected',
                'rejection_reason' => $reason
            ]);
        });
    }

    /**
     * Apply special item effects
     */
    private function applyItemEffects()
    {
        $item = $this->shopItem;
        
        if (!$item || !$item->properties) {
            return;
        }

        switch ($item->category) {
            case 'boost':
                if (isset($item->properties['point_boost'])) {
                    $pointBoost = $item->properties['point_boost'] * $this->quantity;
                    $this->user->increment('points', $pointBoost);
                }
                break;
                
            case 'premium':
                if (isset($item->properties['premium_days'])) {
                    $this->user->update(['is_premium' => true]);
                    // You might want to add premium_expires_at field
                }
                break;
        }
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status icon for UI
     */
    public function getStatusIcon()
    {
        return match($this->status) {
            'pending' => 'pi pi-clock',
            'completed' => 'pi pi-check-circle',
            'rejected' => 'pi pi-times-circle',
            default => 'pi pi-question-circle'
        };
    }
}