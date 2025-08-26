<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\User;

use Carbon\Carbon;
use GuzzleHttp\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurrenceController extends Controller {
    
    public function index(Request $request, $status) {

        $query = User::where('status', $status)->where('type', 2);
        $users = $query->paginate(30);

        $total = $users->sum(function($user) use ($status) {
            $invoice = $user->invoices()
                ->where('type', 1)
                ->where('status', $status)
                ->latest('created_at')
                ->first();

            return $invoice ? $invoice->value : 0;
        });

        return view('app.Finance.Recurrence.list-recurrences', [
            'users'  => $users,
            'total'  => $total,
            'status' => $status == 1 ? 'Ativos' : 'Inativos'
        ]);
    }

    public function notification($id) {

        $user = User::find($id);
        if($user) {

            $message =  "Olá, {$user->name},\r\n\r\n"
                        . "Sua conta em nossa plataforma ainda não foi ativada. Para continuar a ganhar comissões e aproveitar nossos benefícios, ative sua conta até ". Carbon::now()->addDays(30)->format('d/m/Y') ." \r\n\r\n"
                        . "*Instruções para ativação:*\r\n\r\n"
                        . "Acesse: ".env('APP_URL')."\r\n"
                        . "Faça login com seu e-mail e CPF. \r\n"
                        . "Complete a ativação \r\n"
                        . "Após a data limite, o acesso será desativado permanentemente. \r\n\r\n"
                        . "Precisa de ajuda? Estamos aqui para você!\r\n\r\n";
            $this->sendWhatsapp(
                env('APP_URL'),
                $message,
                $user->phone,
                $user->token_whatsapp
            );

            return redirect()->back()->with('success', 'mensagem enviada com sucesso!');
        }

        return redirect()->back()->with('error', 'Usuário não encontrado!');
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
                    'title'           => 'Assinatura de Documento',
                    'linkDescription' => 'Link para Assinatura Digital',
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
