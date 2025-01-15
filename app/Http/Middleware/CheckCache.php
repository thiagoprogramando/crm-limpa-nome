<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CheckCache {

    public function handle(Request $request, Closure $next): Response {

        $response = $next($request);
        if ($response instanceof BinaryFileResponse) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        } else {
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        return $response;
    }
}
