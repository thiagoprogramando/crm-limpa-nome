<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Registrer extends Controller {
     
    public function index($id = null) {

        return view('registrer', ['filiate' => $id]);
    }

    public function registrerUser(Request $request) {

        if(empty($request->terms)) {
            return redirect()->back()->with('error', 'É necessário aceitar os termos de uso!');
        }

        $user = new User();
        $user->name = $request->name;
        $user->cpfcnpj = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        
        if($request->filiate) {
            $filiates = User::where('filiate', $request->filiate)->count();
            if($filiates >= 1) {
                return redirect()->back()->with('error', 'Link de indicação expirado!');
            }

            $user->filiate = $request->filiate;
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
