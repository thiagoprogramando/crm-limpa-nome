<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccount {

    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if($user && $user->type != 4) {
            if ($user->wallet === null || $user->api_key === null) {
                return redirect()->route('profile')->with('info', 'Complete seus dados para acessar todos os módulos!');
            }

            if ($user->status <> 1) {
                return redirect()->route('profile')->with('info', 'É necessário finalizar sua documentação!');
            }

            $payment = Invoice::where('id_user', $user->id)->where('status', 0)->where('type', 1)->count();
            if($payment >= 1) {
                return redirect()->route('payments')->with('info', 'Existem mensalidades em aberto!');
            }
        }

        return $next($request);
    }
}
