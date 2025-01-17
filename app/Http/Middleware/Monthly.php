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

        if (Auth::check()) {
            
            $user = Auth::user();
            if (($user && ($user->status == 1 || $user->status == 2)) && ($user->type <> 1 || $user->type <> 4)) {
        
                $monthly = Invoice::where('id_user', $user->id)->where('status', 1)->where('type', 1)->latest('created_at')->first();
                if (!$monthly || $monthly->created_at->lte(now()->subDays(30)->startOfDay())) {
                    
                    $assas = new AssasController();
                    $createMonthly = $assas->createMonthly($user->id);
                    if ($createMonthly) {
                        return redirect()->route('payments')->with('error', 'Existem mensalidades em aberto!');
                    }
                }
            }
        }        

        return $next($request);
    }
}
