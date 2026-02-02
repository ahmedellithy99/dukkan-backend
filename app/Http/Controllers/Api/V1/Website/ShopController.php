<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\ShopResource;
use App\Services\Website\ShopService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    protected ShopService $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Display a listing of public shops (active only)
     */
    public function index(Request $request): JsonResponse
    {
        $shops = $this->shopService->getPublicShops($request, 20);

        return response()->api(ShopResource::collection($shops),200);
    }

    /**
     * Display the specified public shop (active only)
     */
    public function show(string $slug): JsonResponse
    {
        $shop = $this->shopService->getPublicShop($slug);
        return response()->api(new ShopResource($shop));
    }
}
