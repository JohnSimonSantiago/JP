<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ShopItem;
use App\Models\Shop;
use App\Models\Purchase;
use App\Models\User;

class AdminPricingController extends Controller
{
    /**
     * Admin: Get all shop items for point pricing management
     */
    public function getAllShopItems()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            $items = ShopItem::with(['shop:id,name,owner_id', 'shop.owner:id,name'])
                ->select([
                    'id', 'shop_id', 'name', 'description', 'price', 'cash_price', 
                    'image', 'is_active', 'is_active_in_point_shop', 'stock', 'created_at'
                ])
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
     * Admin: Get all shops for filter dropdown
     */
     public function getAllShops()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            $shops = Shop::with('owner:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'owner_id']);
            
            return response()->json([
                'success' => true,
                'shops' => $shops
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shops',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Update single item point price
     */
    public function updateItemPrice(Request $request, $itemId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'price' => 'required|integer|min:0'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $item = ShopItem::with('shop')->findOrFail($itemId);
            
            $item->update([
                'price' => $request->price
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Point price updated successfully',
                'item' => $item->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update price',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Admin: Bulk update item point prices
     */
    public function bulkUpdatePrices(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:shop_items,id',
            'price' => 'required|integer|min:0'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $updatedCount = ShopItem::whereIn('id', $request->item_ids)
                ->update(['price' => $request->price]);
            
            return response()->json([
                'success' => true,
                'message' => "Updated point prices for {$updatedCount} items",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

      public function getPointShopStatistics()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            $statistics = [
                'total_point_items' => ShopItem::where('price', '>', 0)->count(),
                'active_point_shop_items' => ShopItem::where('is_active_in_point_shop', true)
                    ->where('is_active', true)
                    ->where('price', '>', 0)
                    ->count(),
                'pending_point_orders' => Purchase::where('currency_type', 'points')
                    ->where('status', 'pending')
                    ->count(),
                'total_point_revenue' => Purchase::where('currency_type', 'points')
                    ->where('status', 'completed')
                    ->sum(DB::raw('price_paid * quantity')),
                'monthly_point_orders' => Purchase::where('currency_type', 'points')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                'monthly_point_revenue' => Purchase::where('currency_type', 'points')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->sum(DB::raw('price_paid * quantity'))
            ];
            
            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Admin: Update single item point shop status
     */
     public function updatePointShopStatus(Request $request, $itemId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'is_active_in_point_shop' => 'required|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $item = ShopItem::with('shop')->findOrFail($itemId);
            
            $item->update([
                'is_active_in_point_shop' => $request->is_active_in_point_shop
            ]);
            
            $status = $request->is_active_in_point_shop ? 'added to' : 'removed from';
            $message = "Item \"{$item->name}\" has been {$status} the point shop";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active_in_point_shop' => $item->is_active_in_point_shop,
                'item' => $item->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update point shop status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Bulk update point shop status
     */
    public function bulkUpdatePointShopStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:shop_items,id',
            'is_active_in_point_shop' => 'required|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $updatedCount = ShopItem::whereIn('id', $request->item_ids)
                ->update(['is_active_in_point_shop' => $request->is_active_in_point_shop]);
            
            $status = $request->is_active_in_point_shop ? 'added to' : 'removed from';
            $message = "{$updatedCount} items have been {$status} the point shop";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update point shop status',
                'error' => $e->getMessage()
            ], 500);
        }
    }


     public function getPointOrders()
{
    $user = Auth::user();
    
    if (!$user->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    try {
        // Get all orders paid with points, with relationships
        $baseQuery = Purchase::with([
                'user:id,name,email',
                'shopItem:id,name,description,image',
                'shop:id,name'
            ])
            ->where('currency_type', 'points')
            ->orderBy('created_at', 'desc');

        // Get pending orders
        $pendingOrders = (clone $baseQuery)
            ->where('status', 'pending')
            ->get();

        // Get completed orders (limit to recent 50)
        $completedOrders = (clone $baseQuery)
            ->where('status', 'completed')
            ->limit(50)
            ->get();

        // Get rejected orders (limit to recent 50)
        $rejectedOrders = (clone $baseQuery)
            ->where('status', 'rejected')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'rejected_orders' => $rejectedOrders
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load point orders',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Admin: Approve a point-based order
 */
public function approvePointOrder(Request $request, $orderId)
{
    $user = Auth::user();
    
    if (!$user->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    try {
        DB::beginTransaction();

        $order = Purchase::with(['user', 'shopItem', 'shop'])
            ->where('currency_type', 'points')
            ->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not pending'
            ], 400);
        }

        // Update order status
        $order->update(['status' => 'completed']);

        // Update stock if applicable
        if ($order->shopItem->stock !== null) {
            $order->shopItem->decrement('stock', $order->quantity);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order approved successfully',
            'order' => $order->fresh(['user', 'shopItem', 'shop'])
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to approve order',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Admin: Reject a point-based order
 */
public function rejectPointOrder(Request $request, $orderId)
{
    $user = Auth::user();
    
    if (!$user->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'reason' => 'nullable|string|max:500'
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }
    
    try {
        DB::beginTransaction();

        $order = Purchase::with(['user', 'shopItem', 'shop'])
            ->where('currency_type', 'points')
            ->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not pending'
            ], 400);
        }
         
        $order->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        // Refund points to user
        $totalRefund = $order->price_paid * $order->quantity;
        $order->user->increment('points', $totalRefund);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order rejected successfully and points refunded',
            'order' => $order->fresh(['user', 'shopItem', 'shop']),
            'refunded_points' => $totalRefund
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to reject order',
            'error' => $e->getMessage()
        ], 500);
    }
}
}