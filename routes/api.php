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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication needed)
Route::post('/logout', [LoginController::class, 'logout']);

// Public shop routes
Route::prefix('shops')->group(function () {
    Route::get('/', [ShopController::class, 'index']); // List all shops
    Route::get('/{shop}', [ShopController::class, 'show']); // View specific shop with items
    Route::get('/{shop}/reviews', [ShopController::class, 'getReviews']); // View reviews
});

// AUTHENTICATED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    
    // User routes
    Route::get('/user', [UserController::class, 'getCurrentUser']);
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/upload-profile-image', [UserController::class, 'uploadProfileImage']);
        Route::get('/memberships', [UserController::class, 'getMembershipHistory']);
        Route::post('/memberships', [UserController::class, 'createMembership']);
    });

    // Users routes (for searching/listing)
    Route::prefix('users')->group(function () {
        Route::get('/search', [UserController::class, 'searchUsers']);
        Route::get('/', function () {
            $users = \App\Models\User::select('id', 'name', 'level', 'stars', 'points', 'is_premium', 'profile_image')
                                    ->orderBy('stars', 'desc')
                                    ->orderBy('points', 'desc')
                                    ->get();
            
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        });
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
    
    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        // Shop Management
        Route::get('/shops', [ShopController::class, 'adminIndex']); // All shops (including inactive)
        Route::post('/shops/{shop}/verify', [ShopController::class, 'toggleVerification']);
        Route::post('/shops/{shop}/toggle-active', [ShopController::class, 'toggleActive']);
        
        // Global Purchase Management
        Route::get('/purchases/pending', [ShopItemController::class, 'getAllPendingPurchases']);
        Route::post('/purchases/{purchase}/approve', [ShopItemController::class, 'approvePurchase']);
        Route::post('/purchases/{purchase}/reject', [ShopItemController::class, 'rejectPurchase']);
    });
});