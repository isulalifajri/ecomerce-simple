<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthenticationController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login'])->name('login');
Route::post('logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');

// List Product
Route::get('products', [ProductController::class, 'products']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('checkout', [OrderController::class, 'checkout']);
    Route::post('pay', [OrderController::class, 'pay']);
});

Route::post('midtrans-callback', [OrderController::class, 'callback']);
