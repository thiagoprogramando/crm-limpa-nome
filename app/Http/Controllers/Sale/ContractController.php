<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ContractController extends Controller {
    
    public function createContract($sale) {

        $sale = Sale::find($sale);
        if (!$sale) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da venda! Tente novamente mais tarde.');
        }

        if (empty($sale->user->name) || empty($sale->user->cpfcnpj) || empty($sale->user->birth_date)) {
            return redirect()->back()->with('info', 'Cliente não está com os dados completos/ou é uma Venda Direta Associação!');
        }

        $message = "{$sale->user->name}, segue seu contrato de adesão ao serviço de limpa nome com nossa assessoria.\r\n\r\n".
                    "ASSINAR O CONTRATO CLICANDO NO LINK 👇🏼✍🏼\r\n".
                    " ⚠ Salva o contato se não tiver aparecendo o link.\r\n";

        if ($this->sendWhatsapp(env('APP_URL').'view-contract/'.$sale->id, $message, $sale->user->phone, $sale->seller->api_token_zapapi)) {
            return redirect()->back()->with('success', 'Contrato enviado para o Cliente!');
        }
        
        return redirect()->back()->with('info', 'Não foi possível enviar o Contrato, tente novamente mais tarde!');
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

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function previewContract($saleId)  {

        $sale = Sale::with(['product', 'user', 'seller'])->find($saleId);
        if (!$sale) {
            return redirect()->route('login.cliente')->with('info', 'Não foi possível localizar os dados da venda! Tente novamente mais tarde.');
        }

        $invoices = Invoice::where('id_sale', $sale->id)->get();

        if (empty($sale->product->contract_subject)) {
            return redirect()->route('login.cliente')->with('info', 'Contrato indisponível para venda N° '.$sale->id);
        }

        if ($sale->seller->white_label_contract == 1 || ($sale->seller->parent->white_label_contract == 1)) {
            
            $contractContent = Str::of($sale->product->contract_subject)
            ->replace('{CLIENT_NAME}', $sale->user->name ?? 'N/A')
            ->replace('{CLIENT_CPFCNPJ}', $sale->user->cpfcnpj ?? 'N/A')
            ->replace('{CLIENT_BIRTH_DATE}', $sale->user->birth_date 
                ? Carbon::parse($sale->user->birth_date)->format('d/m/Y') 
                : 'N/A')
            ->replace('{SELLER_NAME}', 
                $sale->seller?->company_name ?? $sale->seller?->parent?->company_name ?? 'AMPAY SOLUÇÕES'
            )
            ->replace('{SELLER_CPFCNPJ}', 
                $sale->seller?->company_cpfcnpj ?? $sale->seller?->parent?->company_cpfcnpj ?? '53.912.699/001-22'
            )
            ->replace('{SELLER_ADDRESS}', 
                $sale->seller?->company_address ?? $sale->seller?->parent?->company_address ?? 'Rua José Versolato, 101 - 12° Andar Centro São Bernado do Campo 09750-730'
            )
            ->replace('{SELLER_EMAIL}', 
                $sale->seller?->company_email ?? $sale->seller?->parent?->company_email ?? 'suporte@ampay.com.br'
            )
            ->replace('{SALE_VALUE}', 
                $sale->value ? number_format($sale->value, 2, ',', '.') : '---'
            )
            ->replace('{SALE_METHOD}', 
                $sale->paymentMethod() . ' em ' . $sale->installments . 'x'
            )
            ->replace('{SALE_DATE}', date('d') . '/' . date('m') . '/' . date('Y'));
        } else {
            
            $contractContent = Str::of($sale->product->contract_subject)
            ->replace('{CLIENT_NAME}'       , $sale->user->name ?? 'N/A')
            ->replace('{CLIENT_CPFCNPJ}'    , $sale->user->cpfcnpj ?? 'N/A')
            ->replace('{CLIENT_BIRTH_DATE}' , $sale->user->birth_date 
                    ? Carbon::parse($sale->user->birth_date)->format('d/m/Y') 
                    : 'N/A')
            ->replace('{SELLER_NAME}'    , 'AMPAY SOLUÇÕES')
            ->replace('{SELLER_CPFCNPJ}' , '53.912.699/001-22')
            ->replace('{SELLER_ADDRESS}' , 'Rua José Versolato, 101 - 12° Andar Centro São Bernado do Campo 09750-730')
            ->replace('{SELLER_EMAIL}'   , 'suporte@ampay.com.br')
            ->replace('{SALE_VALUE}'     , $sale->value 
                    ? number_format($sale->value, 2, ',', '.') 
                    : '---')
            ->replace('{SALE_METHOD}'    , $sale->paymentMethod().' em '.$sale->installments.'x')
            ->replace('{SALE_DATE}', date('d').'/'.date('m').'/'.date('Y'));
        }

        return view('app.Contract.contract', [
            'title'           => 'Contrato de serviço - ' . $sale->product->name,
            'contractContent' => $contractContent,
            'sale'            => $sale,
            'invoices'        => $invoices
        ]);
    }

    public function signSale(Request $request) {

        $sale = Sale::find($request->id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Contrato não encontrado na base de dados!'], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $sale->sign_contract    = $request->sign;
        $sale->status_contract  = 1;
        if ($sale->save()) {
            return response()->json(['success' => true, 'message' => 'Contrato Assinado com sucesso!'], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['success' => false, 'message' => ''], 403, [], JSON_UNESCAPED_UNICODE);
    }
}
