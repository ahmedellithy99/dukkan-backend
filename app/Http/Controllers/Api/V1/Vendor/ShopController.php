<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\StoreShopRequest;
use App\Http\Requests\V1\Vendor\UpdateShopRequest;
use App\Http\Resources\V1\Vendor\ShopResource;
use App\Models\Shop;
use App\Services\Vendor\ShopService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    protected ShopService $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->authorizeResource(Shop::class, 'my_shop');
        
        $this->shopService = $shopService;
    }

    /**
     * Display a listing of shops for the vendor
     */
    public function index(Request $request): JsonResponse
    {
        $shops = $this->shopService->getShops($request, 20);

        return response()->api(ShopResource::collection($shops),200);
    }

    /**
     * Store a newly created shop
     */
    public function store(StoreShopRequest $request): JsonResponse
    {
        $shop = $this->shopService->createShop($request->validated());

        return response()->api(new ShopResource($shop), 201);
    }

    /**
     * Display the specified shop
     */
    public function show(Shop $my_shop): JsonResponse
    {
        $shop = $this->shopService->getShop($my_shop);

        return response()->api(new ShopResource($shop));
    }

    /**
     * Update the specified shop
     */
    public function update(UpdateShopRequest $request, Shop $my_shop): JsonResponse
    {
        $my_shop =$this->shopService->updateShop($my_shop, $request->validated());

        return response()->api(new ShopResource($my_shop));
    }

    /**
     * Remove the specified shop (soft delete)
     */
    public function destroy(Shop $my_shop)
    {
        $this->shopService->deleteShop($my_shop);

        return response()->api(null, 204);
    }

    /**
     * Restore a soft-deleted shop
     */
    public function restore(Shop $shop): JsonResponse
    {
        $this->authorize('restore', $shop);
        
        $this->shopService->restoreShop($shop);

        return response()->api(new ShopResource($shop));
    }
}