<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Assas\AssasController;
use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckMonthly {

    public function handle(Request $request, Closure $next): Response {
        try {
            if (Auth::check()) {
                $user = Auth::user();

                if ($user && in_array($user->status, [1, 2]) && !in_array($user->type, [1, 4])) {
                    $monthly = Invoice::where('user_id', $user->id)
                        ->where('status', 1)
                        ->where('type', 1)
                        ->latest('created_at')
                        ->first();

                    if (!$monthly || ($monthly->created_at && $monthly->created_at->lte(now()->subDays(30)->startOfDay()))) {

                        $assas = new AssasController(); 
                        $createMonthly = $assas->createMonthly($user->id);
                        if (isset($createMonthly['success'])) {
                            return redirect()->route('payments')->with('info', $createMonthly['message']);
                        }

                        return redirect()->route('payments')->with('error', 'Verifique seus dados e tente novamente!');
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Erro no middleware Monthly: '.$e->getMessage());
            return redirect()->route('payments')->with('error', 'Erro ao verificar mensalidade. Contate o suporte.');
        }

        return $next($request);
    }
}
