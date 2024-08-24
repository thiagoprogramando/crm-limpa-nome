<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DefaultController extends Controller {
    
    public function sendWhatsapp($id) {

        $invoice = Invoice::where('id', $id)->first();
        if(!$invoice) {
            return redirect()->back()->with('error', 'Não encontramos dados da Fatura!'); 
        }

        if($invoice->status == 1) {
            return redirect()->back()->with('error', 'Fatura já confirmada!'); 
        }

        if($invoice->due_date > now()) {
            return redirect()->back()->with('error', 'Essa fatura não venceu ainda!'); 
        }        

        $user = User::where('id', $invoice->id_user)->first();
        $client = new Client();

        $sale = Sale::find($invoice->id_sale);
        if($sale) {

            $seller = User::find($sale->id_seller);
            if($seller) {
                $token = $seller->api_token_zapapi;
            } else {
                $token = null;
            }
        } else {
            $token = null;
        }

        $url = $token ?: 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-link';
        try {

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
                ],
                'json' => [
                    'phone'           => '55' . $user->phone,
                    'message'         => "Olá, ".$user->name."! \r\n\r\nVocê possui uma fatura em atraso, mas não se preocupe estamos enviando para você a cobrança N° ".$invoice->num." da sua compra do serviço Limpa Nome.\r\n\r\nPara realizar o pagamento basta clicar no Link abaixo:",
                    'image'           => env('APP_URL_LOGO'),
                    'linkUrl'         => $invoice->url_payment,
                    'title'           => 'Cobrança em atraso',
                    'linkDescription' => 'Link para Pagamento Digital',
                ],
                'verify' => false
            ]);

            return redirect()->back()->with('success', 'Cobrança enviada via WhatsApp!'); 
        } catch (\Exception $e) {
            return redirect()->back()->with('erro', 'Problemas ao enviar cobrança via Whatsapp!'); 
        }

    }
}
