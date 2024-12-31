<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;

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

        if (is_null($sale->id_payment)) {
            return redirect()->back()->with('info', 'Vendas (Manuais) não são integradas aos contratos digitais!');
        }

        if (!empty($sale->url_contract)) {

            $message = "{$sale->user->name}, segue seu contrato de adesão ao serviço de limpa nome com nossa assessoria.\r\n\r\n".
                        "ASSINAR O CONTRATO CLICANDO NO LINK 👇🏼✍🏼\r\n".
                        " ⚠ Salva o contato se não tiver aparecendo o link.\r\n";

            $this->sendWhatsapp($sale->url_contract, $message, $sale->user->phone, $sale->seller->api_token_zapapi);
            return redirect()->back()->with('success', 'Contrato enviado para o Cliente!');
        }

        $product = Product::find($sale->id_product);
        if (!$product || empty($product->contract)) {
            return redirect()->back()->with('info', 'Produto indisponível/ou sem contrato associado!');
        }

        $document = $this->sendContract($sale->user->id, $product->contract, $sale->value, $sale->id_payment);
        if ($document['token']) {

            $sale->fill([
                'token_contract'  => $document['token'],
                'url_contract'    => $document['signers'][0]['sign_url'],
                'status_contract' => 2,
            ]);

            if ($sale->save()) {
                $this->sendWhatsapp($document['signers'][0]['sign_url'], "Prezado(a) ".$sale->user->name.", segue seu contrato de adesão ao serviço de limpa nome com nossa assessoria. \r\n\r\n ⚠ Se não estiver aparecendo o link, Salva o nosso contato que aparecerá! \r\n\r\n\r\n ASSINAR O CONTRATO TOCANDO NO LINK 👇🏼✍🏼 \r\n", $sale->user->phone, $sale->seller->api_token_zapapi); 
                return redirect()->back()->with('success', 'Sucesso! O contrato foi enviado para o cliente via WhatsApp.');
            }
        } else {
            return redirect()->back()->with('error', 'Foram encontrados problemas ao gerar contrato do cliente, contate o suporte!');
        }
    }

    private function sendContract($user, $contract, $value, $payment) {

        $payment = Payment::find($payment);
        if (!$payment) {
            return false;
        }
    
        $user = User::find($user);
        if (!$user) {
            return false;
        }
    
        $client = new Client();
    
        $url = env('API_URL_ZAPSIGN') . 'api/v1/models/create-doc/';
    
        $currentDate    = Carbon::now();
        $day            = $currentDate->format('d');
        $month          = $currentDate->format('m');
    
        switch ($month) {
            case '01':
                $monthName = 'Janeiro';
                break;
            case '02':
                $monthName = 'Fevereiro';
                break;
            case '03':
                $monthName = 'Março';
                break;
            case '04':
                $monthName = 'Abril';
                break;
            case '05':
                $monthName = 'Maio';
                break;
            case '06':
                $monthName = 'Junho';
                break;
            case '07':
                $monthName = 'Julho';
                break;
            case '08':
                $monthName = 'Agosto';
                break;
            case '09':
                $monthName = 'Setembro';
                break;
            case '10':
                $monthName = 'Outubro';
                break;
            case '11':
                $monthName = 'Novembro';
                break;
            case '12':
                $monthName = 'Dezembro';
                break;
            default:
                $monthName = 'Mês Desconhecido';
                break;
        }

        $year = $currentDate->format('Y');
    
        $parentData = [];
        if ($user->white_label_contract == 1 || $user->parent->white_label_contract == 1) {
            $parentData = [
                [
                    "de"    => "EMPRESA_NOME",
                    "para"  =>  $user->company_name ?? $user->parent->name
                ],
                [
                    "de"    => "EMPRESA_CPFCNPJ",
                    "para"  => $user->company_cpfcnpj ?? $user->parent->cpfcnpj
                ],
                [
                    "de"    => 'EMPRESA_ENDERECO',
                    "para"  => $user->company_address ?? $user->parent->address()
                ],
                [
                    "de"    => 'EMPRESA_EMAIL',
                    "para"  => $user->company_email ?? $user->parent->email
                ],
            ];
        } else {
            $parentData = [
                [
                    "de"    => "EMPRESA_NOME",
                    "para"  => "AMPAY SOLUÇÕES"
                ],
                [
                    "de"    => "EMPRESA_CPFCNPJ",
                    "para"  => "53.912.699/001-22"
                ],
                [
                    "de"    => "EMPRESA_ENDERECO",
                    "para"  => "Rua José Versolato, 101 - 12° Andar Centro São Bernado do Campo 09750-730"
                ],
                [
                    "de"    => "EMPRESA_EMAIL",
                    "para"  => "suporte@ampay.com.br"
                ],
            ];
        }
    
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer '.env('API_TOKEN_ZAPSIGN'),
                ],
                'json' => [
                    "template_id"       => $contract,
                    "signer_name"       => $user->name,
                    "signer_email"      => $user->email,
                    "folder_path"       => 'Limpa Nome '.$day.'-'.$monthName,
                    "data"  => array_merge(
                        [
                            [
                                "de"    => "CLIENTE_NOME",
                                "para"  => $user->name
                            ],
                            [
                                "de"    => "CLIENTE_RG",
                                "para"  => $user->rg
                            ],
                            [
                                "de"    => "CLIENTE_EMAIL",
                                "para"  => $user->email
                            ],
                            [
                                "de"    => "CLIENTE_PHONE",
                                "para"  => $user->phone
                            ],
                            [
                                "de"    => "CLIENTE_CPFCNPJ",
                                "para"  => $user->cpfcnpj
                            ],
                            [
                                "de"    => "CLIENTE_DATANASCIMENTO",
                                "para"  => Carbon::createFromFormat('Y-m-d', $user->birth_date)->format('d/m/Y')
                            ],
                            [
                                "de"    => "CLIENTE_ENDERECO",
                                "para"  => $user->address
                            ],
                            [
                                "de"    => "VALOR",
                                "para"  =>  $value
                            ],
                            [
                                "de"    => "FORMADEPAGAMENTO",
                                "para"  => $payment->methodLabel().' em '.$payment->installments.'x'
                            ],
                            [
                                "de"    => "DIA",
                                "para"  => $day
                            ],
                            [
                                "de"    => "MES",
                                "para"  => $monthName
                            ],
                            [
                                "de"    => "ANO",
                                "para"  => $year
                            ],
                        ],
                        $parentData
                    ),
                ],
                'verify' => false      
            ]);
    
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return $e->getMessage();
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

        $sale = Sale::with(['product', 'user', 'seller', 'paymentMethod'])->find($saleId);
        if (!$sale) {
            return redirect()->route('login.cliente')->with('info', 'Não foi possível localizar os dados da venda! Tente novamente mais tarde.');
        }

        if (empty($sale->product->contract_subject)) {
            return redirect()->route('login.cliente')->with('info', 'Contrato indisponível para venda N° '.$sale->id);
        }

        $contractContent = Str::of($sale->product->contract_subject)
            ->replace('{CLIENT_NAME}'       , $sale->user->name ?? 'N/A')
            ->replace('{CLIENT_CPFCNPJ}'    , $sale->user->cpfcnpj ?? 'N/A')
            ->replace('{CLIENT_BIRTH_DATE}' , $sale->user->birth_date 
                    ? Carbon::parse($sale->user->birth_date)->format('d/m/Y') 
                    : 'N/A')
            ->replace('{SELLER_NAME}'    , $sale->seller->name ?? 'N/A')
            ->replace('{SELLER_CPFCNPJ}' , $sale->seller->cpfcnpj ?? 'N/A')
            ->replace('{SELLER_ADDRESS}' , $sale->seller->address() ?? 'N/A')
            ->replace('{SELLER_EMAIL}'   , $sale->seller->email ?? 'N/A')
            ->replace('{SALE_VALUE}'     , $sale->value 
                    ? 'R$ ' . number_format($sale->value, 2, ',', '.') 
                    : '---')
            ->replace('{SALE_METHOD}'    , $sale->paymentMethod->methodLabel().' em '.$sale->paymentMethod->installments.'x')
            ->replace('{SALE_DATE}', date('d').'/'.date('m').'/'.date('Y'));

        return view('contract.contract', [
            'title'             => 'Contrato de serviço - ' . $sale->product->name,
            'contractContent'   => $contractContent,
            'sale'              => $sale,
        ]);
    }
}
