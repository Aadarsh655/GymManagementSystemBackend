<?php

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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/login', 'api/forgot-password','api/register','api/blog','api/blog/*','api/reset-password','api/register/*','api/payments','api/users/*','api/payments/*','api/logout','api/enquiries','api/enquiries/*', 'api/membership','api/membership/*','api/khalti/payment','api/khalti/payment/verify', 'api/password/change', 'api/attendance/filter','api/notices','api/testimonials','api/testimonials/*'
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
