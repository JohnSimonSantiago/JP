<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\BetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/logout', [LoginController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    // User routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/upload-profile-image', [UserController::class, 'uploadProfileImage']);
        Route::get('/memberships', [UserController::class, 'getMembershipHistory']);
        Route::post('/memberships', [UserController::class, 'createMembership']);
        
        // Current authenticated user
        Route::get('/', function () {
            return response()->json([
                'success' => true,
                'user' => auth()->user()
            ]);
        });
    });

    // Users routes (for searching/listing)
    Route::prefix('users')->group(function () {
        Route::get('/search', [UserController::class, 'searchUsers']);
        Route::get('/', function () {
            $users = \App\Models\User::select('id', 'name', 'level', 'points', 'is_premium')
                                    ->orderBy('points', 'desc')
                                    ->get();
            
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        });
    });

    // Leaderboard
    Route::get('/leaderboard', [UserController::class, 'getLeaderboard']);

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
});

Route::middleware('auth:sanctum')->group(function () {
    // Public shop routes (for all authenticated users)
    Route::prefix('shop')->group(function () {
        Route::get('/', [ShopController::class, 'index']);
        Route::post('/{item}/purchase', [ShopController::class, 'purchase']);
        Route::get('/purchases', [ShopController::class, 'getPurchases']);
    });

    // Admin-only shop routes
    Route::middleware('admin')->prefix('admin/shop')->group(function () {
        Route::get('/items', [ShopController::class, 'adminIndex']);
        Route::post('/items', [ShopController::class, 'store']);
        Route::put('/items/{item}', [ShopController::class, 'update']);
        Route::delete('/items/{item}', [ShopController::class, 'destroy']);
        
        // Purchase management
        Route::get('/purchases/pending', [ShopController::class, 'getPendingPurchases']);
        Route::post('/purchases/{purchase}/approve', [ShopController::class, 'approvePurchase']);
        Route::post('/purchases/{purchase}/reject', [ShopController::class, 'rejectPurchase']);
    });
});