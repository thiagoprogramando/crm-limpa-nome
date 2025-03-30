<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;

use App\Models\Code;
use App\Models\User;

use Illuminate\Http\Request;

use GuzzleHttp\Client;

class ForgoutController extends Controller {
    
    public function forgout($code = null) {

        return view('forgout', ['code' => $code]);
    }

    public function forgoutPassword(Request $request) {

        if($request->password != $request->repeat_password) {
            return redirect()->back()->with('info', 'Senhas não coincidem!');
        }

        $code = Code::where('code', $request->code)->first();
        if(!$code) {
            return redirect()->back()->with('error', 'Código inválido!');
        }

        $code->status = 1;
        $code->save();

        $user = User::find($code->user_id);
        if(!$user) {
            return redirect()->back()->with('error', 'Dados do usuário não encontrados!');
        }

        $user->password = bcrypt($request->password);
        if($user->save()) {
            return redirect()->route('login')->with('success', 'Senha atualizada com sucesso!');
        }
    }

    public function sendCodePassword(Request $request) {

        $user = User::where('cpfcnpj', preg_replace('/\D/', '', $request->cpfcnpj))->first();
        if($user) {

            $code          = new Code();
            $code->code    = $this->generateCode();
            $code->id_user = $user->id;

            if($code->save()) {
                // if($this->sendCode($user->phone, $code->code)) {
                //     return redirect()->route('forgout', ['code' => 1])->with('success', 'Verifique seu Whatsapp, enviamos o código de redefinição!');
                // }

                return redirect()->back()->with('error', 'Não foi possível enviar o código, tente novamente!');
            }
        }

        return redirect()->back()->with('error', 'CPF ou CNPJ não pertece a nenhuma conta associada!');
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
    
    // private function sendCode($phone, $code) {

    //     $client = new Client();

    //     $url = env('API_TOKEN_EVOLUTION').'080723487B44-4F6D-B89F-FF69F96F81F5';
    //     try {

    //         $response = $client->post($url, [
    //             'headers' => [
    //                 'Content-Type'  => 'application/json',
    //                 'Accept'        => 'application/json',
    //                 'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
    //             ],
    //             'json' => [
    //                 'number'  => '55' . $phone,
    //                 'text'    => "Prezado(a) parceiro, segue seu *código* de redefinição: ".$code."\r\n",
    //             ],
    //             'verify' => false
    //         ]);

    //         return true;
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    // }
}
