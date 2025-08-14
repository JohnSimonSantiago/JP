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

// Route::get('/', function () {
//     return view('app');
// });

Route::post("/login", [LoginController::class, "login"]);
Route::get("/checkUser", [LoginController::class, "checkLogin"]);
Route::post("/logout", [LoginController::class, "logout"]);

Route::post("/signup", [UserController::class, "signUp"]);

Route::get('/shops', function () {
    return view('app'); // Your main Vue.js app view
});

Route::get('/shop/{id}', function () {
    return view('app'); // Individual shop view
});

// Shop owner dashboard
Route::get('/my-shop', function () {
    return view('app');
})->middleware(['auth', 'shop_owner']);

// Admin routes
Route::get('/admin/shops', function () {
    return view('app');
})->middleware(['auth', 'admin']);

Route::get('/admin/purchases', function () {
    return view('app');
})->middleware(['auth', 'admin']);

//kababaan amin
Route::get('/{vue?}', function(){
    return view('app');
})->where('vue', '[\/\w\.-]*');
