<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth routes
Route::post("/login", [LoginController::class, "login"]);
Route::get("/checkUser", [LoginController::class, "checkLogin"]);
Route::post("/logout", [LoginController::class, "logout"]);
Route::post("/signup", [UserController::class, "signUp"]);

// SPA Routes - All return the main Vue.js app
// Remove middleware since authorization is handled in Vue.js and API
Route::get('/shops', function () {
    return view('app');
});

Route::get('/shop/{id}', function () {
    return view('app');
});

// FIXED: Remove the problematic middleware
Route::get('/my-shop', function () {
    return view('app');
}); // No middleware - authorization handled in Vue.js

// Admin routes - also remove problematic middleware
Route::get('/admin/shops', function () {
    return view('app');
});

Route::get('/admin/purchases', function () {
    return view('app');
});

// Catch-all route for Vue.js SPA (this should be last)
Route::get('/{vue?}', function(){
    return view('app');
})->where('vue', '[\/\w\.-]*');