<?php

use App\Http\Middleware\Cache;
use App\Http\Middleware\CheckAccount;
use App\Http\Middleware\CheckCache;
use App\Http\Middleware\CheckWallet;
use App\Http\Middleware\Monthly;
use App\Http\Middleware\ShareProducts;
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
        $middleware->web( [
            ShareProducts::class,
            CheckCache::class
        ]);

        $middleware->appendToGroup('checkMonthly', [
            Monthly::class,
        ]);

        $middleware->appendToGroup('checkAccount', [
            CheckAccount::class,
        ]);

        $middleware->appendToGroup('checkWallet', [
            CheckWallet::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
