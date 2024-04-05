<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Assas\AssasController;
use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Monthly {

    public function handle(Request $request, Closure $next): Response {

        $user = Auth::user();
        if ($user && $user->status == 1) {

            $monthly = Invoice::where('id_user', $user->id)->where('status', 1)->first();
            if (!$monthly || $monthly->created_at < now()->subDays(30)) {
                
                $assas = new AssasController();
                $createMonthly = $assas->createMonthly($user->id);
                if ($createMonthly) {
                    return redirect()->route('payments')->with('error', 'Existem mensalidades em aberto!');
                } else {
                    return redirect()->route('logout')->with('error', 'Não foi possível gerar sua Mensalidade!');
                }
            }
        }

        return $next($request);
    }
}
