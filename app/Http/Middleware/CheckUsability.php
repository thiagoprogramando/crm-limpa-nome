<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUsability {

    public function handle(Request $request, Closure $next): Response {
       
        if (Auth::check()) {

            $user = Auth::user();
            if ((empty($user->company_name) || empty($user->company_cpfcnpj) || empty($user->company_address) || empty($user->company_email) && $user->type == 99)) {
                return redirect()->route('profile')->with('info', 'Preencha todos os dados para acessar sua plataforma!');
            }

            if (empty($user->terms_of_usability) && $user->type == 99) {
                return redirect()->route('view-terms-of-usability')->with('info', 'Você precisa concordar e assinar os termos de uso da Plataforma!');
            }
        }

        return $next($request);
    }
}
