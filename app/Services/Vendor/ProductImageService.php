<?php

namespace App\Services\Vendor;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductImageService
{
    /**
     * Upload multiple images to a product
     *
     * @param Product $product
     * @param array $images
     * @return Collection
     */
    public function uploadImages(Product $product, array $images): Collection
    {
        $uploadedMedia = collect();
        $currentMaxOrder = $product->getMedia('product_images')->max('order_column') ?? -1;

        foreach ($images as $index => $image) {
            $media = $product->addMedia($image)
                ->withCustomProperties([
                    'order' => $currentMaxOrder + $index + 1,
                ])
                ->toMediaCollection('product_images');
            
            $uploadedMedia->push($media);
        }

        return $uploadedMedia;
    }

    /**
     * Delete a product image
     *
     * @param Product $product
     * @param Media $media
     * @return bool
     */
    public function deleteImage(Product $product, Media $media): bool
    {
        // Verify media belongs to this product
        if ($media->model_id !== $product->id || $media->model_type !== Product::class) {
            return false;
        }

        // Delete the media (Spatie handles file deletion automatically)
        $media->delete();

        return true;
    }

    /**
     * Reorder product images
     *
     * @param Product $product
     * @param array $orderData
     * @return Collection
     */
    public function reorderImages(Product $product, array $orderData): Collection
    {
        foreach ($orderData as $item) {
            $media = Media::find($item['id']);
            
            // Skip if media doesn't belong to this product
            if (!$media || $media->model_id !== $product->id || $media->model_type !== Product::class) {
                continue;
            }

            $media->setCustomProperty('order', $item['order']);
            $media->save();
        }

        $product->refresh();
        
        return $product->getMedia('product_images')
            ->sortBy(function ($media) {
                return $media->getCustomProperty('order', 0);
            });
    }
}
