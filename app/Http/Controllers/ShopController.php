<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * Display shop items
     */
    public function index(Request $request)
    {
        try {
            $category = $request->get('category');
            
            $query = ShopItem::active()->inStock()->orderBy('created_at', 'desc');
            
            if ($category) {
                $query->byCategory($category);
            }
            
            $items = $query->get();
            
            return response()->json([
                'success' => true,
                'items' => $items,
                'user' => Auth::user()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shop items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase an item
     */
    public function purchase(Request $request, ShopItem $item)
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10'
        ]);

        $user = Auth::user();
        $quantity = $request->get('quantity', 1);

        // Check if item is active and in stock
        if (!$item->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This item is no longer available'
            ], 400);
        }

        if (!$item->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => 'This item is out of stock'
            ], 400);
        }

        if ($item->stock !== null && $item->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Only {$item->stock} items available"
            ], 400);
        }

        $totalCost = $item->price * $quantity;

        // Check if user has enough points
        if ($user->points < $totalCost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points. You need ' . number_format($totalCost) . ' points.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $item, $quantity, $totalCost) {
                // Deduct points from user immediately
                $user->decrement('points', $totalCost);
                
                // Create purchase record with pending status
                Purchase::create([
                    'user_id' => $user->id,
                    'shop_item_id' => $item->id,
                    'price_paid' => $item->price,
                    'quantity' => $quantity,
                    'status' => 'pending'
                ]);

                // Note: Stock is NOT decremented until admin approves
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase submitted! Your order is pending admin approval.',
                'new_balance' => $user->fresh()->points
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's purchase history
     */
    public function getPurchases()
    {
        try {
            $purchases = Purchase::forUser(Auth::id())
                ->with('shopItem')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'purchases' => $purchases
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchase history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Create new shop item
     */
    public function store(Request $request)
    {
        $this->checkAdminAccess();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:1',
            'category' => 'required|in:cosmetic,boost,premium,special',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'properties' => 'nullable|array'
        ]);

        try {
            $data = $request->except('image');
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('shop-items', 'public');
                $data['image'] = $imagePath;
            }

            $item = ShopItem::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'item' => $item
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Update shop item
     */
    public function update(Request $request, ShopItem $item)
    {
        $this->checkAdminAccess();

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'integer|min:1',
            'category' => 'in:cosmetic,boost,premium,special',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'properties' => 'nullable|array'
        ]);

        try {
            $data = $request->except('image');
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                
                $imagePath = $request->file('image')->store('shop-items', 'public');
                $data['image'] = $imagePath;
            }

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Delete shop item
     */
    public function destroy(ShopItem $item)
    {
        $this->checkAdminAccess();

        try {
            // Delete image if exists
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all items (including inactive)
     */
    public function adminIndex()
    {
        $this->checkAdminAccess();

        try {
            $items = ShopItem::with(['purchases' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get pending purchases for approval
     */
    public function getPendingPurchases()
    {
        $this->checkAdminAccess();

        try {
            $pendingPurchases = Purchase::where('status', 'pending')
                ->with(['user', 'shopItem'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'purchases' => $pendingPurchases
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load pending purchases',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Approve a purchase
     */
    public function approvePurchase(Purchase $purchase)
    {
        $this->checkAdminAccess();

        if ($purchase->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This purchase has already been processed'
            ], 400);
        }

        try {
            DB::transaction(function () use ($purchase) {
                $item = $purchase->shopItem;
                
                // Check if item still has stock (if limited)
                if ($item->stock !== null && $item->stock < $purchase->quantity) {
                    throw new \Exception("Insufficient stock. Only {$item->stock} items remaining.");
                }
                
                // Decrement stock if limited
                $item->decrementStock($purchase->quantity);
                
                // Update purchase status
                $purchase->update(['status' => 'completed']);
                
                // Apply item effects (if any)
                $this->applyItemEffects($purchase->user, $item, $purchase->quantity);
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Reject a purchase and refund points
     */
    public function rejectPurchase(Purchase $purchase, Request $request)
    {
        $this->checkAdminAccess();

        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        if ($purchase->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This purchase has already been processed'
            ], 400);
        }

        try {
            DB::transaction(function () use ($purchase, $request) {
                // Refund points to user
                $refundAmount = $purchase->price_paid * $purchase->quantity;
                $purchase->user->increment('points', $refundAmount);
                
                // Update purchase status
                $purchase->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->reason
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase rejected and points refunded'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply special item effects
     */
    private function applyItemEffects(User $user, ShopItem $item, int $quantity)
    {
        if (!$item->properties) {
            return;
        }

        switch ($item->category) {
            case 'boost':
                if (isset($item->properties['point_boost'])) {
                    $pointBoost = $item->properties['point_boost'] * $quantity;
                    $user->increment('points', $pointBoost);
                }
                break;
                
            case 'premium':
                if (isset($item->properties['premium_days'])) {
                    $user->update(['is_premium' => true]);
                    // You might want to add premium_expires_at field to track duration
                }
                break;
        }
    }
}