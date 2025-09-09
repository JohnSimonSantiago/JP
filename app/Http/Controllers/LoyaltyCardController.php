<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyCard;
use App\Models\LoyaltyCardProgress;
use App\Models\LoyaltyCardReward;
use App\Models\Shop;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoyaltyCardController extends Controller
{
    /**
     * Apply auth middleware to all methods
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Check if user has admin privileges
     */
    private function isAdmin($user)
    {
        return $user && $user->role === 'admin';
    }

    /**
     * Check if user can edit the shop
     */
    private function canEditShop($user, $shop)
    {
        if (!$user) {
            return false;
        }
        
        // Admin can edit any shop
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // Shop owner can edit their own shop
        if ($user->role === 'shop_owner' && $shop->owner_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Get shop's loyalty card
     */
    public function show(Shop $shop)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot access this shop'
                ], 403);
            }

            $loyaltyCard = $shop->loyaltyCard;

            return response()->json([
                'success' => true,
                'loyalty_card' => $loyaltyCard
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load loyalty card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create loyalty card for shop
     */
    public function store(Request $request, Shop $shop)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot edit this shop'
                ], 403);
            }

            // Check if shop already has a loyalty card
            if ($shop->loyaltyCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop already has a loyalty card'
                ], 400);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'required_purchases' => 'required|integer|min:1|max:100',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create loyalty card
            $loyaltyCard = LoyaltyCard::create([
                'shop_id' => $shop->id,
                'name' => $request->name,
                'description' => $request->description,
                'required_purchases' => $request->required_purchases,
                'is_active' => $request->get('is_active', true)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loyalty card created successfully',
                'loyalty_card' => $loyaltyCard
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create loyalty card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update loyalty card
     */
    public function update(Request $request, Shop $shop)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot edit this shop'
                ], 403);
            }

            $loyaltyCard = $shop->loyaltyCard;
            if (!$loyaltyCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loyalty card not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'required_purchases' => 'required|integer|min:1|max:100',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update loyalty card
            $loyaltyCard->update([
                'name' => $request->name,
                'description' => $request->description,
                'required_purchases' => $request->required_purchases,
                'is_active' => $request->get('is_active', true)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loyalty card updated successfully',
                'loyalty_card' => $loyaltyCard->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update loyalty card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle loyalty card active status
     */
    public function toggle(Shop $shop)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot edit this shop'
                ], 403);
            }

            $loyaltyCard = $shop->loyaltyCard;
            if (!$loyaltyCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loyalty card not found'
                ], 404);
            }

            // Toggle active status
            $loyaltyCard->update([
                'is_active' => !$loyaltyCard->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loyalty card status updated',
                'loyalty_card' => $loyaltyCard->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle loyalty card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all customer loyalty progress for a shop
     */
    public function getProgress(Shop $shop)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot access this shop'
                ], 403);
            }

            $loyaltyCard = $shop->loyaltyCard;
            if (!$loyaltyCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loyalty card not found'
                ], 404);
            }

            // Get customer progress with user details
            $progress = $loyaltyCard->progress()
                ->with('user:id,name,email')
                ->orderBy('current_purchases', 'desc')
                ->get();

            // Get pending rewards
            $rewards = $loyaltyCard->rewards()
                ->with(['user:id,name,email', 'shopItem:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'progress' => $progress,
                'rewards' => $rewards
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load loyalty progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually adjust customer loyalty count (add or remove)
     */
    public function adjustProgress(Request $request, Shop $shop, $userId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot edit this shop'
                ], 403);
            }

            $loyaltyCard = $shop->loyaltyCard;
            if (!$loyaltyCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loyalty card not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:add,remove'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ], 422);
            }

            $customer = User::findOrFail($userId);
            $action = $request->action;

            // Get or create progress record
            $progress = LoyaltyCardProgress::firstOrCreate([
                'loyalty_card_id' => $loyaltyCard->id,
                'user_id' => $customer->id
            ], [
                'current_purchases' => 0,
                'completed_cards' => 0
            ]);

            if ($action === 'add') {
                $progress->incrementPurchase();
                $message = 'Purchase count added successfully';
            } else { // remove
                if ($progress->current_purchases <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot remove from zero purchases'
                    ], 400);
                }

                $progress->decrement('current_purchases');
                $progress->update(['last_purchase_at' => now()]);
                
                // Recalculate completed cards
                $completedCards = floor($progress->current_purchases / $loyaltyCard->required_purchases);
                $progress->update(['completed_cards' => $completedCards]);
                
                $message = 'Purchase count removed successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'progress' => $progress->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust loyalty count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a loyalty reward claim
     */
    public function approveReward(Shop $shop, LoyaltyCardReward $reward)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot edit this shop'
                ], 403);
            }

            // Verify reward belongs to this shop
            if ($reward->loyaltyCard->shop_id !== $shop->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward does not belong to this shop'
                ], 403);
            }

            if ($reward->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward is not pending approval'
                ], 400);
            }

            // Approve the reward
            $reward->approve();

            return response()->json([
                'success' => true,
                'message' => 'Reward approved successfully',
                'reward' => $reward->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve reward',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a loyalty reward claim
     */
    public function rejectReward(Request $request, Shop $shop, LoyaltyCardReward $reward)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login'
                ], 401);
            }

            // Check authorization
            if (!$this->canEditShop($user, $shop)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You cannot edit this shop'
                ], 403);
            }

            // Verify reward belongs to this shop
            if ($reward->loyaltyCard->shop_id !== $shop->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward does not belong to this shop'
                ], 403);
            }

            if ($reward->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward is not pending approval'
                ], 400);
            }

            // Reject the reward
            $reason = $request->get('reason', 'No reason provided');
            $reward->reject($reason);

            return response()->json([
                'success' => true,
                'message' => 'Reward rejected successfully',
                'reward' => $reward->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject reward',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update loyalty progress when purchase is approved
     * This method is called from the purchase approval process
     */
    public static function updateLoyaltyOnPurchaseApproval(Purchase $purchase)
    {
        try {
            // Only process if purchase is for a shop (not point shop)
            if (!$purchase->shop_id) {
                return;
            }

            $shop = $purchase->shop;
            $loyaltyCard = $shop->loyaltyCard;

            // Only process if shop has an active loyalty card
            if (!$loyaltyCard || !$loyaltyCard->is_active) {
                return;
            }

            // Get or create progress record for this user
            $progress = LoyaltyCardProgress::firstOrCreate([
                'loyalty_card_id' => $loyaltyCard->id,
                'user_id' => $purchase->user_id
            ], [
                'current_purchases' => 0,
                'completed_cards' => 0
            ]);

            // Add the quantity of items purchased to loyalty count
            for ($i = 0; $i < $purchase->quantity; $i++) {
                $progress->incrementPurchase();
            }

            // Update the purchase record to link with loyalty card
            $purchase->update([
                'loyalty_card_id' => $loyaltyCard->id,
                'counts_for_loyalty' => true
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the purchase approval
            \Log::error('Failed to update loyalty progress: ' . $e->getMessage());
        }
    }
}