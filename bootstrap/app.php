<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckDeanUsertype;
use App\Http\Middleware\CheckStaff_OSAUsertype;
use App\Http\Middleware\CheckBranch_OSAUsertype;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'Dean_OSA' => CheckDeanUsertype::class,
            'Staff_OSA' => CheckStaff_OSAUsertype::class,
            'Branch_OSA' => CheckBranch_OSAUsertype::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
