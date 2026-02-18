<?php

namespace App\Services\Vendor;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductImageService
{
    /**
     * Upload main product image
     *
     * @param Product $product
     * @param UploadedFile $image
     * @return Media
     */
    public function uploadMainImage(Product $product, UploadedFile $image): Media
    {
        // Spatie's singleFile() collection automatically replaces existing image
        return $product->addMedia($image)
            ->toMediaCollection('main_image');
    }

    /**
     * Upload secondary product image
     *
     * @param Product $product
     * @param UploadedFile $image
     * @return Media
     */
    public function uploadSecondaryImage(Product $product, UploadedFile $image): Media
    {
        // Spatie's singleFile() collection automatically replaces existing image
        return $product->addMedia($image)
            ->toMediaCollection('secondary_image');
    }

    /**
     * Delete main product image
     *
     * @param Product $product
     * @return bool
     */
    public function deleteMainImage(Product $product): bool
    {
        $image = $product->getFirstMedia('main_image');

        if (!$image) {
            return false;
        }

        $image->delete();

        return true;
    }

    /**
     * Delete secondary product image
     *
     * @param Product $product
     * @return bool
     */
    public function deleteSecondaryImage(Product $product): bool
    {
        $image = $product->getFirstMedia('secondary_image');

        if (!$image) {
            return false;
        }

        $image->delete();

        return true;
    }
}
