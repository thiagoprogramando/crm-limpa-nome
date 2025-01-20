<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;

use App\Models\Code;
use App\Models\User;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Forgout extends Controller {
    
    public function forgout($code = null) {

        return view('forgout', ['code' => $code]);
    }

    public function updatePassword(Request $request) {

        if($request->password != $request->repeat_password) {
            return redirect()->back()->with('error', 'Senhas diferentes!');
        }

        $code = Code::where('code', $request->code)->first();
        if(!$code) {
            return redirect()->back()->with('error', 'Código inválido!');
        }

        $code->data_used = now();
        $code->status = 1;
        $code->save();

        $user = User::find($code->id_user);
        if(!$user) {
            return redirect()->back()->with('error', 'Dados do usuário não encontrados!');
        }

        $user->password = bcrypt($request->password);
        if($user->save()) {
            return redirect()->route('login')->with('success', 'Senha atualizada com sucesso!');
        }

    }

    public function sendCodePassword(Request $request) {

        $user = User::where('email', $request->email)->first();
        if($user) {

            $code                   = new Code();
            $code->code             = $this->generateCode();
            $code->data_generate    = now();
            $code->id_user          = $user->id;
            if($code->save()) {
                if($this->sendCode($user->phone, $code->code)) {
                    return redirect()->route('forgout', ['code' => 1])->with('success', 'Código enviado para o número cadastro!');
                }

                return redirect()->back()->with('error', 'Não foi possível enviar o código, tente novamente!');
            }
        }

        return redirect()->back()->with('error', 'Email não pertece a nenhuma conta associada!');
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
    
    private function sendCode($phone, $code) {

        $client = new Client();

        $url = 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-text';
        try {

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
                ],
                'json' => [
                    'phone'           => '55' . $phone,
                    'message'         => "Prezado(a) parceiro, segue seu *código* de redefinição: ".$code."\r\n",
                ],
                'verify' => false
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
}
