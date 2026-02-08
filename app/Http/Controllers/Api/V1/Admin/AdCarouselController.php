<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreAdCarouselRequest;
use App\Http\Requests\V1\Admin\UpdateAdCarouselRequest;
use App\Http\Resources\V1\Admin\AdCarouselResource;
use App\Models\AdCarousel;
use App\Services\Admin\AdCarouselService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdCarouselController extends Controller
{
    protected AdCarouselService $adCarouselService;

    public function __construct(AdCarouselService $adCarouselService)
    {
        $this->authorizeResource(AdCarousel::class, 'ad_carousel');
        $this->adCarouselService = $adCarouselService;
    }

    /**
     * Display a listing of ad carousels
     */
    public function index(Request $request): JsonResponse
    {
        $adCarousels = $this->adCarouselService->getAdCarousels($request, 20);

        return response()->api(AdCarouselResource::collection($adCarousels));
    }

    /**
     * Store a newly created ad carousel
     */
    public function store(StoreAdCarouselRequest $request): JsonResponse
    {
        $adCarousel = $this->adCarouselService->createAdCarousel($request->validated());

        return response()->api(new AdCarouselResource($adCarousel), 201);
    }

    /**
     * Display the specified ad carousel
     */
    public function show(AdCarousel $adCarousel): JsonResponse
    {
        $adCarousel = $this->adCarouselService->getAdCarousel($adCarousel);
        
        return response()->api(new AdCarouselResource($adCarousel));
    }

    /**
     * Update the specified ad carousel (image only)
     */
    public function update(UpdateAdCarouselRequest $request, AdCarousel $adCarousel): JsonResponse
    {
        $adCarousel = $this->adCarouselService->updateAdCarousel($adCarousel);

        return response()->api(new AdCarouselResource($adCarousel));
    }

    /**
     * Remove the specified ad carousel
     */
    public function destroy(AdCarousel $adCarousel)
    {
        $this->adCarouselService->deleteAdCarousel($adCarousel);
        return response()->api(null, 204);
    }
}
