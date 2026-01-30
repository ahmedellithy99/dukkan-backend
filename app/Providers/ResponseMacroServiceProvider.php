<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register API response macro for consistent API responses
        Response::macro('api', function ($data = null, int $status = 200, array $meta = []) {
            $version = config('app.api_version', 'v1.0.0');

            if ($status === 204) {
                return response()->noContent();
            }
            
            $dataOut = $data;
            $autoPagination = null;
            $autoLinks = null;

            // 1) If it's a Laravel API Resource / ResourceCollection
            if ($data instanceof JsonResource) {
                $payload = $data->response(request())->getData(true);

                // Resource responses are usually wrapped as: { data: ..., meta: ..., links: ... }
                $dataOut = $payload['data'] ?? $payload;

                // If pagination exists, Laravel puts it in meta + links
                if (isset($payload['meta']) && is_array($payload['meta'])) {
                    $autoPagination = [
                        'current_page' => $payload['meta']['current_page'] ?? null,
                        'per_page' => $payload['meta']['per_page'] ?? null,
                        'total' => $payload['meta']['total'] ?? null,
                        'last_page' => $payload['meta']['last_page'] ?? null,
                        'from' => $payload['meta']['from'] ?? null,
                        'to' => $payload['meta']['to'] ?? null,
                    ];
                    $autoPagination = array_filter($autoPagination, fn ($v) => $v !== null);
                }

                if (isset($payload['links']) && is_array($payload['links'])) {
                    $autoLinks = $payload['links']; // first/last/prev/next (Laravel format)
                }
            }

            // 2) If it's a paginator directly (not wrapped in a resource)
            if ($data instanceof LengthAwarePaginator) {
                $dataOut = $data->items();

                $autoPagination = [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ];

                $autoLinks = [
                    'first' => $data->url(1),
                    'last' => $data->url($data->lastPage()),
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ];
            } elseif ($data instanceof Paginator) {
                $dataOut = $data->items();

                $autoPagination = [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                ];

                $autoLinks = [
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ];
            }

            // Build meta (keep your version_info, merge user meta, then auto pagination/links if not provided)
            $metaOut = array_merge([
                'version_info' => [
                    'current' => $version,
                    'latest' => $version,
                    'deprecated' => false,
                    'sunset_date' => null,
                ],
            ], $meta);

            if ($autoPagination && ! isset($metaOut['pagination'])) {
                $metaOut['pagination'] = $autoPagination;
            }
            if ($autoLinks && ! isset($metaOut['links'])) {
                $metaOut['links'] = $autoLinks;
            }

            return response()->json([
                'api_version' => $version,
                'success' => $status < 400,
                'data' => $dataOut,
                'meta' => $metaOut,
            ], $status);
        });

        Response::macro('apiError', function ($message, $code = 'ERROR', array $fields = [], int $status = 400, array $meta = []) {
            $version = config('app.api_version', 'v1.0.0');

            return response()->json([
                'api_version' => $version,
                'success' => false,
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'fields' => $fields,
                ],
                'meta' => array_merge([
                    'version_info' => [
                        'current' => $version,
                        'latest' => $version,
                        'deprecated' => false,
                        'sunset_date' => null,
                    ],
                ], $meta),
            ], $status);
        });
    }
}
