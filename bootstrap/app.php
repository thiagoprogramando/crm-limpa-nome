<?php

use App\Http\Middleware\CheckAccount;
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
        $middleware->web(ShareProducts::class);

        $middleware->appendToGroup('verify', [
            CheckAccount::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
