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

        // $user = Auth::user();
        // if ($user && $user->type !== 4) {
            
        //     if ($user->wallet === null || $user->api_key === null) {
        //         return redirect()->route('profile')->with('info', 'Complete seus dados para acessar todos os módulos!');
        //     }

        //     if ($user->status !== 1) {

        //         $assas      = new AssasController();
        //         $documents  = $assas->myDocuments();

        //         if ($documents && is_array($documents)) {
  
        //             $allApproved = collect($documents)->every(fn($doc) => $doc['status'] === 'APPROVED');
        //             if ($allApproved) {

        //                 $user->status = 1;
        //                 $user->save();
            
        //                 return redirect()->route('app')->with('success', 'Bem-vindo(a), Acesso liberado!');
        //             }
        //         }

        //         return redirect()->route('profile')->with('info', 'É necessário finalizar sua documentação!');
        //     }

        //     $payment = Invoice::where('id_user', $user->id)->where('status', 0)->where('type', 1)->count();
        //     if($payment >= 1) {
        //         return redirect()->route('payments')->with('info', 'Existem mensalidades em aberto!');
        //     }
        // }

        $user = Auth::user();
        if ($user && $user->type !== 4) {
            
            if ($user->name == null || $user->cpfcnpj == null || $user->birth_date == null || $user->phone == null) {
                return redirect()->route('profile')->with('info', 'Complete seus dados para acessar todos os módulos!');
            }

            $payment = Invoice::where('id_user', $user->id)->where('status', 0)->where('type', 1)->count();
            if($payment >= 1) {
                return redirect()->route('payments')->with('info', 'Existem mensalidades em aberto!');
            }
        }

        return $next($request);
    }
}
