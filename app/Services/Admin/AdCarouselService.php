<?php

namespace App\Services\Admin;

use App\Models\AdCarousel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdCarouselService
{
    /**
     * Get ad carousels with pagination.
     */
    public function getAdCarousels(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $query = AdCarousel::query()->ordered();

        return $query->paginate($perPage);
    }

    /**
     * Get a single ad carousel.
     */
    public function getAdCarousel(AdCarousel $adCarousel): AdCarousel
    {
        return $adCarousel->load('media');
    }

    /**
     * Create a new ad carousel with auto-incremented display_order.
     */
    public function createAdCarousel(array $data): AdCarousel
    {
        return DB::transaction(function () use ($data) {
            $maxDisplayOrder = AdCarousel::max('display_order') ?? -1;
            $adCarousel = AdCarousel::create(['title' => $data['title'], 'display_order' => $maxDisplayOrder + 1]);

            $adCarousel->addMediaFromRequest('carousel_image')
                ->toMediaCollection('carousel_image');

            return $adCarousel->load('media');
        });
    }

    /**
     * Update an ad carousel (image only).
     *
     * Note: Authorization is handled by AdCarouselPolicy
     */
    public function updateAdCarousel(AdCarousel $adCarousel): AdCarousel
    {
        return DB::transaction(function () use ($adCarousel) {
            // Clear existing image and upload new one
            $adCarousel->clearMediaCollection('carousel_image');
            
            $adCarousel->addMediaFromRequest('carousel_image')
                ->toMediaCollection('carousel_image');

            return $adCarousel->load('media');
        });
    }

    /**
     * Delete an ad carousel.
     *
     * Note: Authorization is handled by AdCarouselPolicy
     */
    public function deleteAdCarousel(AdCarousel $adCarousel): bool
    {
        return $adCarousel->delete();
    }
}
