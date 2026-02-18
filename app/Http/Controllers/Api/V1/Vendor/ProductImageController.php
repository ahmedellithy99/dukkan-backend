<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\UploadProductImageRequest;
use App\Http\Resources\V1\MediaResource;
use App\Models\Product;
use App\Models\Shop;
use App\Services\Vendor\ProductImageService;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    protected ProductImageService $productImageService;

    public function __construct(ProductImageService $productImageService)
    {
        $this->productImageService = $productImageService;
    }

    /**
     * Upload main product image
     */
    public function storeMain(Shop $shop, Product $product, UploadProductImageRequest $request): JsonResponse
    {
        $this->authorize('update', $product);

        $media = $this->productImageService->uploadMainImage(
            $product,
            $request->file('image')
        );

        return response()->api(new MediaResource($media), 201);
    }

    /**
     * Upload secondary product image
     */
    public function storeSecondary(Shop $shop, Product $product, UploadProductImageRequest $request): JsonResponse
    {
        $this->authorize('update', $product);

        $media = $this->productImageService->uploadSecondaryImage(
            $product,
            $request->file('image')
        );

        return response()->api(new MediaResource($media), 201);
    }

    /**
     * Delete main product image
     */
    public function destroyMain(Shop $shop, Product $product)
    {
        $this->authorize('update', $product);

        $deleted = $this->productImageService->deleteMainImage($product);

        if (!$deleted) {
            return response()->api(['message' => 'Main image not found'], 404);
        }

        return response()->api(null, 204);
    }

    /**
     * Delete secondary product image
     */
    public function destroySecondary(Shop $shop, Product $product)
    {
        $this->authorize('update', $product);

        $deleted = $this->productImageService->deleteSecondaryImage($product);

        if (!$deleted) {
            return response()->api(['message' => 'Secondary image not found'], 404);
        }

        return response()->api(null, 204);
    }
}
