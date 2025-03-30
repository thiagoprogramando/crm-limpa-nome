<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Assas\AssasController;
use App\Models\Invoice;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccount {

    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if ($user && $user->type !== 4 && $user->type !== 1) {
            
            if ($user->name == null || $user->cpfcnpj == null || $user->birth_date == null || $user->phone == null) {
                return redirect()->route('profile')->with('info', 'Complete seus dados para acessar todos os módulos!');
            }

            $payment = Invoice::where('user_id', $user->id)->where('status', 0)->where('type', 1)->count();
            if($payment >= 1) {
                return redirect()->route('payments')->with('info', 'Existem mensalidades em aberto!');
            }
        }

        return $next($request);
    }
}
