<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class SaleController extends Controller {

    public function create($id) {

        $product = Product::find($id);
        $payments = Payment::where('id_product', $product->id)->get();

        return view('app.Sale.create', ['product' => $product, 'payments' => $payments]);
    }

    public function createSale(Request $request) {

        $user = $this->createUser($request->name, $request->email, $request->cpfcnpj, $request->phone, $request->postal_code, $request->address, $request->complement, $request->city, $request->state, $request->num);
        if($user != false) {

            $product = Product::where('id', $request->product)->first();
            if(!$product) {
                return redirect()->back()->with('error', 'Produto não disponível!');
            }

            if($this->formatarValor($request->value) < $product->value_min) {
                return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$product->value_min.'!');
            }

            if($this->formatarValor($request->value) > $product->value_max && $product->value_max > 0) {
                return redirect()->back()->with('error', 'O valor max de venda é: R$ '.$product->value_max.'!');
            }

            $method = Payment::where('id', $request->payment)->first();
            if(!$method) {
                return redirect()->back()->with('error', 'Forma de pagamento não disponível!');
            }

            $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
            if(!$list) {
                return redirect()->back()->with('error', 'Não há uma lista disponível para vendas!');
            }
        
            $sale               = new Sale();
            $sale->id_client    = $user->id;
            $sale->id_product   = $request->product;
            $sale->id_list      = 1;
            $sale->id_payment   = $method->id;
            $sale->id_seller    = Auth::id();

            $sale->payment      = $method->method;
            $sale->installments = $method->installments;
            $sale->status       = 0;

            $sale->value        = $this->formatarValor($request->value);
            $sale->commission   = ($this->formatarValor($request->value) - $product->value_cost) - $product->value_rate;

            if(!empty($product->contract)) {

                $document = $this->sendContract($user->id, $product->contract, $request->value, $request->payment);
                if($document['token']) {

                    $sale->token_contract = $document['token'];
                    $sale->url_contract   = $document['signers'][0]['sign_url'];
                    $this->sendWhatsapp($document['signers'][0]['sign_url'], "Prezado Cliente, segue seu *contrato de adesão* ao produto da ".env('APP_NAME')." Assessoria: \r\n ASSINAR O CONTRATO CLICANDO NO LINK 👇🏼✍🏼 \r \n ⚠ Salva o contato se não tiver aparecendo o link.", $user->phone);

                    if($sale->save()) {
                        return redirect()->back()->with('success', 'Sucesso! O contrato foi enviado para o cliente via WhatsApp.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Foram encontrados problemas ao gerar contrato do cliente, contate o suporte!');
                }
            } else {

                $sale->save();

                $assas = new AssasController();
                $invoice = $assas->createSalePayment($sale->id);
                if($invoice) {
                    return redirect()->back()->with('success', 'Sucesso! Os dados de pagamento foram enviados para o Cliente!');
                }
            }

            return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }
    }

    private function createUser($name, $email, $cpfcnpj, $phone, $postal_code = null, $address = null, $complement = null, $city = null, $state = null, $num = null) {

        $user = User::where('cpfcnpj', $cpfcnpj)->first();
        if($user) {
            return $user;
        }
        
        $user               = new User();
        $user->name         = $name;
        $user->email        = $email;
        $user->cpfcnpj      = $cpfcnpj;
        $user->password     = bcrypt($cpfcnpj);
        $user->phone        = $phone;
        $user->postal_code  = $postal_code;
        $user->address      = $address;
        $user->complement   = $complement;
        $user->city         = $city;
        $user->state        = $state;
        $user->num          = $num;
        if($user->save()) {
            return $user;
        }

        return false;
    }

    private function sendContract($user, $contract, $value, $payment) {

        $payment = Payment::find($payment);
        if(!$payment) {
            return false;
        }

        $user = User::find($user);
        if(!$user) {
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
                    "data"  => [
                        [
                            "de"    => "NOME",
                            "para"  => $user->name
                        ],
                        [
                            "de"    => "RG",
                            "para"  => $user->rg
                        ],
                        [
                            "de"    => "CPFCNPJ",
                            "para"  => $user->cpfcnpj
                        ],
                        [
                            "de"    => "DATANASCIMENTO",
                            "para"  => Carbon::createFromFormat('Y-m-d', $user->birth_date)->format('d/m/Y')
                        ],
                        [
                            "de"    => "ENDERECO",
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
                ],
                'verify' => false      
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    private function sendWhatsapp($link, $message, $phone) {

        $client = new Client();

        $url = 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-link';
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

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }

    public function manager(Request $request) {

        $sales = Sale::orderBy('created_at', 'desc')->get();
        return view('app.Sale.manager', ['sales' => $sales]);
    }

    public function viewSale($id) {

        $sale = Sale::find($id);
        $invoices = Invoice::where('id_sale', $sale->id)->get();

        return view('app.Sale.view', ['sale' => $sale, 'invoices' => $invoices]);
    }

    public function deleteSale(Request $request) {

        $sale = Sale::find($request->id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Não encontramos dados da venda!');
        }

        Invoice::where('id_sale', $sale->id)->delete();

        switch ($sale->status) {
            case 0:
            case 3:
            case 4:
                $sale->delete();
                return redirect()->back()->with('success', 'Dados da venda eliminados do sistema!');
                break;
            case 1:
                return redirect()->back()->with('error', 'Existem pagamentos atribuídos ao N° da venda, contate o suporte!');
                break;
            case 2:
                return redirect()->back()->with('error', 'Existem contratos atribuídos ao N° da venda, contate o suporte!');
                break;
            default:
                return redirect()->back()->with('error', 'Erro desconhecido ao excluir a venda!');
                break;
        }
    }

    public function default() {

        $user = Auth::user();
        if ($user->type == 1) {
            $invoices = Invoice::where('due_date', '<', now())->where('status', 0)->get();
        } else {
            $invoices = Invoice::whereHas('sale', function ($query) use ($user) { $query->where('id_seller', $user->id); })->where('due_date', '<', now())->where('status', 0)->get();
        }
    
        return view('app.Sale.default', ['invoices' => $invoices]);
    }    

}