<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController; // Add this import

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
Route::post('/logout', [LoginController::class, 'logout']);


Route::middleware(['auth:sanctum'])->group(function () {
    // User Profile Routes
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::post('/user/upload-profile-image', [UserController::class, 'uploadProfileImage']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    
    // Membership Routes
    Route::get('/user/memberships', [UserController::class, 'getMembershipHistory']);
    Route::post('/user/memberships', [UserController::class, 'createMembership']);
});