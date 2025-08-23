<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ShopItem;
use App\Models\Shop;

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
}