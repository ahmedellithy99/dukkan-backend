<?php

use App\Http\Controllers\Api\V1\Vendor\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor API Routes (v1)
|--------------------------------------------------------------------------
|
| Vendor/shop management routes for shop owners
|
*/

// Vendor Authentication Routes
Route::prefix('vendor')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected vendor routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});