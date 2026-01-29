<?php

namespace App\Http\Middleware;

use App\Models\City;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCityFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $citySlug = $request->header('X-City');

        // If city is optional for some endpoints, allow missing header
        if (!$citySlug) {
            return $next($request);
        }

        $citySlug = strtolower(trim($citySlug));

        // Basic slug validation (avoid weird inputs)
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $citySlug)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CITY_HEADER',
                    'message' => 'Invalid X-City header format.',
                ],
            ], 422);
        }

        $city = City::where('slug', $citySlug)->first();

        if (!$city) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CITY_NOT_FOUND',
                    'message' => 'City not found for provided X-City header.',
                ],
            ], 422);
        }

        // Store City model for controllers/services
        $request->attributes->set('city', $city);

        return $next($request);
    }
}