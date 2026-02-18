<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\UploadShopLogoRequest;
use App\Http\Resources\V1\MediaResource;
use App\Models\Shop;
use App\Services\Vendor\ShopLogoService;
use Illuminate\Http\JsonResponse;

class ShopLogoController extends Controller
{
    protected ShopLogoService $shopLogoService;

    public function __construct(ShopLogoService $shopLogoService)
    {
        $this->shopLogoService = $shopLogoService;
    }

    /**
     * Upload or update shop logo
     */
    public function store(Shop $shop, UploadShopLogoRequest $request): JsonResponse
    {
        $this->authorize('update', $shop);

        $media = $this->shopLogoService->uploadLogo(
            $shop,
            $request->file('logo')
        );

        return response()->api(new MediaResource($media), 201);
    }

    /**
     * Delete shop logo
     */
    public function destroy(Shop $shop)
    {
        $this->authorize('update', $shop);

        $this->shopLogoService->deleteLogo($shop);

        return response()->api(null, 204);
    }
}
