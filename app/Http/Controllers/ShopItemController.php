<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopItem;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopItemController extends Controller
{
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

        // Check if user can purchase this item
        $blockReason = $item->getPurchaseBlockReason($user, $quantity);
        if ($blockReason) {
            return response()->json([
                'success' => false,
                'message' => $blockReason
            ], 400);
        }

        $totalCost = $item->price * $quantity;

        try {
            DB::transaction(function () use ($user, $item, $quantity, $totalCost) {
                // Deduct points from user immediately
                $user->decrement('points', $totalCost);
                
                // Create purchase record with pending status
                Purchase::create([
                    'user_id' => $user->id,
                    'shop_item_id' => $item->id,
                    'shop_id' => $item->shop_id,
                    'price_paid' => $item->price,
                    'quantity' => $quantity,
                    'status' => 'pending'
                ]);

                // Note: Stock is NOT decremented until shop owner/admin approves
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase submitted! Your order is pending approval from the shop owner.',
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
                ->with(['shopItem', 'shop:id,name'])
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
     * Shop Owner: Create new shop item
     */
    public function store(Shop $shop, Request $request)
    {
        if (!$shop->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'properties' => 'nullable|array'
        ]);

        try {
            $data = $request->except('image');
            $data['shop_id'] = $shop->id;
            
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
     * Shop Owner: Update shop item
     */
    public function update(ShopItem $item, Request $request)
    {
        if (!$item->shop->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'integer|min:1',
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
     * Shop Owner: Delete shop item
     */
    public function destroy(ShopItem $item)
    {
        if (!$item->shop->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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
     * Shop Owner: Get all items for owned shop
     */
    public function shopItems(Shop $shop)
    {
        if (!$shop->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $items = $shop->items()
                ->withCount(['purchases' => function ($query) {
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
     * Shop Owner: Get pending purchases for shop
     */
    public function getPendingPurchases(Shop $shop)
    {
        if (!$shop->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $pendingPurchases = $shop->purchases()
                ->pending()
                ->with(['user:id,name,profile_image', 'shopItem:id,name'])
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
     * Shop Owner/Admin: Approve a purchase
     */
    public function approvePurchase(Purchase $purchase)
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->isAdmin() && !$purchase->shop->canBeEditedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$purchase->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This purchase has already been processed'
            ], 400);
        }

        try {
            $purchase->approve();

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
     * Shop Owner/Admin: Reject a purchase and refund points
     */
    public function rejectPurchase(Purchase $purchase, Request $request)
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->isAdmin() && !$purchase->shop->canBeEditedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        if (!$purchase->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This purchase has already been processed'
            ], 400);
        }

        try {
            $purchase->reject($request->reason);

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
     * Admin: Get all pending purchases from all shops
     */
    public function getAllPendingPurchases()
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $pendingPurchases = Purchase::pending()
                ->with(['user:id,name,profile_image', 'shopItem:id,name', 'shop:id,name'])
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
}   