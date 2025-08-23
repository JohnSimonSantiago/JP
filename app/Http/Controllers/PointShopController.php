<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PointShopController extends Controller
{
    /**
     * Display point shop items (public access)
     */
    public function index(Request $request)
    {
        try {
            $perPage = min($request->get('per_page', 20), 100);
            $search = $request->get('search');
            $sort = $request->get('sort', 'name');

            // Build query for point shop items
            $query = ShopItem::where('is_active', true)
                ->where('is_active_in_point_shop', true)
                ->where('price', '>', 0) // Only items with point prices
                ->select([
                    'id', 'name', 'description', 'price', 'stock', 
                    'is_active', 'is_active_in_point_shop', 'image', 'created_at'
                ]);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            switch ($sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    // Sort by number of completed purchases
                    $query->withCount(['purchases' => function ($q) {
                        $q->where('status', 'completed');
                    }])->orderBy('purchases_count', 'desc');
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }

            $items = $query->paginate($perPage);

            // Add image URLs
            $items->getCollection()->transform(function ($item) {
                $item->image_url = $item->image ? Storage::url($item->image) : null;
                return $item;
            });

            // Get statistics
            $totalItems = ShopItem::where('is_active', true)
                ->where('is_active_in_point_shop', true)
                ->where('price', '>', 0)
                ->count();

            $totalPurchases = Purchase::whereHas('shopItem', function ($q) {
                $q->where('is_active_in_point_shop', true)->where('price', '>', 0);
            })->where('status', 'completed')->count();

            return response()->json([
                'success' => true,
                'items' => $items,
                'total_items' => $totalItems,
                'total_purchases' => $totalPurchases
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load point shop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase a point shop item
     */
    public function purchase(Request $request, ShopItem $item)
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10'
        ]);

        $user = Auth::user();
        $quantity = $request->get('quantity', 1);

        // Verify item is available in point shop
        if (!$item->is_active || !$item->is_active_in_point_shop || $item->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This item is not available in the point shop'
            ], 400);
        }

        // Check stock availability
        if ($item->stock !== null && $item->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available'
            ], 400);
        }

        $totalCost = $item->price * $quantity;

        // Check if user has enough points
        if ($user->points < $totalCost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points'
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $item, $quantity, $totalCost) {
                // Deduct points from user
                $user->decrement('points', $totalCost);
                
                // Create purchase record - Point shop purchases are automatically approved
                Purchase::create([
                    'user_id' => $user->id,
                    'shop_item_id' => $item->id,
                    'shop_id' => null, // Point shop has no shop_id
                    'price_paid' => $item->price,
                    'quantity' => $quantity,
                    'status' => 'pending' // Still needs admin approval
                ]);

                // Don't decrease stock yet - wait for admin approval
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase submitted! Your order is pending approval.',
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
     * Admin: Get all point shop items for management
     */
    public function adminIndex(Request $request)
    {
        try {
            $perPage = min($request->get('per_page', 20), 100);
            $search = $request->get('search');
            $status = $request->get('status'); // active, inactive, all

            $query = ShopItem::where('price', '>', 0) // Only items that can be in point shop
                ->select([
                    'id', 'name', 'description', 'price', 'stock', 
                    'is_active', 'is_active_in_point_shop', 'image', 'created_at'
                ])
                ->withCount(['purchases' => function ($q) {
                    $q->where('status', 'completed');
                }]);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active_in_point_shop', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active_in_point_shop', false);
            }

            $items = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Add image URLs
            $items->getCollection()->transform(function ($item) {
                $item->image_url = $item->image ? Storage::url($item->image) : null;
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
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Create new point shop item
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:1', // Point shop items must have points price
            'stock' => 'nullable|integer|min:0',
            'unlimited_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_active_in_point_shop' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'name', 'description', 'price', 'is_active', 'is_active_in_point_shop'
            ]);

            // Handle stock
            if ($request->boolean('unlimited_stock')) {
                $data['stock'] = null;
            } else {
                $data['stock'] = $request->get('stock', 0);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('shop-items', $filename, 'public');
                $data['image'] = $path;
            }

            // Set default values for point shop items
            $data['shop_id'] = null; // Point shop items don't belong to any shop
            $data['cash_price'] = 0.00; // Point shop items only use points

            $item = ShopItem::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Point shop item created successfully',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Update point shop item
     */
    public function update(Request $request, ShopItem $item)
    {
        // Verify this is a point shop item (or can be one)
        if ($item->shop_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This item belongs to a shop and cannot be managed here'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'unlimited_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_active_in_point_shop' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'name', 'description', 'price', 'is_active', 'is_active_in_point_shop'
            ]);

            // Handle stock
            if ($request->boolean('unlimited_stock')) {
                $data['stock'] = null;
            } else {
                $data['stock'] = $request->get('stock', 0);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }

                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('shop-items', $filename, 'public');
                $data['image'] = $path;
            }

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Point shop item updated successfully',
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
     * Admin: Delete point shop item
     */
    public function destroy(ShopItem $item)
    {
        // Verify this is a point shop item
        if ($item->shop_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This item belongs to a shop and cannot be deleted here'
            ], 400);
        }

        try {
            // Delete image if exists
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Point shop item deleted successfully'
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
     * Admin: Toggle point shop availability for an item
     */
    public function toggleActive(ShopItem $item)
    {
        try {
            $item->update([
                'is_active_in_point_shop' => !$item->is_active_in_point_shop
            ]);

            $status = $item->is_active_in_point_shop ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Item {$status} in point shop",
                'is_active_in_point_shop' => $item->is_active_in_point_shop
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle item status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Bulk toggle point shop availability
     */
    public function bulkTogglePointShop(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer|exists:shop_items,id',
            'active' => 'required|boolean'
        ]);

        try {
            ShopItem::whereIn('id', $request->item_ids)
                ->update(['is_active_in_point_shop' => $request->active]);

            $status = $request->active ? 'activated' : 'deactivated';
            $count = count($request->item_ids);

            return response()->json([
                'success' => true,
                'message' => "{$count} items {$status} in point shop"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}