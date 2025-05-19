<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccount {

    public function handle(Request $request, Closure $next): Response {

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($user && ($user->type !== 4 || $user->type !== 3)) {
            if ($user->name == null || $user->cpfcnpj == null || $user->birth_date == null || $user->phone == null) {
                return redirect()->route('profile')->with('info', 'Complete seus dados para acessar todos os módulos!');
            }

            $months = Invoice::where('user_id', $user->id)->where('status', 0)->where('type', 1)->count();
            if($months >= 1) {
                return redirect()->route('payments')->with('info', 'Você precisa renovar sua Assinatura!');
            }
        }

        return $next($request);
    }
}
