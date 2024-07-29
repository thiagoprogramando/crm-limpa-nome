<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Registrer extends Controller {
     
    public function index($id = null, $type = null) {

        return view('registrer', ['id' => $id, 'type' => $type]);
    }

    public function registrerUser(Request $request) {


        $validator = $request->validate([
            'name'      => 'required',
            'email'     => 'required|unique:users,email',
            'cpfcnpj'   => 'required|unique:users,cpfcnpj',
            'password'  => 'required',
            'terms'     => 'accepted',
        ], [
            'name.required'     => 'É necessário informar o seu Nome!',
            'email.unique'      => 'Esse email já está em uso!',
            'cpfcnpj.unique'    => 'Esse CPF ou CNPJ já esta em uso!',
            'password.required' => 'É necessário informar uma senha!!',
            'terms.accepted'    => 'É necessário aceitar os termos de uso!',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->cpfcnpj = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        
        if($request->filiate) {

            $user->filiate = $request->filiate;

            if($request->type == 6) {
                
                $rede = User::find($request->filiate);
                if($rede && !empty($rede->api_key) && !empty($rede->wallet)) {
                    $user->wallet               = $rede->wallet;
                    $user->api_key              = $rede->api_key;
                    $user->api_token_zapapi     = $rede->api_token_zapapi;
                    $user->status               = 1;
                    $user->type                 = 6;
                } else {
                    return redirect()->back()->with('error', 'Escritório não disponível! Contate o seu indicador.');
                }
            }
        }

        if($user->save()) {

            $credentials = $request->only(['email', 'password']);
            if (Auth::attempt($credentials)) {
                return redirect()->route('app');
            } else {
                return redirect()->route('index')->with('success', 'Bem-vindo(a)! Faça Login para acessar o sistema.');
            }
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

}
