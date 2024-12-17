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
use App\Models\Item;
use App\Models\Coupon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class SaleController extends Controller {

    public function myShop() {
        
        $sales = Sale::where('id_client', Auth::id())->get();
        return view('app.Shop.list', ['sales' => $sales]);
    }

    public function myProduct($id) {

        $product = Product::find($id);
        if($product) {

            $itens = Item::where('id_product', $product->id)->get();
            return view('app.Shop.product', [
                'product' => $product,
                'itens'   => $itens
            ]);
        }
        
        return redirect()->back()->with('error', 'Não foram encontrados dados do Produto!');
    }

    public function create($id) {

        $product = Product::find($id);
        $payments = Payment::where('id_product', $product->id)->get();

        return view('app.Sale.create', ['product' => $product, 'payments' => $payments]);
    }

    public function createSale(Request $request) {

        $user = $this->createUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, $request->postal_code, $request->address, $request->complement, $request->city, $request->state, $request->num, $request->id_seller);
        if($user != false) {

            $seller = User::find($request->id_seller);
            if (!$seller) {
                return redirect()->back()->with('error', 'Dados do CONSULTOR DE VENDAS não localizados no sistema!');
            }

            if (($seller && $seller->fixed_cost > 0) && ($this->formatarValor($request->value) < $seller->fixed_cost)) {
                return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$seller->fixed_cost.'!');
            }

            $product = Product::where('id', $request->product)->first();
            if(!$product) {
                return redirect()->back()->with('error', 'Produto não disponível!');
            }

            $baseValue = $this->formatarValor($request->value);
            $discountPercentage = 0;

            if ($request->has('coupon') && !empty($request->coupon)) {

                $coupon = Coupon::where('name', $request->coupon)->first();
                if ($coupon) {
                    if ($coupon->qtd < 1) {
                        return redirect()->back()->with('info', 'CUPOM esgotado!');
                    }
        
                    if (!empty($coupon->expiry_date) && $coupon->expiry_date < now()) {
                        return redirect()->back()->with('info', 'CUPOM expirado!');
                    }
        
                    $discountPercentage = $coupon->percentage;
                    $coupon->qtd -= 1;
                    $coupon->save();
                } else {
                    return redirect()->back()->with('info', 'Nenhum CUPOM encontrado!');
                }
            }

            if (empty($seller->fixed_cost)) {
                if ($this->formatarValor($request->value) < $product->value_min) {
                    return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$product->value_min.'!');
                }
            }

            if ($this->formatarValor($request->value) > $product->value_max && $product->value_max > 0) {
                return redirect()->back()->with('error', 'O valor max de venda é: R$ '.$product->value_max.'!');
            }

            $method = Payment::where('id', $request->payment)->first();
            if (!$method) {
                return redirect()->back()->with('error', 'Forma de pagamento não disponível!');
            }

            $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
            if (!$list) {
                return redirect()->back()->with('error', 'Não há uma lista disponível para vendas!');
            }

            $productCost = $seller->fixed_cost > 0 ? $seller->fixed_cost : $product->value_cost;
            $discountedValue = $baseValue * (1 - $discountPercentage / 100);

            $commission = (($discountedValue - $productCost) - $product->value_rate);

            if ($seller->filiate <> null) {
                $commissionFiliate = ($seller->fixed_cost - $seller->parent->fixed_cost);
            } else {
                $commissionFiliate = 0;
            }          
    
            $sale = new Sale();
            $sale->id_client    = $user->id;
            $sale->id_product   = $request->product;
            $sale->id_list      = $list->id;
            $sale->id_payment   = $method->id;
            $sale->id_seller    = !empty($request->id_seller) ? $request->id_seller : Auth::id();

            $sale->payment      = $method->method;
            $sale->installments = $method->installments;
            $sale->status       = 0;
            $sale->wallet_off   = $request->has('wallet_off') ? 1 : null;

            $sale->value                = $discountedValue + $method->value_rate;
            $sale->commission           = max($commission, 0);
            $sale->commission_filiate   = $commissionFiliate;

            if(!empty($product->contract)) {

                $document = $this->sendContract($user->id, $product->contract, $request->value, $request->payment);
                if($document['token']) {

                    $sale->token_contract = $document['token'];
                    $sale->url_contract   = $document['signers'][0]['sign_url'];

                    $seller = User::find(!empty($request->id_seller) ? $request->id_seller : Auth::id());
                    if($seller->api_token_zapapi) {
                        $this->sendWhatsapp($document['signers'][0]['sign_url'], "Prezado(a) ".$user->name.", segue seu contrato de adesão ao serviço de limpa nome com nossa assessoria. \r\n\r\n ⚠ Se não estiver aparecendo o link, Salva o nosso contato que aparecerá! \r\n\r\n\r\n ASSINAR O CONTRATO TOCANDO NO LINK 👇🏼✍🏼 \r\n", $user->phone, $seller->api_token_zapapi);
                    } else {
                        $this->sendWhatsapp($document['signers'][0]['sign_url'], "Prezado(a) ".$user->name.", segue seu contrato de adesão ao serviço de limpa nome com nossa assessoria. \r\n\r\n ⚠ Se não estiver aparecendo o link, Salva o nosso contato que aparecerá! \r\n\r\n\r\n ASSINAR O CONTRATO TOCANDO NO LINK 👇🏼✍🏼 \r\n", $user->phone);
                    }
                        
                    if($sale->save()) {
                        return redirect()->back()->with('success', 'Sucesso! O contrato foi enviado para o cliente via WhatsApp.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Foram encontrados problemas ao gerar contrato do cliente, contate o suporte!');
                }
            } else {

                $sale->save();
                
                $assas = new AssasController();
                $invoice = $assas->createSalePayment($sale->id, true);
                if($invoice) {
                    return redirect()->back()->with('success', 'Sucesso! Os dados de pagamento foram enviados para o Cliente!');
                }
            }

            return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }
    }

    private function createUser($name, $email, $cpfcnpj, $birth_date, $phone, $postal_code = null, $address = null, $complement = null, $city = null, $state = null, $num = null, $filiate = null) {

        $user = User::where('cpfcnpj', str_replace(['.', '-'], '', $cpfcnpj))->orWhere('email', $email)->first();
        if($user) {
            return $user;
        }
        
        $user               = new User();
        $user->name         = $name;
        $user->email        = preg_replace('/[^\w\d\.\@\-\_]/', '', $email);
        $user->cpfcnpj      = preg_replace('/\D/', '', $cpfcnpj);
        $user->birth_date   = date('Y-m-d', strtotime($birth_date));
        $user->password     = bcrypt(str_replace(['.', '-'], '', $cpfcnpj));
        $user->phone        = $phone;
        $user->postal_code  = $postal_code;
        $user->address      = $address;
        $user->complement   = $complement;
        $user->city         = $city;
        $user->state        = $state;
        $user->num          = $num;
        $user->type         = 3;
        $user->filiate      = $filiate;
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
                            "de"    => "EMAIL",
                            "para"  => $user->email
                        ],
                        [
                            "de"    => "PHONE",
                            "para"  => $user->phone
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

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }       

    public function manager(Request $request) {

        $query = Sale::orderBy('created_at', 'desc');

        if (!empty($request->name)) {
            $users = User::where('name', 'LIKE', '%'.$request->name.'%')->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('id_client', $users);
            }
        }

        if (!empty($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }

        if (!empty($request->value) && $this->formatarValor($request->value) > 0) {
            $query->where('value', $this->formatarValor($request->value));
        }

        if (!empty($request->id_list)) {
            $query->where('id_list', $request->id_list);
        }

        if (!empty($request->id_seller)) {
            $query->where('id_seller', $request->id_seller);
        }

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        if (!empty($request->label)) {
            $query->where('label', $request->label);
        }

        if(Auth::user()->type != 1) {
            $query->where('id_seller', Auth::user()->id);
        }

        $sales = $query->paginate(100);

        return view('app.Sale.manager',  [
            'sales'   => $sales,
            'lists'   => Lists::orderBy('created_at', 'desc')->get(),
            'sellers' => User::whereIn('type', [1, 2, 4, 5])->orderBy('name', 'asc')->get()
        ]);
    }

    public function viewSale($id) {

        $sale       = Sale::find($id);
        $invoices   = Invoice::where('id_sale', $sale->id)->get();
        $users      = User::whereIn('type', [1, 2, 5])->orderBy('name', 'asc')->get();

        return view('app.Sale.view', [
            'sale'      => $sale, 
            'invoices'  => $invoices,
            'users'     => $users
        ]);
    }

    public function updatedSale(Request $request) {

        $sale = Sale::find($request->id);
        if(!$sale) {
            return redirect()->back()->with('error', 'Não encontramos dados da venda!');
        }

        $sale->status = $request->status;
        if($sale->save()) {
            return redirect()->back()->with('success', 'Dados alterados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível alterar os dados da venda!');
    }

    public function deleteSale(Request $request) {

        $sale = Sale::find($request->id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Não encontramos dados da venda!');
        }

        $invoices = Invoice::where('id_sale', $sale->id)->get();
        foreach ($invoices as $invoice) {
           
            $assasController = new AssasController();
            if($invoice->status <> 1) {
                $assasController->cancelInvoice($invoice->token_payment);
            }
            
            $invoice->delete();
        }

        if($sale->delete()) {
            return redirect()->back()->with('success', 'Venda e Faturas excluídas com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível excluir a venda!');
    }

    public function deleteSalesPending() {

        $sales = Sale::whereIn('status', [0, 3])->get();
        $saleIds = $sales->pluck('id')->toArray();

        Invoice::whereIn('id_sale', $saleIds)->delete();
        Sale::whereIn('id', $saleIds)->delete();

        return redirect()->back()->with('success', 'Vendas pendentes removidas com sucesso!');
    }

    public function default(Request $request) {
        
        $user       = Auth::user();
        $id_seller  = $request->input('id_seller');
        $id_list    = $request->input('id_list');
        $name       = $request->input('name');
    
        $query = Invoice::query();
    
        if ($user->type == 1) {
            $query->where('due_date', '<', now())->where('status', 0);
        } else {
            $query->whereHas('sale', function ($query) use ($user) {
                $query->where('id_seller', $user->id);
            })->where('due_date', '<', now())->where('status', 0);
        }
    
        if ($id_seller) {
            $query->whereHas('sale', function ($query) use ($id_seller) {
                $query->where('id_seller', $id_seller);
            });
        }
    
        if ($id_list) {
            $query->whereHas('sale', function ($query) use ($id_list) {
                $query->where('id_list', $id_list);
            });
        }
    
        if ($name) {
            $query->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            });
        }
    
        $invoices = $query->paginate(100);
    
        return view('app.Sale.default', [
            'invoices' => $invoices,
            'lists'    => Lists::orderBy('created_at', 'desc')->get(),
            'sellers'  => User::whereIn('type', [1, 2, 4, 5])->orderBy('name', 'asc')->get()
        ]);
    }
    
    public function sendContractWhatsapp($id) {

        $sale = Sale::find($id);
        if(!$sale) {
            return redirect()->back()->with('error', 'Dados do contrato não encontrado!');
        }

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
                    'phone'           => '55' . $sale->user->phone,
                    'message'         => "Prezado(a) ".$sale->user->name.", segue seu contrato de adesão ao serviço de limpa nome com nossa assessoria. \r\n\r\n ASSINAR O CONTRATO CLICANDO NO LINK 👇🏼✍🏼 \r\n ⚠ Salva o contato se não tiver aparecendo o link.",
                    'image'           => env('APP_URL_LOGO'),
                    'linkUrl'         => $sale->url_contract,
                    'title'           => 'Assinatura de Documento',
                    'linkDescription' => 'Link para Assinatura Digital',
                ],
                'verify' => false
            ]);

            return redirect()->back()->with('success', 'Contrato enviado para o Cliente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar contrato, tente novamente mais tarde!');
        }
    }

    public function saleLink($product, $user, $value) {

        $payments       = Payment::where('id_product', $product)->get();
        $productSale    = Product::find($product);

        return view('form.sale', [
            'id_product'    => $product, 
            'payments'      => $payments, 
            'id_seller'     => $user, 
            'value'         => $value,
            'product'       => $productSale
        ]);
    }

    public function reprotocolSale($id) {

        $sale = Sale::find($id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');   
        }

        if ($sale->status <> 1) {
            return redirect()->back()->with('info', 'Função limitada a vendas confirmadas!');   
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não há uma lista disponível para reprotocolar a venda!');
        }

        $invoices = Invoice::where('id_sale', $sale->id)->get();
        $tomorrow = now()->addDay();
        foreach ($invoices as $invoice) {
            if ($invoice->due_date <= $tomorrow && $invoice->status == 0) {
                return redirect()->back()->with('error', 'Existem faturas vencidas associadas a Venda!');
            }
        }
        $sale->id_list = $sale->label === 'REPROTOCOLADO' ? $sale->id_list : $list->id;
        $sale->label   = $sale->label === 'REPROTOCOLADO' ? null : 'REPROTOCOLADO';
        if ($sale->save()) {

            if ($sale->label === 'REPROTOCOLADO') {
                $clientName     = $sale->user->name;
                $phone          = $sale->user->phone;
                $sellerApiToken = $sale->seller->api_token_zapapi;
            
                $message = "*Assunto: Reprotocolamento de Processo Judicial*\r\n\r\n" .
                           "{$clientName},\r\n\r\n" .
                           "Gostaríamos de informar que o *seu processo* foi *reprotocolado com sucesso.*\r\n\r\n" .
                           "A partir de agora, será necessário *aguardar o prazo estimado de 20 a 30 dias*, " .
                           "conforme estipulado pelos trâmites judiciais, para a análise e andamento do seu caso.\r\n\r\n" .
                           "Estamos acompanhando de perto o andamento do processo e *entraremos em contato assim que houver novidades.*\r\n\r\n" .
                           "Agradecemos sua paciência e estamos à disposição para esclarecer qualquer dúvida.";
            
                $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sellerApiToken);
            } else {
                $clientName     = $sale->user->name;
                $phone          = $sale->user->phone;
                $sellerApiToken = $sale->seller->api_token_zapapi;
            
                $message = "*Assunto: Conclusão do Processo Judicial*\r\n\r\n" .
                           "{$clientName},\r\n\r\n" .
                           "É com satisfação que informamos que o *seu processo foi concluído com sucesso!*\r\n\r\n" .
                           "Agradecemos pela confiança em nosso trabalho.";
            
                $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sellerApiToken);
            }            

            return redirect()->back()->with('success', 'Venda alterada com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');
    }

    public function deleteInvoice($id) {

        $invoice = Invoice::find($id);
        if(!$invoice) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Fatura!');
        }

        $assasController = new AssasController();
        if($invoice->status <> 1) {
            $cancelInvoice = $assasController->cancelInvoice($invoice->token_payment);

            if($cancelInvoice && $invoice->delete()) {
                return redirect()->back()->with('success', 'Fatura excluída com sucesso!');
            }
        }

        return redirect()->back()->with('error', 'Não é possível excluir uma Fatura já conciliada!');
    }

    public function createInvoice(Request $request) {

        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados do Produto!');
        }

        $sale = Sale::find($request->sale_id);
        if (!$sale) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Venda!');
        }

        if (!empty($request->wallet) && $this->formatarValor($request->commission) <= 0) {
            return redirect()->back()->with('info', 'Informe um valor de comissão!');
        }

        $assasController = new AssasController();
        $assasInvoice = $assasController->createCharge($sale->user->customer, $request->billingType, $this->formatarValor($request->value), 'Fatura única para venda N°'.$sale->id, $request->due_date, 1, $sale->seller->wallet, $this->formatarValor($request->commission));
        if ($assasInvoice <> false) {

            $invoice                = new Invoice();
            $invoice->id_user       = $sale->id_client;
            $invoice->id_product    = $product->id;
            $invoice->id_sale       = $sale->id;
            $invoice->name          = 'Fatura única para venda N°'.$sale->id;
            $invoice->description   = 'Fatura única para venda N°'.$sale->id;
            $invoice->token_payment = $assasInvoice['id'];
            $invoice->url_payment   = $assasInvoice['invoiceUrl'];
            $invoice->due_date      = $request->due_date;
            $invoice->value         = $this->formatarValor($request->value);
            $invoice->commission    = $this->formatarValor($request->commission);
            $invoice->status        = 0;
            $invoice->num           = 1;
            $invoice->type          = 3;
            if($invoice->save()) {
                return redirect()->back()->with('success', 'Fatura adicionada com sucesso!');
            }

            return redirect()->back()->with('info', 'Não foi possível adicionar Fatura, verifique os dados e tentar novamente!');
        }
            
        return redirect()->back()->with('info', 'Não foi possível adicionar Fatura, verifique os dados e tentar novamente!');
    }

    public function approvedAll(Request $request) {

        try {
            
            $sales = Sale::whereIn('id', $request['ids'])->get();
            if ($sales->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nenhuma venda encontrada!',
                ], 404);
            }
    
            foreach ($sales as $sale) {
                $sale->status = 1;
                $sale->save();
            }
    
            return response()->json([
                'success'       => true,
                'status'        => 'success',
                'message'       => 'Vendas aprovadas com sucesso!',
                'approved_ids'  => $sales->pluck('id')
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success'   => false,
                'status'    => 'error',
                'message'   => 'Ocorreu um erro ao aprovar as vendas!',
                'details'   => $e->getMessage(),
            ], 500);
        }
    }
}
