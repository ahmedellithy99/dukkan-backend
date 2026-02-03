<?php

use App\Http\Controllers\Api\V1\Website\ShopController;
use App\Http\Controllers\Api\V1\Website\CategoryController;
use App\Http\Controllers\Api\V1\Website\SubcategoryController;
use App\Http\Controllers\Api\V1\Website\AttributeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website API Routes (v1)
|--------------------------------------------------------------------------
|
| Public website routes for product discovery and browsing
|
*/

Route::apiResource('shops', ShopController::class)->only(['index', 'show']);

Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('categories/{category}/subcategories', SubcategoryController::class)->only(['index', 'show'])->scoped();

Route::apiResource('attributes', AttributeController::class)->only(['index', 'show']);
