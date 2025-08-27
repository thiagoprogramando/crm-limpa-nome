<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckWallet {

    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if ($user && $user->type !== 4 && $user->type !== 1 && ($user->token_wallet == null || $user->token_key == null)) {
            return redirect()->route('create-wallet')->with('info', 'Você precisa de uma Carteira Digital para acessar esse módulo!');
        }

        return $next($request);
    }
}
