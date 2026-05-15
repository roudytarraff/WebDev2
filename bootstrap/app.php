<?php

use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Middleware\IsOfficeStaffMiddleware;
use App\Http\Middleware\IsconnectedMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'isconnected' => IsconnectedMiddleware::class,
            'isAdmin' => IsAdminMiddleware::class,
            'isOfficeStaff' => IsOfficeStaffMiddleware::class,
            'otp' => \App\Http\Middleware\EnsureOtpVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
