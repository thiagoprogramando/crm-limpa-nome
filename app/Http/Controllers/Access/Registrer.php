<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;

use App\Models\User;

use GuzzleHttp\Client;
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
            'terms'     => 'accepted',
        ], [
            'name.required'     => 'É necessário informar o seu Nome!',
            'email.unique'      => 'Esse email já está em uso!',
            'cpfcnpj.unique'    => 'Esse CPF ou CNPJ já esta em uso!',
            'terms.accepted'    => 'É necessário aceitar os termos de uso!',
        ]);

        $user = new User();
        $user->name     = $request->name;
        $user->cpfcnpj  = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->phone    = preg_replace('/\D/', '', $request->phone);
        $user->email    = $request->email;
        $password       = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->password = bcrypt($password);
        
        if(!empty($request->filiate)) {

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

            $this->sendActive($user->id);

            if (Auth::attempt(['email' => $user->email, 'password' => $password])) {
                return redirect()->route('app');
            } else {
                return redirect()->route('login')->with('success', 'Bem-vindo(a)! Faça Login para acessar o sistema.');
            }
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function sendActive($id) {

        $user = User::find($id);
        if($user) {

            $message =  "Olá, {$user->name}! 😊\r\n\r\n"
                        . "Seja bem-vindo(a) à G7! \r\n\r\n"
                        . "Estamos muito felizes em tê-lo(a) conosco. Seu acesso foi criado com sucesso. Aqui estão seus dados de login para que você possa começar a aproveitar todos os benefícios: \r\n\r\n"
                        . "Acesse: https://app.grupo7assessoria.com/\r\n"
                        . "E-mail: {$user->email}\r\n"
                        . "Senha: *CPF/CNPJ (Sem caracteres, apenas letras)* \r\n"
                        . "proveite a sua jornada com a gente e tenha um ótimo dia! \r\n\r\n";
            $this->sendWhatsapp(
                "https://app.grupo7assessoria.com/",
                $message,
                $user->phone,
                $user->api_token_zapapi
            );

            return true;
        }

    }

    private function sendWhatsapp($link, $message, $phone, $token = null) {

        $client = new Client();
        $url = $token ?: 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-link';
    
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
                ],
                'json' => [
                    'phone'           => '55' . $phone,
                    'message'         => $message,
                    'image'           => env('APP_URL_LOGO'),
                    'linkUrl'         => $link,
                    'title'           => 'Boas vindas',
                    'linkDescription' => 'Boas vindas',
                ],
                'verify' => false
            ]);
    
            if ($response->getStatusCode() == 200) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

}
