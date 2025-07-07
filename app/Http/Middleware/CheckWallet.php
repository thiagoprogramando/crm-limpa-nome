<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckWallet {

    public function handle(Request $request, Closure $next): Response {

        try {
            if (Auth::check()) {

                $user = Auth::user();
                if (($user->type !== 2) && (empty($user->company_name) || empty($user->company_cpfcnpj) || empty($user->company_address) || empty($user->company_email))) {
                    return redirect()->route('profile')->with('info', 'Preencha todos os dados para acessar sua plataforma!');
                }

                if (empty($user->token_key) || empty($user->wallet)) {
                    return redirect()->route('app')->with('info', 'Escolha um Gateway de Pagamentos no Menu Integrações!');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Erro no middleware CheckUsability: '.$e->getMessage());
            return redirect()->route('payments')->with('error', '');
        }

        return $next($request);
    }
}
