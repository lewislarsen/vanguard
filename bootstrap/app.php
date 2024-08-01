<?php

use App\Http\Middleware\UserLanguage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Application configuration and bootstrapping.
 *
 * This script configures the Laravel application, setting up routing,
 * middleware, and exception handling.
 */

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(UserLanguage::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Flare::handles($exceptions);
    })->create();
