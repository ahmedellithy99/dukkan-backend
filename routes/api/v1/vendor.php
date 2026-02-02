<?php

use App\Http\Controllers\Api\V1\Vendor\AuthController;
use App\Http\Controllers\Api\V1\Vendor\LocationController;
use App\Http\Controllers\Api\V1\Vendor\ShopController;
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

        Route::put('locations/{location}', [LocationController::class, 'update'])
            ->middleware('can:update,location');

        Route::apiResource('/my-shops', ShopController::class);
        Route::post('my-shops/{shop}/restore', [ShopController::class, 'restore'])
            ->withTrashed();
    });
});
