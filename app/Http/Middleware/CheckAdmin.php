<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin {

    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if ($user && $user->type === 1) {
            return $next($request);
        }

        return redirect()->route('logout')->with('info', 'Acesso negado!');
    }
}
