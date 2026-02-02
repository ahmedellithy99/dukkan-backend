<?php

use App\Http\Controllers\Api\V1\Website\ShopController;
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
