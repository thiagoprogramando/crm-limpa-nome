<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller {
    
    public function index() {
        
        return view('welcome');
    }

    public function logon(Request $request) {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);
        if (Auth::attempt($credentials)) {
            return redirect()->route('app');
        } else {
            return redirect()->back()->withInput($request->only('email'))->with('error', 'Credenciais inválidas!');
        }
    }

    public function logout() {

        Auth::logout();
        return redirect()->route('login');
    }

}
