<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller {
    
    public function wallet() {

        if (!empty(Auth::user()->token_wallet) && !empty(Auth::user()->token_wallet)) {
            $assas        = new AssasController();
            $balance      = $assas->balance() ?? 0;
            $statistics   = $assas->statistics();
            $extracts     = $assas->receivable();
        }

        return view('app.User.wallet', [
            'balance'       => $balance ?? 0, 
            'statistics'    => $statistics ?? [], 
            'extracts'      => $extracts ?? [], 
            'cashback'      => Auth::user()->wallet,
            'extractsCashback' => Auth::user()->extracts()->orderBy('id', 'desc')->paginate(30)
        ]);
    }

    public function withdrawSend(Request $request) {

        $password = $request->password;    
        if (Hash::check($password, auth()->user()->password)) {

            if(empty($request->key) || empty($request->value) || empty($request->type)) {
                return redirect()->back()->with('error', 'Dados incompletos!');
            }
    
            $assas = new AssasController();
            $withdraw = $assas->withdrawSend($request->key, $this->formatarValor($request->value), $request->type);
    
            if($withdraw['success']) {
                return redirect()->back()->with('success', $withdraw['message']);
            }
    
            return redirect()->back()->with('error', 'Não foi possível realizar saque: '.$withdraw['message']);
        }

        return redirect()->back()->with('error', 'Senha inválida!');
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }

}
