<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cache {

    public function handle(Request $request, Closure $next): Response {
        
        $response = $next($request);
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        return $response;
    }
}
