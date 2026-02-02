<?php

namespace App\Exceptions\Api;

use App\Exceptions\Domain\DomainException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionMapper
{
    public static function register(Exceptions $exceptions): void
    {
        // Handle Domain Exceptions first (most specific)
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->apiError(
                    $e->getMessage(),
                    $e->apiCode(),
                    [],
                    $e->status()
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->apiError('Unauthenticated.', 'UNAUTHENTICATED', [], 401);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->apiError('Forbidden.', 'FORBIDDEN', [], 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
        if ($request->expectsJson()) {
        return response()->apiError('Resource not found.', 'NOT_FOUND', [], 404);
        }
});
    }
}
