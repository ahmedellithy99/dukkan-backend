<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get aggregated dashboard statistics for the authenticated vendor.
     *
     * Returns total products, active/inactive counts, engagement metrics,
     * engagement rate, and trend data.
     */
    public function stats(Request $request): JsonResponse
    {
        $vendor = $request->user();

        // Get aggregated stats
        $stats = $this->dashboardService->getVendorStats($vendor);

        // Get trends (optional, defaults to 7 days)
        $days = $request->input('days', 7);
        $trends = $this->dashboardService->calculateTrends($vendor, $days);

        // Combine stats and trends
        $data = array_merge($stats, [
            'trends' => $trends,
        ]);

        return response()->api($data);
    }
}
