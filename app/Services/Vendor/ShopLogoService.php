<?php

namespace App\Services\Vendor;

use App\Models\Shop;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ShopLogoService
{
    /**
     * Upload or replace shop logo
     *
     * @param Shop $shop
     * @param UploadedFile $logo
     * @return Media
     */
    public function uploadLogo(Shop $shop, UploadedFile $logo): Media
    {
        // Spatie's singleFile() collection automatically replaces existing logo
        return $shop->addMedia($logo)
            ->toMediaCollection('logo');
    }

    /**
     * Delete shop logo
     *
     * @param Shop $shop
     * @return bool
     */
    public function deleteLogo(Shop $shop): bool
    {
        $logo = $shop->getFirstMedia('logo');

        if (!$logo) {
            return false;
        }

        $logo->delete();

        return true;
    }
}
