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
    public function store(Request $request)
    {
        try {
            // Get shop ID from route
            $shopId = $request->route('shop');
            
            // Handle if it's an object (extract ID)
            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }
            
            // Find the shop
            $shop = Shop::findOrFail($shopId);
            
            // Check authorization
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
                'unlimited_stock' => 'boolean',
                'image' => 'nullable|image|max:2048',
                'properties' => 'nullable|array',
                'is_active' => 'boolean'
            ]);

            // Prepare data
            $data = $request->except(['image', 'unlimited_stock']);
            $data['shop_id'] = $shop->id;
            $data['is_active'] = $request->get('is_active', true);
            
            // Handle stock based on unlimited_stock flag
            $unlimitedStock = filter_var($request->get('unlimited_stock', false), FILTER_VALIDATE_BOOLEAN);
            
            if ($unlimitedStock) {
                $data['stock'] = null; // Unlimited stock
            } else {
                // Limited stock - use provided value or default to 0
                $data['stock'] = (int) $request->get('stock', 0);
            }
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('shop-items', 'public');
                $data['image'] = $imagePath;
            }

            // Create the item
            $item = ShopItem::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'item' => $item->fresh() // Return fresh instance with all data
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => $e->getMessage(),
                'debug' => [
                    'shop_route' => $request->route('shop'),
                    'error_line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Shop Owner: Update shop item
     */
    public function update(Request $request)
    {
        try {
            // Get route parameters manually
            $shopId = $request->route('shop');
            $itemId = $request->route('shopItem');
            
            // Handle if they're objects (extract ID)
            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }
            if (is_object($itemId)) {
                $itemId = $itemId->id;
            }
            
            // Find the shop first
            $shop = Shop::findOrFail($shopId);
            
            // Find the item that belongs to this shop
            $item = $shop->items()->findOrFail($itemId);
            
            // Check authorization
            if (!$shop->canBeEditedBy(Auth::user())) {
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
                'unlimited_stock' => 'boolean',
                'is_active' => 'boolean',
                'image' => 'nullable|image|max:2048',
                'properties' => 'nullable|array'
            ]);

            // Prepare update data
            $data = $request->except(['image', '_method', 'unlimited_stock']);
            
            // Handle stock based on unlimited_stock flag if provided
            if ($request->has('unlimited_stock')) {
                $unlimitedStock = filter_var($request->get('unlimited_stock'), FILTER_VALIDATE_BOOLEAN);
                
                if ($unlimitedStock) {
                    $data['stock'] = null; // Unlimited stock
                } else {
                    // Limited stock - use provided value or keep current
                    if ($request->has('stock')) {
                        $data['stock'] = (int) $request->get('stock', 0);
                    } else {
                        // If switching from unlimited to limited but no stock specified, set to 0
                        $data['stock'] = $item->stock === null ? 0 : $item->stock;
                    }
                }
            }
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                
                $imagePath = $request->file('image')->store('shop-items', 'public');
                $data['image'] = $imagePath;
            }

            // Update the item
            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item->fresh() // Return fresh instance
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
    public function destroy(Request $request)
    {
        try {
            // Get route parameters manually
            $shopId = $request->route('shop');
            $itemId = $request->route('shopItem');
            
            // Handle if they're objects (extract ID)
            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }
            if (is_object($itemId)) {
                $itemId = $itemId->id;
            }
            
            // Find the shop first
            $shop = Shop::findOrFail($shopId);
            
            // Find the item that belongs to this shop
            $item = $shop->items()->findOrFail($itemId);
            
            // Check authorization
            if (!$shop->canBeEditedBy(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Delete image if exists
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            // Delete the item
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item',
                'error' => $e->getMessage(),
                'debug' => [
                    'shop_route' => $request->route('shop'),
                    'item_route' => $request->route('shopItem'),
                    'error_line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Shop Owner: Get all items for owned shop
     */
    public function shopItems(Request $request)
    {
        try {
            // Get shop ID from route
            $shopId = $request->route('shop');
            
            // Handle if it's an object (extract ID)
            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }
            
            // Find the shop
            $shop = Shop::findOrFail($shopId);
            
            // Check authorization
            if (!$shop->canBeEditedBy(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Get items for this shop with additional stock information
            $items = $shop->items()
                ->withCount(['purchases' => function ($query) {
                    $query->where('status', 'completed');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    // Add computed stock status
                    $item->stock_status = $this->getItemStockStatus($item);
                    $item->is_unlimited_stock = $item->stock === null;
                    return $item;
                });

            return response()->json([
                'success' => true,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load items',
                'error' => $e->getMessage(),
                'debug' => [
                    'shop_route' => $request->route('shop'),
                    'error_line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Helper method to get item stock status
     */
    private function getItemStockStatus($item)
    {
        if ($item->stock === null) {
            return 'unlimited';
        } elseif ($item->stock === 0) {
            return 'out_of_stock';
        } elseif ($item->stock <= 5) {
            return 'low_stock';
        } else {
            return 'in_stock';
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
                ->with(['user:id,name,profile_image', 'shopItem:id,name,stock'])
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
     * Shop Owner/Admin: Approve a purchase - WITH STOCK DEDUCTION
     */
    public function approvePurchase($shopId, $purchaseId)
    {
        try {
            // Manually find the shop and purchase
            $shop = Shop::findOrFail($shopId);
            $purchase = $shop->purchases()->findOrFail($purchaseId);
            
            $user = Auth::user();
            
            // Check authorization
            if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($purchase->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This purchase has already been processed'
                ], 400);
            }

            DB::transaction(function () use ($purchase) {
                // Get the shop item
                $shopItem = $purchase->shopItem;
                
                // Deduct stock ONLY if the item has limited stock (not null)
                if ($shopItem->stock !== null) {
                    // Check if there's enough stock
                    if ($shopItem->stock < $purchase->quantity) {
                        throw new \Exception('Insufficient stock available. Current stock: ' . $shopItem->stock . ', requested: ' . $purchase->quantity);
                    }
                    
                    // Deduct the stock
                    $shopItem->decrement('stock', $purchase->quantity);
                }
                
                // Update purchase status
                $purchase->update(['status' => 'completed']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase approved successfully and stock updated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Shop Owner/Admin: Reject a purchase
     */
    public function rejectPurchase($shopId, $purchaseId, Request $request)
    {
        try {
            // Manually find the shop and purchase
            $shop = Shop::findOrFail($shopId);
            $purchase = $shop->purchases()->findOrFail($purchaseId);
            
            $user = Auth::user();
            
            // Check authorization
            if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($purchase->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This purchase has already been processed'
                ], 400);
            }

            DB::transaction(function () use ($purchase, $request) {
                // Refund points to user
                $customer = $purchase->user;
                $refundAmount = $purchase->price_paid * $purchase->quantity;
                $customer->increment('points', $refundAmount);

                // Update purchase status
                $updateData = ['status' => 'rejected'];
                if ($request->has('reason')) {
                    $updateData['rejection_reason'] = $request->input('reason');
                }
                
                $purchase->update($updateData);
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase rejected and points refunded successfully'
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
                ->with(['user:id,name,profile_image', 'shopItem:id,name,stock', 'shop:id,name'])
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