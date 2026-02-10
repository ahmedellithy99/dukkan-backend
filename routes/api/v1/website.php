<?php

use App\Http\Controllers\Api\V1\Website\AdCarouselController;
use App\Http\Controllers\Api\V1\Website\ShopController;
use App\Http\Controllers\Api\V1\Website\CategoryController;
use App\Http\Controllers\Api\V1\Website\SubcategoryController;
use App\Http\Controllers\Api\V1\Website\AttributeController;
use App\Http\Controllers\Api\V1\Website\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website API Routes (v1)
|--------------------------------------------------------------------------
|
| Public website routes for product discovery and browsing
|
*/

Route::middleware('throttle:public')->group(function () {
    Route::apiResource('shops', ShopController::class)->only(['index', 'show']);

    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('categories/{category}/subcategories', SubcategoryController::class)->only(['index', 'show'])->scoped();

    Route::apiResource('attributes', AttributeController::class)->only(['index', 'show']);

    Route::apiResource('products', ProductController::class)->only(['index', 'show']);

    // Offers endpoint for homepage (products with discounts)
    Route::get('offers', [ProductController::class, 'offers']);

    // Ad Carousel for Homepage
    Route::get('ad-carousels', [AdCarouselController::class, 'index']);
});

// Product analytics tracking (public endpoints with higher rate limit)
Route::middleware('throttle:analytics')->group(function () {
    Route::post('products/{product}/track/whatsapp', [ProductController::class, 'trackWhatsAppClick']);
    Route::post('products/{product}/track/location', [ProductController::class, 'trackLocationClick']);
});

