<?php

use App\Http\Middleware\CheckAccountState;
use App\Http\Middleware\CustomCheckForAnyAbility;
use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\UserLanguage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Sentry\Laravel\Integration;

/**
 * Application configuration and bootstrapping.
 *
 * This script configures the Laravel application, setting up routing,
 * middleware, and exception handling.
 */

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(UserLanguage::class);
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CustomCheckForAnyAbility::class,
            'two-factor' => EnforceTwoFactor::class,
            'account-disabled' => CheckAccountState::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
