<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AdminPricingController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PointShopController;
use App\Http\Controllers\LoyaltyCardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication needed)
Route::post('/logout', [LoginController::class, 'logout']);

// Public shop routes
Route::prefix('shops')->group(function () {
  Route::prefix('{shop}')->group(function () {
        // Loyalty Card CRUD
        Route::get('/loyalty-card', [LoyaltyCardController::class, 'show']); 
        Route::post('/loyalty-card', [LoyaltyCardController::class, 'store']); 
        Route::put('/loyalty-card', [LoyaltyCardController::class, 'update']); 
        Route::post('/loyalty-card/toggle', [LoyaltyCardController::class, 'toggle']); 
        Route::post('/loyalty-rewards/{reward}/mark-claimed', [LoyaltyCardController::class, 'markAsClaimed']);
        
        // Loyalty Progress Management
        Route::get('/loyalty-progress', [LoyaltyCardController::class, 'getProgress']); 
        Route::post('/loyalty-progress/{userId}/adjust', [LoyaltyCardController::class, 'adjustProgress']); 
        
        // Loyalty Rewards Management
        Route::post('/loyalty-rewards/{reward}/approve', [LoyaltyCardController::class, 'approveReward']);
        Route::post('/loyalty-rewards/{reward}/reject', [LoyaltyCardController::class, 'rejectReward']);
        
        // Customer Loyalty Routes (for customers viewing shops)
        Route::get('/loyalty-status', [LoyaltyCardController::class, 'getCustomerProgress']); 
        Route::post('/loyalty-claim', [LoyaltyCardController::class, 'claimReward']); 
    });

    
    Route::get('/', [ShopController::class, 'index']); // List all shops
    Route::get('/{shop}', [ShopController::class, 'show']); // View specific shop with items
    Route::get('/{shop}/reviews', [ShopController::class, 'getReviews']); // View reviews
    
});

// Public gallery routes
Route::get('/gallery', [GalleryController::class, 'index']); // Public access
Route::get('/gallery/{id}', [GalleryController::class, 'show']); // Get specific image

// AUTHENTICATED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    
    // Current user route (for getting basic user info)
    Route::get('/user', [UserController::class, 'getCurrentUser']);
    
    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']); // Get full profile with membership
        Route::put('/profile', [UserController::class, 'updateProfile']); // Update profile (including new fields)
        Route::post('/upload-profile-image', [UserController::class, 'uploadProfileImage']);
        Route::get('/stats', [UserController::class, 'getUserStats']); // Get user statistics with age
        Route::get('/memberships', [UserController::class, 'getMembershipHistory']);
        Route::post('/memberships', [UserController::class, 'createMembership']);
    });

    // Users routes (for searching/listing)
    Route::prefix('users')->group(function () {
        Route::get('/search', [UserController::class, 'searchUsers']);
        Route::get('/', [UserController::class, 'getLeaderboard']); // Use controller method instead of closure
        Route::get('/{userId}/profile', [UserController::class, 'getPublicProfile']); // NEW: View other users' profiles
    });

    // Leaderboard routes
    Route::prefix('leaderboard')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index']);
        Route::post('/new-season', [LeaderboardController::class, 'newSeason']);
        Route::get('/current-season', [LeaderboardController::class, 'getCurrentSeason']);
    });

    // Trade routes
    Route::prefix('trades')->group(function () {
        Route::get('/', [TradeController::class, 'index']);
        Route::post('/', [TradeController::class, 'store']);
        Route::patch('/{id}', [TradeController::class, 'update']);
        Route::delete('/{id}', [TradeController::class, 'destroy']);
        Route::get('/stats', [TradeController::class, 'getStats']);
    });

    // Bet routes
    Route::prefix('bets')->group(function () {
        Route::get('/', [BetController::class, 'index']);
        Route::post('/', [BetController::class, 'store']);
        Route::post('/{bet}/accept', [BetController::class, 'accept']);
        Route::post('/{bet}/reject', [BetController::class, 'reject']);
        Route::post('/{bet}/cancel', [BetController::class, 'cancel']);
        Route::post('/{bet}/declare-winner', [BetController::class, 'declareWinner']);
    });

    // Shop interactions (authenticated users)
    Route::prefix('shops/{shop}')->group(function () {
        Route::post('/follow', [ShopController::class, 'toggleFollow']);
        Route::post('/reviews', [ShopController::class, 'addReview']);
    });
    
    // Shop Owner Routes (Authorization handled in controllers)
    Route::prefix('shops')->group(function () {
        Route::post('/', [ShopController::class, 'create']); // Create new shop
        Route::post('/{shop}', [ShopController::class, 'update']); // Update shop (POST for file uploads)
        Route::put('/{shop}', [ShopController::class, 'update']); // Update shop (PUT for form data)
        Route::get('/dashboard/my-shop', [ShopController::class, 'dashboard']); // Shop owner dashboard
        
        // Shop Items Management (Shop Owner)
        Route::prefix('{shop}/items')->group(function () {
            Route::get('/', [ShopItemController::class, 'shopItems']); // Get all items for shop
            Route::post('/', [ShopItemController::class, 'store']); // Create new item
            Route::post('/{shopItem}', [ShopItemController::class, 'update']); // Update item (POST for file uploads)
            Route::put('/{shopItem}', [ShopItemController::class, 'update']); // Update item (PUT for form data)
            Route::delete('/{shopItem}', [ShopItemController::class, 'destroy']); // Delete item
            
            // Order Management
            Route::get('/purchases/pending', [ShopItemController::class, 'getPendingPurchases']);
            Route::post('/purchases/{purchase}/approve', [ShopItemController::class, 'approvePurchase']);
            Route::post('/purchases/{purchase}/reject', [ShopItemController::class, 'rejectPurchase']);
        });
    });
    
    // Shop Item Routes (for purchasing)
    Route::prefix('shop-items')->group(function () {
        Route::post('/{item}/purchase', [ShopItemController::class, 'purchase']);
    });
    
    // User Purchase History
    Route::get('/my-purchases', [ShopItemController::class, 'getPurchases']);
    
    // Admin-only gallery routes
    Route::prefix('gallery')->group(function () {
        Route::post('/', [GalleryController::class, 'store']);
        Route::put('/{id}', [GalleryController::class, 'update']);
        Route::patch('/{id}', [GalleryController::class, 'update']);
        Route::delete('/{id}', [GalleryController::class, 'destroy']);
        Route::post('/upload', [GalleryController::class, 'uploadImage']);
        Route::post('/reorder', [GalleryController::class, 'reorder']);
    });
    
    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        // Shop Management
  Route::get('/shops', [ShopController::class, 'adminIndex']); // All shops (including inactive)
    Route::post('/shops/{shop}/verify', [ShopController::class, 'toggleVerification']);
    Route::post('/shops/{shop}/toggle-active', [ShopController::class, 'toggleActive']);
    
    // Admin Point Shop Management Routes
    Route::put('/shop-items/{item}/point-shop-status', [AdminPricingController::class, 'updatePointShopStatus']); // Update single item point shop status
    Route::put('/shop-items/bulk-point-shop', [AdminPricingController::class, 'bulkUpdatePointShopStatus']); // Bulk update point shop status
    
    // Global Purchase Management
    Route::get('/purchases/pending', [ShopItemController::class, 'getAllPendingPurchases']);
    Route::post('/purchases/{purchase}/approve', [ShopItemController::class, 'approvePurchase']);
    Route::post('/purchases/{purchase}/reject', [ShopItemController::class, 'rejectPurchase']);
    
    // Admin Point Pricing Management
    Route::get('/shop-items', [AdminPricingController::class, 'getAllShopItems']); // Get all items for pricing
    Route::get('/shops/dropdown', [AdminPricingController::class, 'getAllShops']); // Get all shops for filter dropdown  
    Route::put('/shop-items/{item}/price', [AdminPricingController::class, 'updateItemPrice']); // Update single item price
    Route::put('/shop-items/bulk-price', [AdminPricingController::class, 'bulkUpdatePrices']); // Bulk update prices

    // *** ADD THESE MISSING ROUTES FOR POINT ORDERS ***
    Route::get('/point-orders', [AdminPricingController::class, 'getPointOrders']); // Get all point orders
    Route::post('/point-orders/{id}/approve', [AdminPricingController::class, 'approvePointOrder']); // Approve point order
    Route::post('/point-orders/{id}/reject', [AdminPricingController::class, 'rejectPointOrder']); // Reject point order

    // Point shop statistics
    Route::get('/point-shop/statistics', [AdminPricingController::class, 'getPointShopStatistics']);
    });
});

Route::prefix('point-shop')->group(function () {
    Route::get('/', [PointShopController::class, 'index']); // List all point shop items
});

// Point Shop Purchase Routes (Authenticated users)
Route::middleware('auth:sanctum')->group(function () {
    // Point Shop Purchases
    Route::prefix('point-shop')->group(function () {
        Route::post('/{item}/purchase', [PointShopController::class, 'purchase']); // Purchase point shop item
    });
    
    Route::get('/my-shop/notifications', [ShopController::class, 'getShopNotifications']);
    
    // Update existing purchases route to support shop filtering
    Route::get('/my-purchases', [ShopItemController::class, 'getPurchases']); // This route already exists, just needs updating

    // Admin Routes for Point Shop Management
    Route::middleware(['admin'])->prefix('admin/point-shop')->group(function () {
        Route::get('/items', [PointShopController::class, 'adminIndex']); // Admin view all items
        Route::post('/items', [PointShopController::class, 'store']); // Create point shop item
        Route::put('/items/{item}', [PointShopController::class, 'update']); // Update point shop item
        Route::delete('/items/{item}', [PointShopController::class, 'destroy']); // Delete point shop item
        Route::post('/items/{item}/toggle-active', [PointShopController::class, 'toggleActive']); // Toggle point shop availability
        
        // Bulk operations
        Route::put('/items/bulk-toggle', [PointShopController::class, 'bulkTogglePointShop']); // Bulk toggle point shop availability
    });
});
