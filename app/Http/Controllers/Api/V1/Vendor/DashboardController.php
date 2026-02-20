<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Vendor\ProductActivityResource;
use App\Models\User;
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
        $this->authorize('viewStats', User::class);
        
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

    /**
     * Get recent activity feed for the authenticated vendor.
     *
     * Returns chronological list of customer interactions with vendor's products,
     * including activity type and product details, filtered by timeframe.
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $this->authorize('viewActivity', User::class);
        
        $vendor = $request->user();

        // Get timeframe from request (defaults to 7 days)
        $days = $request->input('days', 7);
        
        // Get limit from request (defaults to 50)
        $limit = $request->input('limit', 50);

        // Validate inputs
        $days = max(1, min((int)$days, 90)); // Between 1 and 90 days
        $limit = max(1, min((int)$limit, 100)); // Between 1 and 100 activities

        // Get recent activities
        $activities = $this->dashboardService->getRecentActivity($vendor, $days, $limit);

        return response()->api([
            'activities' => ProductActivityResource::collection($activities),
            'timeframe_days' => $days,
            'count' => $activities->count(),
        ]);
    }
}
