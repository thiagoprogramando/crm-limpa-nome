<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Mail\Forgout;
use App\Models\Code;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForgoutController extends Controller {
    
    public function forgout($token = null) {

        return view('forgout', [
            'token' => $token
        ]);
    }

    public function forgoutPassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.exists' => 'Email não encontrado! Verifique os dados e tente novamente.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()->withErrors('error', 'Email não encontrado! Verifique os dados e tente novamente.');
        }

        $token = str_shuffle(Str::upper(Str::random(3)) . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT));
        DB::table('password_reset_tokens')->updateOrInsert(
            [
                'email' => $user->email
            ],
            [
                'token'         => $token,
                'created_at'    => Carbon::now(),
            ]
        );
        
        Mail::to($user->email, $user->name)->send(new Forgout([
            'fromName'  => 'Thiago César',
            'fromEmail' => 'suporte@expressoftwareclub.com',
            'toName'    => $user->name,
            'toEmail'   => $user->email,
            'token'     => $token,  
        ]));

        return redirect()->back()->with('success', 'Verifique sua caixa de E-mail, enviamos às instruções para você!');
    }

    public function recoveryPassword(Request $request, $token) {

        $token = DB::table('password_reset_tokens')->where('token', $token)->first();
        if (!$token) {
            return redirect()->back()->withErrors(['token' => 'Código inválido! Verifique os dados e tente novamente.']);
        }

        $user = User::where('email', $token->email)->first();
        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'E-mail não válido! Verifique os dados e tente novamente.']);
        }

        if ($request->password !== $request->password_confirmed) {
            return redirect()->back()->withErrors(['password' => 'As senhas não conferem!']);
        }

        $user->password = bcrypt($request->password);
        if ($user->save()) {
            DB::table('password_reset_tokens')->where('email', $token->email)->delete();
            return redirect()->route('login')->with('success', 'Senha alterada com sucesso! Você já pode acessar sua conta.');
        }

        return redirect()->route('forgout')->withErrors(['general' => 'Não foi possível alterar a senha! Verifique os dados e tente novamente.']);	
    }

    private function generateCode() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        $length = 6;
    
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
    
        return $code;
    }  
}
