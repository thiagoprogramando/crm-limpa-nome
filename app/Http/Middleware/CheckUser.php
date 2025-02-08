<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUser {
    
    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if ($user && $user->type === 3) {
            return redirect()->route('logout')->with('info', 'Acesso negado!');
        }

        if($user && !empty($user->deleted_at)) {
            return redirect()->route('logout')->with('info', 'Acesso negado!');
        }

        return $next($request);
    }
}
