<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\AdCarouselResource;
use App\Services\Website\AdCarouselService;
use Illuminate\Http\JsonResponse;

class AdCarouselController extends Controller
{
    protected AdCarouselService $adCarouselService;

    public function __construct(AdCarouselService $adCarouselService)
    {
        $this->adCarouselService = $adCarouselService;
    }

    /**
     * Display active ad carousels for homepage
     */
    public function index(): JsonResponse
    {
        $adCarousels = $this->adCarouselService->getActiveAdCarousels();

        return response()->api(AdCarouselResource::collection($adCarousels));
    }
}
