<?php

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\SubcategoryController;
use App\Http\Controllers\Api\V1\Admin\AttributeController;
use App\Http\Controllers\Api\V1\Admin\AttributeValueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes (v1)
|--------------------------------------------------------------------------
|
| Admin management routes for platform administration
|
*/

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    // Protected admin routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('categories/{category}/subcategories', SubcategoryController::class)->scoped();

        Route::apiResource('attributes', AttributeController::class);
        Route::apiResource('attribute-values', AttributeValueController::class);
    });
});
