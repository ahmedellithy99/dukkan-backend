<?php

use App\Exceptions\Api\ApiExceptionMapper;
use App\Providers\AuthServiceProvider;
use App\Providers\ResponseMacroServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API middleware
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureJsonResponse::class,
            \App\Http\Middleware\AddApiVersionHeader::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'city.resolve' => \App\Http\Middleware\ResolveCityFromHeader::class,
        ]);

        // Throttle configuration
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        ApiExceptionMapper::register($exceptions);
    })
    ->withProviders([
        ResponseMacroServiceProvider::class,
        AuthServiceProvider::class,
    ])
    ->create();
