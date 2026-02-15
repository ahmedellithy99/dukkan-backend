<?php

namespace App\Services\Website;

use App\Models\AdCarousel;
use Illuminate\Database\Eloquent\Collection;

class AdCarouselService
{
    /**
     * Get all active ad carousels ordered by display_order.
     */
    public function getActiveAdCarousels(): Collection
    {
        return AdCarousel::with('media')->ordered()->get();
    }
}
