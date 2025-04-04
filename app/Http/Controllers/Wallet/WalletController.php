<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller {

    public function wallet() {

        $assas        = new AssasController();
        $balance      = $assas->balance() ?? 0;
        $accumulated  = $assas->accumulated() ?? 0;
        $statistics   = $assas->statistics() ?? 0;
        $extracts     = $assas->receivable() ?? [];
        
        
        return view('app.Finance.Wallet.wallet', [
            'balance'       => $balance, 
            'statistics'    => $statistics, 
            'accumulated'   => $accumulated,
            'extracts'      => $extracts
        ]);
    }

    public function withdrawSend(Request $request) {

        $password = $request->password;    
        if (Hash::check($password, Auth::user()->passowrd)) {

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

    public function IntegrateWallet() {
        
        if (!Auth::check()) {
            return redirect()->back()->with('info', 'Verifique seus dados e tente novamente!');
        }
    
        return view('app.Finance.Wallet.Integrate-wallet');
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }
}
