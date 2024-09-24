<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller {
    
    public function login() {

        return view('client.login');
    }

    public function logon(Request $request) {

        $user = User::where('cpfcnpj', $request->cpfcnpj)->first();
        if(!$user) {
            return redirect()->back()->with('error', 'Não foram encontrado dados para o CPF ou CNPJ!');
        }

        Auth::login($user);
        return redirect()->route('app.cliente');
    }
}
