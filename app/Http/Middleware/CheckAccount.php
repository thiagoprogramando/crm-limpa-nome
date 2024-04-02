<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccount {

    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if($user && $user->type != 4) {
            if ($user->wallet === null || $user->api_key === null) {
                return redirect()->route('profile')->with('error', 'Complete seus dados para acessar todos os módulos!');
            }
        }
        

        return $next($request);
    }
}
