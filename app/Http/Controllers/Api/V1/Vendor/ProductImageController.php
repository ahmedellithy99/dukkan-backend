<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\UploadProductImageRequest;
use App\Http\Requests\V1\Vendor\ReorderProductImagesRequest;
use App\Http\Resources\V1\MediaResource;
use App\Models\Product;
use App\Models\Shop;
use App\Services\Vendor\ProductImageService;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductImageController extends Controller
{
    protected ProductImageService $productImageService;

    public function __construct(ProductImageService $productImageService)
    {
        $this->productImageService = $productImageService;
    }

    /**
     * Upload product images
     */
    public function store(Shop $shop, Product $product, UploadProductImageRequest $request): JsonResponse
    {
        $this->authorize('update', $product);

        $uploadedMedia = $this->productImageService->uploadImages(
            $product,
            $request->file('images')
        );

        return response()->api(MediaResource::collection($uploadedMedia), 201);
    }

    /**
     * Delete a product image
     */
    public function destroy(Shop $shop, Product $product, Media $media): JsonResponse
    {
        $this->authorize('update', $product);

        $deleted = $this->productImageService->deleteImage($product, $media);

        if (!$deleted) {
            return response()->api(null, 404, [], 'Image does not belong to this product');
        }

        return response()->api(null, 204);
    }

    /**
     * Reorder product images
     */
    public function reorder(Shop $shop, Product $product, ReorderProductImagesRequest $request): JsonResponse
    {
        $this->authorize('update', $product);

        $orderedImages = $this->productImageService->reorderImages(
            $product,
            $request->input('order')
        );

        return response()->api(MediaResource::collection($orderedImages));
    }
}
