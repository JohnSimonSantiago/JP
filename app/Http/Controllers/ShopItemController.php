<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopItem;
use App\Models\Purchase;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ShopItemController extends Controller
{
    public function purchase(Request $request, ShopItem $item)
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10'
        ]);

        $user = Auth::user();
        $quantity = $request->get('quantity', 1);

        $blockReason = $item->getPurchaseBlockReason($user, $quantity);
        if ($blockReason) {
            return response()->json([
                'success' => false,
                'message' => $blockReason
            ], 400);
        }

        if ($item->isPointShopItem()) {
            $totalCost = $item->price * $quantity;
            $pricePerItem = $item->price;
            $currencyType = 'points';
            $balanceField = 'points';
        } else {
            $totalCost = $item->cash_price * $quantity;
            $pricePerItem = $item->cash_price;
            $currencyType = 'cash';
            $balanceField = 'cash';
        }

        try {
            DB::transaction(function () use ($user, $item, $quantity, $totalCost, $pricePerItem, $currencyType, $balanceField) {
                $user->decrement($balanceField, $totalCost);

                Purchase::create([
                    'user_id' => $user->id,
                    'shop_item_id' => $item->id,
                    'shop_id' => $item->shop_id,
                    'price_paid' => $pricePerItem,
                    'currency_type' => $currencyType,
                    'quantity' => $quantity,
                    'status' => 'pending'
                ]);
            });

            // Notify shop owner
            $item->load('shop.owner');
            $shopOwner = $item->shop->owner ?? null;
            if ($shopOwner) {
                PushNotificationService::send(
                    $shopOwner->push_token,
                    'New Order Received',
                    $user->name . ' placed an order for ' . $item->name . '.'
                );
            }

            $user->refresh();
            $newBalance = $user->{$balanceField};

            return response()->json([
                'success' => true,
                'message' => $item->isPointShopItem()
                    ? 'Purchase submitted! Your order is pending approval.'
                    : 'Purchase submitted! Your order is pending approval from the shop owner.',
                'new_balance' => $newBalance,
                'currency_type' => $currencyType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPurchases(Request $request)
    {
        try {
            $perPage = min($request->get('per_page', 20), 100);
            $shopId = $request->get('shop_id');
            $status = $request->get('status');

            $query = Purchase::forUser(Auth::id())
                ->with(['shopItem:id,name,description,image', 'shop:id,name'])
                ->orderBy('created_at', 'desc');

            if ($shopId) {
                $query->where('shop_id', $shopId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $purchases = $query->paginate($perPage);

            $purchases->getCollection()->transform(function ($purchase) {
                if ($purchase->shopItem && $purchase->shopItem->image) {
                    $purchase->shopItem->image_url = Storage::url($purchase->shopItem->image);
                }
                return $purchase;
            });

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

    public function store(Request $request)
    {
        try {
            $shopId = $request->route('shop');
            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }

            $shop = Shop::findOrFail($shopId);
            $user = Auth::user();

            if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'stock' => 'nullable|integer|min:0',
                'unlimited_stock' => 'boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            if ($user->isAdmin()) {
                $rules['price'] = 'nullable|integer|min:0';
                $rules['cash_price'] = 'nullable|numeric|min:0';
            } else {
                $rules['cash_price'] = 'required|numeric|min:0.01';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['image', 'unlimited_stock']);
            $data['shop_id'] = $shop->id;
            $data['is_active'] = $request->get('is_active', true);

            if ($user->isAdmin()) {
                $data['price'] = $request->get('price', 0);
                $data['cash_price'] = $request->get('cash_price', 0);

                if (($data['price'] <= 0) && ($data['cash_price'] <= 0)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'At least one price (points or cash) must be greater than 0'
                    ], 422);
                }
            } else {
                $data['price'] = 0;
                $data['cash_price'] = $request->get('cash_price');
            }

            $unlimitedStock = filter_var($request->get('unlimited_stock', false), FILTER_VALIDATE_BOOLEAN);
            $data['stock'] = $unlimitedStock ? null : (int) $request->get('stock', 0);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('shop-items', 'public');
                $data['image'] = $imagePath;
            }

            $item = ShopItem::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'item' => $item->fresh()
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $shopId = $request->route('shop');
            $itemId = $request->route('shopItem');

            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }
            if (is_object($itemId)) {
                $itemId = $itemId->id;
            }

            $shop = Shop::findOrFail($shopId);
            $item = $shop->items()->findOrFail($itemId);
            $user = Auth::user();

            if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $rules = [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'stock' => 'nullable|integer|min:0',
                'unlimited_stock' => 'boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            if ($user->isAdmin()) {
                $rules['price'] = 'nullable|integer|min:0';
                $rules['cash_price'] = 'nullable|numeric|min:0';
            } else {
                $rules['cash_price'] = 'sometimes|required|numeric|min:0.01';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['image', 'unlimited_stock']);

            if ($user->isAdmin()) {
                if ($request->has('price')) {
                    $data['price'] = $request->get('price', 0);
                }
                if ($request->has('cash_price')) {
                    $data['cash_price'] = $request->get('cash_price', 0);
                }

                $newPrice = $request->has('price') ? $data['price'] : $item->price;
                $newCashPrice = $request->has('cash_price') ? $data['cash_price'] : $item->cash_price;

                if ($newPrice <= 0 && $newCashPrice <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'At least one price (points or cash) must be greater than 0'
                    ], 422);
                }
            } else {
                if ($request->has('cash_price')) {
                    $data['cash_price'] = $request->get('cash_price');
                }
                unset($data['price']);
            }

            if ($request->has('unlimited_stock')) {
                $unlimitedStock = filter_var($request->get('unlimited_stock'), FILTER_VALIDATE_BOOLEAN);
                $data['stock'] = $unlimitedStock ? null : (int) $request->get('stock', $item->stock ?? 0);
            }

            if ($request->hasFile('image')) {
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

    public function destroy(Request $request)
    {
        try {
            $shopId = $request->route('shop');
            $itemId = $request->route('shopItem');

            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }
            if (is_object($itemId)) {
                $itemId = $itemId->id;
            }

            $shop = Shop::findOrFail($shopId);
            $item = $shop->items()->findOrFail($itemId);

            if (!$shop->canBeEditedBy(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

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
                'error' => $e->getMessage(),
                'debug' => [
                    'shop_route' => $request->route('shop'),
                    'item_route' => $request->route('shopItem'),
                    'error_line' => $e->getLine()
                ]
            ], 500);
        }
    }

    public function shopItems(Request $request)
    {
        try {
            $shopId = $request->route('shop');

            if (is_object($shopId)) {
                $shopId = $shopId->id;
            }

            $shop = Shop::findOrFail($shopId);

            if (!$shop->canBeEditedBy(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $items = $shop->items()
                ->withCount(['purchases' => function ($query) {
                    $query->where('status', 'completed');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
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

    public function approvePurchase($shopId, $purchaseId)
    {
        try {
            $shop = Shop::findOrFail($shopId);
            $purchase = $shop->purchases()->with('user')->findOrFail($purchaseId);

            $user = Auth::user();

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
                $shopItem = $purchase->shopItem;

                if ($shopItem->stock !== null) {
                    if ($shopItem->stock < $purchase->quantity) {
                        throw new \Exception('Insufficient stock available. Current stock: ' . $shopItem->stock . ', requested: ' . $purchase->quantity);
                    }
                    $shopItem->decrement('stock', $purchase->quantity);
                }

                $purchase->update(['status' => 'completed']);

                \App\Http\Controllers\LoyaltyCardController::updateLoyaltyOnPurchaseApproval($purchase);
            });

            PushNotificationService::send(
                $purchase->user->push_token,
                'Order Approved ✅',
                'Your order has been approved! Head to the shop to claim it.'
            );

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

    public function rejectPurchase($shopId, $purchaseId, Request $request)
    {
        try {
            $shop = Shop::findOrFail($shopId);
            $purchase = $shop->purchases()->with(['shopItem', 'user'])->findOrFail($purchaseId);

            $user = Auth::user();

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
                $customer = $purchase->user;
                $refundAmount = $purchase->price_paid * $purchase->quantity;

                if ($purchase->currency_type === 'cash') {
                    $customer->increment('cash', $refundAmount);
                } else {
                    $customer->increment('points', $refundAmount);
                }

                $updateData = ['status' => 'rejected'];
                if ($request->has('reason')) {
                    $updateData['rejection_reason'] = $request->input('reason');
                }

                $purchase->update($updateData);
            });

            PushNotificationService::send(
                $purchase->user->push_token,
                'Order Rejected',
                'Your order was rejected and your payment has been refunded.'
            );

            return response()->json([
                'success' => true,
                'message' => 'Purchase rejected and amount refunded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    public function walkInOrder(Request $request, Shop $shop)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.shop_item_id' => 'required|exists:shop_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'customer_type'        => 'required|in:user,walk_in',
            'walk_in_name'         => 'nullable|string|max:100',
            'user_id'              => 'nullable|exists:users,id',
            'payment_method'       => 'required|in:balance,cash',
        ]);

        try {
            DB::transaction(function () use ($request, $shop) {
                foreach ($request->items as $orderItem) {
                    $item = ShopItem::findOrFail($orderItem['shop_item_id']);
                    $quantity = $orderItem['quantity'];

                    if ($item->stock !== null && $item->stock < $quantity) {
                        throw new \Exception("Insufficient stock for item: {$item->name}");
                    }

                    if ($item->stock !== null) {
                        $item->decrement('stock', $quantity);
                    }

                    if ($request->payment_method === 'balance' && $request->user_id) {
                        $customer = \App\Models\User::findOrFail($request->user_id);
                        $cost = $item->cash_price * $quantity;
                        if ($customer->cash < $cost) {
                            throw new \Exception("Insufficient balance for user.");
                        }
                        $customer->decrement('cash', $cost);
                    }

                    Purchase::create([
                        'shop_id'        => $shop->id,
                        'shop_item_id'   => $item->id,
                        'user_id'        => $request->user_id ?? null,
                        'customer_type'  => $request->customer_type,
                        'walk_in_name'   => $request->walk_in_name ?? null,
                        'payment_method' => $request->payment_method,
                        'price_paid'     => $item->cash_price,
                        'quantity'       => $quantity,
                        'currency_type'  => 'cash',
                        'status'         => 'completed',
                    ]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Order recorded successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function salesReport(Request $request, Shop $shop)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $purchases = Purchase::where('shop_id', $shop->id)
            ->where('status', 'completed')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->with(['shopItem:id,name', 'user:id,name'])
            ->orderBy('created_at')
            ->get();

        $totalOrders  = $purchases->count();
        $totalRevenue = $purchases->sum(fn($p) => $p->price_paid * $p->quantity);
        $totalItems   = $purchases->sum('quantity');
        $walkInCount  = $purchases->where('customer_type', 'walk_in')->count();
        $memberCount  = $purchases->where('customer_type', 'user')->count();

        $topItems = $purchases
            ->groupBy(fn($p) => $p->shopItem->name ?? 'Unknown')
            ->map(fn($group) => $group->sum('quantity'))
            ->sortDesc()
            ->take(3);

        $monthLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ["SALES SUMMARY — {$shop->name} — {$monthLabel}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Total Orders',    $totalOrders]);
        fputcsv($handle, ['Total Revenue',   '₱' . number_format($totalRevenue, 2)]);
        fputcsv($handle, ['Total Items Sold', $totalItems]);
        fputcsv($handle, ['Walk-in Orders',  $walkInCount]);
        fputcsv($handle, ['Member Orders',   $memberCount]);
        fputcsv($handle, []);
        fputcsv($handle, ['TOP 3 ITEMS']);
        foreach ($topItems as $name => $qty) {
            fputcsv($handle, [$name, "{$qty} sold"]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['--- ORDER DETAILS ---']);
        fputcsv($handle, ['Date', 'Item', 'Quantity', 'Unit Price', 'Total', 'Customer']);
        foreach ($purchases as $p) {
            fputcsv($handle, [
                $p->created_at->format('Y-m-d H:i'),
                $p->shopItem->name ?? 'N/A',
                $p->quantity,
                number_format($p->price_paid, 2),
                number_format($p->price_paid * $p->quantity, 2),
                $p->customer_type === 'walk_in'
                    ? ($p->walk_in_name ?? 'Walk-in')
                    : ($p->user->name ?? 'Member'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = "sales-{$shop->name}-{$year}-{$month}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

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