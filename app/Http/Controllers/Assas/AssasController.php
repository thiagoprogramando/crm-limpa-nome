<?php

namespace App\Http\Controllers\Assas;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class AssasController extends Controller {

    public function createSalePayment($id, $notification = null) {

        $sale      = Sale::find($id);
        $product   = Product::find($sale->id_product);
        $payment   = Payment::find($sale->id_payment);
        $client    = User::find($sale->id_client);
        $user      = User::find($sale->id_seller);
        
        if($sale->wallet_off) {
            $commission = $sale->value - $payment->value_rate;
        } else {
            $commission = (($sale->value - $product->value_cost) - $product->value_rate) - $payment->value_rate;
        }
        
        if($user->filiate != null) {
            $filiate = User::where('id', $user->filiate)->first();
            $filiate = !empty($filiate) ? $filiate->wallet : null;
        } else {
            $filiate = null;
        }

        if($user->type == 4) {
            $commission = 0;
        }
    
        switch($sale->payment) {
            case 'BOLETO':
                return $this->invoiceBoleto($sale->value, $product->value_cost, $commission, $sale, $user->wallet, $client, $filiate, $notification);
                break;
            case 'CREDIT_CARD':
                return $this->invoiceCard($sale->value, $commission, $sale, $user->wallet, $client, $filiate);
                break;
            case 'PIX':
                return $this->invoiceBoleto($sale->value, $product->value_cost, $commission, $sale, $user->wallet, $client, $filiate, $notification);
                break;
            default:
                return false;
                break;
        }
    
        return false;
    }

    private function invoiceBoleto($value, $value_cost, $commission, $sale, $wallet, $client, $filiate = null, $notification = null) {
    
        $valueInstallment = $value / $sale->installments;
    
        $invoices = Invoice::where('id_sale', $sale->id)->count();
        if ($invoices >= 2) {
            return true;
        }
    
        $existingInvoice = Invoice::where('id_sale', $sale->id)->first();

        if($sale->wallet_off) {
            $firstInstallmentValue = $valueInstallment;
            $installmentValue = $valueInstallment;
    
            $firstInstallmentCommission = $valueInstallment;
            $installmentCommission = ($commission - $firstInstallmentCommission) / max(1, ($sale->installments - 1));
        } else {
            if ($valueInstallment < $value_cost) {
            
                $firstInstallmentValue = $value_cost;
                $installmentValue = ($value - $firstInstallmentValue) / ($sale->installments - 1); 
                
                $firstInstallmentCommission = 0;
                $installmentCommission = ($commission - $firstInstallmentCommission) / max(1, ($sale->installments - 1));
            } else {
                
                $firstInstallmentValue = $valueInstallment;
                $installmentValue = $valueInstallment;
        
                $firstInstallmentCommission = $valueInstallment - $value_cost;
                $installmentCommission = ($commission - $firstInstallmentCommission) / max(1, ($sale->installments - 1));
            }
        }
    
        $customer = $this->createCustomer($client->name, $client->cpfcnpj, $client->phone, $client->email);
    
        if (!$existingInvoice) {
            
            $invoice = new Invoice();
            $invoice->id_user       = $sale->id_client;
            $invoice->id_sale       = $sale->id;
            $invoice->id_product    = $sale->id_product;
    
            $invoice->name          = env('APP_NAME').' - Fatura';
            $invoice->description   = 'Fatura N° 1 da venda N° '.$sale->id;
    
            $charge = $this->createCharge(
                $customer,
                $sale->payment, 
                $firstInstallmentValue, 
                'Fatura N° 1 da venda N°'.$sale->id,
                now()->addDay(),
                null,
                $wallet,
                max(0, $firstInstallmentCommission - 2),
                $filiate
            );  
    
            if ($charge) {
                $invoice->url_payment   = $charge['invoiceUrl'];
                $invoice->token_payment  = $charge['id'];
            }
    
            $invoice->value         = $firstInstallmentValue;
            $invoice->commission    = max(0, $firstInstallmentCommission - 2);
            $invoice->due_date      = now()->addDay();
            $invoice->num           = 1;
            $invoice->type          = 3;
            $invoice->status        = 0;
            $invoice->save();
    
        } else {

            for ($i = 2; $i <= $sale->installments; $i++) {
                $invoice = new Invoice();
                $invoice->id_user       = $sale->id_client;
                $invoice->id_sale       = $sale->id;
                $invoice->id_product    = $sale->id_product;

                $invoice->name          = env('APP_NAME').' - Fatura';
                $invoice->description   = 'Fatura N° '.$i.' da venda N° '.$sale->id;

                $charge = $this->createCharge(
                    $customer,
                    $sale->payment, 
                    ($i == 1) ? $firstInstallmentValue : $installmentValue, 
                    'Fatura N°'.$i.' da venda N°'.$sale->id,
                    ($i == 1) ? now()->addDay() : now()->addMonths($i - 1),
                    null,
                    $wallet,
                    ($i == 1) ? max(0, $firstInstallmentCommission - 2) : max(0, $installmentCommission - 2),
                    $filiate
                );  
                
                if ($charge) {
                    $invoice->url_payment   = $charge['invoiceUrl'];
                    $invoice->token_payment  = $charge['id'];
                }

                $invoice->value         = ($i == 1) ? $firstInstallmentValue : $installmentValue;
                $invoice->commission    = ($i == 1) ? max(0, $firstInstallmentCommission - 2) : max(0, $installmentCommission - 2);
                $invoice->due_date      = ($i == 1) ? now()->addDay() : now()->addMonths($i - 1);
                $invoice->num           =  $i;
                $invoice->type          =  3;
                $invoice->status        =  0;
                $invoice->save();
            }
        }
    
        $invoice = Invoice::where('id_sale', $sale->id)->where('status', 0)->orderBy('created_at', 'asc')->first();
        if ($invoice && $notification == true) {
            $this->sendInvoice($invoice->url_payment, $sale->id_client, $sale->seller->api_token_zapapi);
        }
        
        return true;
    }        
    
    private function invoiceCard($value, $commission, $sale, $wallet, $client, $filiate = null) {

        $invoice                = new Invoice();
        $invoice->id_user       = $sale->id_client;
        $invoice->id_sale       = $sale->id;
        $invoice->id_product    = $sale->id_product;

        $invoice->name          = env('APP_NAME').' - Fatura';
        $invoice->description   = 'Fatura única para venda N°'.$sale->id;

        $invoice->value         = $value;
        $invoice->commission    = $commission;
        $invoice->due_date      = now()->addDay();
        $invoice->num           = 1;
        $invoice->type          = 3;
        $invoice->status        = 0;

        $charge = $this->createCharge(
            $this->createCustomer($client->name, $client->cpfcnpj, $client->phone, $client->email),
            $sale->payment, 
            $value, 
            'Fatura única para venda N°'.$sale->id,
            now()->addDay(),
            $sale->installments,
            $wallet,
            $commission
        );

        if($charge) {
            $invoice->url_payment   = $charge['invoiceUrl'];
            $invoice->token_payment = $charge['id'];
        } else {
            return false;
        }

        $notification               = new Notification();
        $notification->name         = 'Faturas criada';
        $notification->description  = 'Faturas geradas para venda N° '.$sale->id;
        $notification->type         = 1;
        $notification->id_user      = $sale->id_seller; 
        $notification->save();

        if($invoice->save()) {
            
            $invoice = Invoice::where('id_sale', $sale->id)->where('status', 0)->first();
            $this->sendInvoice($charge['invoiceUrl'], $sale->id_client, $sale->seller->api_token_zapapi);

            return true;
        }

        return false;
    }

    private function sendInvoice($url_payment, $id, $token = null) {

        $user = User::find($id);
        if($user) {

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
                        'phone'           => '55' . $user->phone,
                        'message'         => "Prezado ".$user->name.", estamos enviando o link para pagamento da sua contratação aos serviços da nossa assessoria.  \r\n\r\n\r\n FAZER O PAGAMENTO CLIQUE NO LINK 👇🏼💳 \r\n",
                        'image'           => env('APP_URL_LOGO'),
                        'linkUrl'         => $url_payment,
                        'title'           => 'Pagamento de Fatura',
                        'linkDescription' => 'Link para Pagamento Digital',
                    ],
                    'verify' => false
                ]);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }
    
    public function createMonthly($id) {

        $user = User::find($id);
        if($user) {
            $invoice = Invoice::where('id_user', $user->id)->where('type', 1)->where('status', 0)->exists();
            if($invoice) {
                return redirect()->route('payments')->with('error', 'Você possui uma mensalidade em aberto!');
            }
        }

        if($user->customer == null) {
            $customer = $this->createCustomer($user->name, $user->cpfcnpj, $user->phone, $user->email);
            if($customer) {
                $user->customer = $customer;
                $user->save();
            } else {
                return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
            }
        }

        $charge = $this->createCharge($user->customer, 'PIX', '99.00', 'Assinatura -'.env('APP_NAME'), now()->addDay(), null, env('WALLET_HEFESTO'), 20);
        if($charge != false) {

            $invoice = new Invoice();
            $invoice->name          = 'Mensalidade '.env('APP_NAME');
            $invoice->description   = 'Mensalidade '.env('APP_NAME');

            $invoice->id_user       = $user->id;
            $invoice->id_product    = 0;
            $invoice->value         = 99;
            $invoice->commission    = 20;
            $invoice->status        = 0;
            $invoice->type          = 1;
            $invoice->num           = 1;
            $invoice->due_date      = now()->addDay();

            $invoice->url_payment   = $charge['invoiceUrl'];
            $invoice->token_payment = $charge['id'];

            if($invoice->save()) {
                return redirect()->route('payments')->with('success', 'Agora, será necessário efetuar o pagamento!');
            }
        }

        return redirect()->back()->with('error', 'Tivemos um pequeno problema, contate o suporte!');
    }

    public function createDeposit(Request $request) {

        $user = User::find(Auth::user()->id);
        if(!$user) {
            return redirect()->route('logout')->with('error', 'Você precisa fazer login para acessar sua conta!');
        }

        if(empty($user->customer)) {
            $customer = $this->createCustomer($user->name, $user->cpfcnpj, $user->phone, $user->email);
            $user->customer = $customer;
            $user->save();
        } else {
            $customer = $user->customer;
        }

        try {

            $charge = $this->createCharge($customer, 'PIX', $this->formatarValor($request->value), 'Depósito - ' . env('APP_NAME'), now()->addDay(), 0);
            if ($charge) {
                $invoice = new Invoice();
                $invoice->name          = 'Depósito para Carteira de Investimentos';
                $invoice->description   = 'Depósito ' . $user->name;
                $invoice->id_user       = $user->id;
                $invoice->id_product    = 0;
                $invoice->value         = $this->formatarValor($request->value);
                $invoice->commission    = 0;
                $invoice->status        = 0;
                $invoice->type          = 4;
                $invoice->num           = 1;
                $invoice->due_date      = now()->addDay();
                $invoice->url_payment   = $charge['invoiceUrl'];
                $invoice->token_payment = $charge['id'];
    
                if ($invoice->save()) {
                    return redirect($charge['invoiceUrl'])->with('success', 'Agora, será necessário efetuar o pagamento!');
                } else {
                    return redirect()->back()->with('error', 'Tivemos um problema ao gerar sua fatura, contate o suporte!');
                }
            } else {
                return redirect()->back()->with('error', 'Verifique seus dados!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Tivemos um pequeno problema, contate o suporte! ' . $e->getMessage());
        }
    }

    private function createCustomer($name, $cpfcnpj, $mobilePhone, $email) {
        
        $client = new Client();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'accept'       => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
                'User-Agent'   => env('APP_NAME')
            ],
            'json' => [
                'name'          => $name,
                'cpfCnpj'       => $cpfcnpj,
                'mobilePhone'   => $mobilePhone,
                'email'         => $email,
            ],
            'verify' => false
        ];

        $response = $client->post(env('API_URL_ASSAS') . 'v3/customers', $options);
        $body = (string) $response->getBody();
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
            return $data['id'];
        } else {
            return false;
        }
    }

    public function createCharge($customer, $billingType, $value, $description, $dueDate, $installments = null, $wallet= null, $commission = null, $filiate = null) {

        $client = new Client();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
                'User-Agent'   => env('APP_NAME')
            ],
            'json' => [
                'customer'          => $customer,
                'billingType'       => $billingType,
                'value'             => number_format($value, 2, '.', ''),
                'dueDate'           => $dueDate,
                'description'       => $description,
                'installmentCount'  => $installments != null ? $installments : 1,
                'installmentValue'  => $installments != null ? number_format(($value / intval($installments)), 2, '.', '') : $value,
                'isAddressRequired' => false
            ],
            'verify' => false
        ];

        if(env('APP_ENV') <> 'local') {
            if ($filiate != null && $commission > 0) {
                if (!isset($options['json']['split'])) {
                    $options['json']['split'] = [];
                }

                $affiliateCommission = $commission * 0.20;
                $commission = $commission - $affiliateCommission;
            
                $options['json']['split'][] = [
                    'walletId'          => $filiate,
                    'totalFixedValue' => number_format($affiliateCommission, 2, '.', '')
                ];
            }

            if ($commission > 0) {
                if (!isset($options['json']['split'])) {
                    $options['json']['split'] = [];
                }

                $g7Commission = $commission * 0.05;
                $commission = $commission - $g7Commission;

                if($wallet <> env('WALLET_HEFESTO')) {
                    $options['json']['split'][] = [
                        'walletId'          => env('WALLET_G7'),
                        'totalFixedValue' => number_format($g7Commission, 2, '.', '')
                    ];
                }
            }

            if ($wallet != null && $commission > 0) {
                if (!isset($options['json']['split'])) {
                    $options['json']['split'] = [];
                }
            
                $options['json']['split'][] = [
                    'walletId'          => $wallet,
                    'totalFixedValue' => number_format($commission, 2, '.', '')
                ];
            }
        }
        
        $response = $client->post(env('API_URL_ASSAS') . 'v3/payments', $options);
        $body = (string) $response->getBody();

        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
            return $dados['json'] = [
                'id'            => $data['id'],
                'invoiceUrl'    => $data['invoiceUrl'],
            ];
        } else {
            return false;
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
    
            if ($response->getStatusCode() == 200) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }    

    private function createKey($id) {

        $invoice = Invoice::find($id);
        if($invoice) {

            $user = User::find($invoice->id_user);
            if($user) {

                if($user->api_key != null) {
                    return true;
                }

                $client = new Client();

                $options = [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'access_token' => env('API_TOKEN_ASSAS'),
                        'User-Agent'   => env('APP_NAME')
                    ],
                    'json' => [
                        'name'          => $user->name,
                        'email'         => $user->email,
                        'cpfCnpj'       => $user->cpfcnpj,
                        'birthDate'     => $user->birth_date,
                        'mobilePhone'   => $user->phone,
                        'address'       => '09750-730 Rua José Versolato, 101 - Vila da Saúde, São Bernado do Campo',
                        'addressNumber' => '101',
                        'province'      => 'São Paulo',
                        'postalCode'    => '09750730',
                        'companyType'   => strlen($user->cpfcnpj) === 11 ? '' : 'MEI',
                        'incomeValue'   => 1000,
                        "accountStatusWebhook" => [
                            "url"           => env('APP_URL')."/api/webhookAccount",
                            "email"         => env('APP_EMAIL_SUPORT'),
                            "interrupted"   => false,
                            "enabled"       => true,
                            "apiVersion"    => 3,
                        ],
                        "transferWebhook"      => [
                            "url"           => env('APP_URL')."/api/webhookAccount",
                            "email"         => env('APP_EMAIL_SUPORT'),
                            "interrupted"   => false,
                            "enabled"       => true,
                            "apiVersion"    => 3,
                        ],
                        "paymentWebhook"       => [
                            "url"           => env('APP_URL')."/api/webhookAccount",
                            "email"         => env('APP_EMAIL_SUPORT'),
                            "interrupted"   => false,
                            "enabled"       => true,
                            "apiVersion"    => 3,
                        ],
                        "invoiceWebhook"        => [
                            "url"           => env('APP_URL')."/api/webhookAccount",
                            "email"         => env('APP_EMAIL_SUPORT'),
                            "interrupted"   => false,
                            "enabled"       => true,
                            "apiVersion"    => 3,
                        ],
                    ],
                    'verify' => false
                ];

                $response = $client->post(env('API_URL_ASSAS') . 'v3/accounts', $options);
                if ($response->getStatusCode() === 200) {
                    $body = (string) $response->getBody();
                    $data = json_decode($body, true);

                    $user->api_key  = $data['apiKey'];
                    $user->wallet   = $data['walletId'];
                    $user->status   = 2;
                    if($user->save()) {
                        return true;
                    }

                    return false;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    public function webhook(Request $request) {

        $jsonData = $request->json()->all();
        if ($jsonData['event'] === 'PAYMENT_CONFIRMED' || $jsonData['event'] === 'PAYMENT_RECEIVED') {
            
            $token = $jsonData['payment']['id'];
            $invoice = Invoice::where('token_payment', $token)->where('status', 0)->first();
            if($invoice) {

                $invoice->status = 1;
                if(!$invoice->save()) {
                    return response()->json(['status' => 'error', 'message' => 'Não foi possível confirmar o pagamento da fatura!']);
                }

                $sale = Sale::where('id', $invoice->id_sale)->first();
                
                if($sale) {
                    $invoices = $this->createSalePayment($sale->id);
                    if($invoices == false) {
                        $invoice->status = 0;
                        $invoice->save();

                        return response()->json(['status' => 'error', 'message' => 'Não foi possível confirmar o pagamento da fatura e gerar às demais faturas!']);
                    }

                    $sale->guarantee = Carbon::parse($sale->guarantee)->addMonths(3);
                    $sale->save();
                }
                
                $product = $invoice->id_product != null ? Product::where('id', $invoice->id_product)->first() : false;
                if($product) {
                    if($invoice->num == 1) {
                        if($sale) {
                            $sale->status = 1;
                            $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
                            if($list) {
                                $sale->id_list = $list->id;
                            }

                            $sale->save();
                        }
                    }
                }

                if($sale) {
                    
                    $notification               = new Notification();
                    $notification->name         = 'Fatura N°'.$invoice->id;
                    $notification->description  = 'Faturas recebida com sucesso!';
                    $notification->type         = 1;
                    $notification->id_user      = $invoice->id_seller; 
                    $notification->save();

                    $seller = User::find($sale->id_seller);
                    if($seller->type != 4 && $seller->level != 5) {
                        $totalSales = Sale::where('id_seller', $seller->id)->where('status', 1)->count();
                        switch($totalSales) {
                            case 10:
                                $seller->level = 2;
                                $notification               = new Notification();
                                $notification->name         = 'Novo nível!';
                                $notification->description  = $seller->name.' Alcançou o nível: CONSULTOR';
                                $notification->type         = 2;
                                $notification->id_user      = 14; 
                                $notification->save();
                                break;
                            case 30:
                                $seller->level = 3;
                                $notification               = new Notification();
                                $notification->name         = 'Novo nível!';
                                $notification->description  = $seller->name.' Alcançou o nível: CONSULTOR LÍDER';
                                $notification->type         = 2;
                                $notification->id_user      = 14; 
                                $notification->save();
                                break;
                            case 50:
                                $seller->level = 4;
                                $notification               = new Notification();
                                $notification->name         = 'Novo nível!';
                                $notification->description  = $seller->name.' Alcançou o nível: REGIONAL';
                                $notification->type         = 2;
                                $notification->id_user      = 14; 
                                $notification->save();
                                break;
                            case 100:
                                $seller->level = 5;
                                $notification               = new Notification();
                                $notification->name         = 'Novo nível!';
                                $notification->description  = $seller->name.' Alcançou o nível: GERENTE REGIONAL';
                                $notification->type         = 2;
                                $notification->id_user      = 14; 
                                break;
                        }
                        $seller->save();
                    }
                }

                if($invoice->type == 1) {
                    $key = $this->createKey($invoice->id);
                    if($key) {
                        return response()->json(['status' => 'success', 'message' => 'Operação Finalizada & ApiKey criada!']);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Operação Finalizada, mas houve um erro na criação da ApiKey!']);
                }

                if($invoice->type == 4) {
                    $user = User::find($invoice->id_user);
                    $user->wallet_off += $invoice->value;
                    if($user->save()) {
                        return response()->json(['status' => 'success', 'message' => 'Operação Finalizada & Saldo depositado!']);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Operação Finalizada, mas houve um erro no deposito!']);
                }

                $client = User::find($invoice->id_user);
                if($client && $invoice->num == 1) {
                    $this->sendWhatsapp(env('APP_URL').'login-cliente', "Olá, ".$client->name."!\r\n\r\nAgradecemos pelo seu pagamento! \r\n\r\n\r\n Tenha a certeza de que sua situação está em boas mãos. \r\n\r\n\r\n *Nos próximos 30 dias úteis*, nossa equipe especializada acompanhará de perto todo o processo para garantir que seu nome seja limpo o mais rápido possível. \r\n\r\n\r\n Estamos à disposição para qualquer dúvida ou esclarecimento. \r\n\r\n Você pode acompanhar o processo acessando nosso sistema no link abaixo: \r\n\r\n", $client->phone, $seller->api_token_zapapi);
                } else {
                    $this->sendWhatsapp(env('APP_URL').'login-cliente', $client->name."!\r\n\r\nAgradecemos por manter o compromisso e realizar o pagamento do boleto, o que garante a continuidade e a validade da garantia do serviço. \r\n\r\n Acesse o Painel do cliente👇", $client->phone, $seller->api_token_zapapi);
                }

                if($invoice->num == 1 && $invoice->type == 3) {
                    $message =  "Olá, {$seller->name}, Espero que esteja bem! 😊\r\n\r\n"
                                . "Gostaria de informar que uma nova venda foi realizada com sucesso.🤑💸\r\n\r\n"
                                . "Cliente: {$client->name}\r\n"
                                . "Produto/Serviço: {$product->name}\r\n"
                                . "Valor Total: R$ " . number_format($sale->value, 2, ',', '.') . "\r\n"
                                . "Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
                                . "Obrigado pelo excelente trabalho!🥇\r\n\r\n"
                                . "Atenciosamente,\r\n"
                                . "Equipe G7 Assessoria";

                    $this->sendWhatsapp("", $message, $seller->phone, $seller->api_token_zapapi);
                }

                if($invoice->num != 1 && $invoice->type == 3 && $invoice->commission > 0) {
                    $message =  "Olá, {$seller->name}, Espero que esteja bem! 😊\r\n\r\n"
                                . "Gostaria de informar que uma nova COMISSÃO FOI RECEBIDA com sucesso.🤑💸\r\n\r\n"
                                . "Cliente: {$client->name}\r\n"
                                . "Produto/Serviço: {$product->name}\r\n"
                                . "Fatura N° {$invoice->num}\r\n"
                                . "Valor apróximado: R$ " . number_format($invoice->commission, 2, ',', '.') . "\r\n"
                                . "Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
                                . "Obrigado pelo excelente trabalho!🥇\r\n\r\n"
                                . "Atenciosamente,\r\n"
                                . "Equipe G7 Assessoria";

                    $this->sendWhatsapp("", $message, $seller->phone, $seller->api_token_zapapi);
                }
                
                return response()->json(['status' => 'success', 'message' => 'Operação Finalizada!']);
            }
            
            return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura encontrada!']);
        }

        if($jsonData['event'] === 'PAYMENT_OVERDUE') {

            $token = $jsonData['payment']['id'];
            $invoice = Invoice::where('token_payment', $token)->where('status', 0)->first();
            if($invoice) {

                if(($invoice->type == 2 || $invoice->type == 3) && $invoice->num > 1) {
                    switch ($invoice->notification_number) {
                        case 1:
                            $value      = $invoice->value - ($invoice->value * 0.10);
                            $commission = $invoice->commission - ($invoice->commission * 0.15);
                            $dueDate    = Carbon::now()->addDays(7);
                            $wallet     = $invoice->sale->seller->wallet;

                            $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
                            if($charge) {

                                $invoice->due_date               = $dueDate;
                                $invoice->value                  = $value;
                                $invoice->commission             = $commission;
                                $invoice->url_payment            = $charge['invoiceUrl'];
                                $invoice->token_payment          = $charge['id'];
                                $invoice->notification_number    += 1;
                                $invoice->save(); 

                                $dueDateFormatted = Carbon::parse($dueDate)->format('d/m/Y');
                                $message =  "Olá, {$invoice->user->name}!\r\n\r\n"
                                    . "Sua fatura {$invoice->num} está atrasada. Oferecemos um desconto de 10% se o pagamento for feito até {$dueDateFormatted}.\r\n"
                                    . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n"
                                    . "Atenciosamente, Equipe G7 Assessoria \r\n";

                                $this->sendWhatsapp(
                                    $charge['invoiceUrl'],
                                    $message,
                                    $invoice->user->phone,
                                    $invoice->sale->seller->api_token_zapapi
                                );
                            }
                            break;
                        case 2:
                            $value      = $invoice->value - ($invoice->value * 0.10);
                            $commission = $invoice->commission - ($invoice->commission * 0.15);
                            $dueDate    = Carbon::now()->addDays(7);
                            $wallet     = $invoice->sale->seller->wallet;

                            $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
                            if($charge) {

                                $invoice->due_date               = $dueDate;
                                $invoice->value                  = $value;
                                $invoice->commission             = $commission;
                                $invoice->url_payment            = $charge['invoiceUrl'];
                                $invoice->token_payment          = $charge['id'];
                                $invoice->notification_number    += 1;
                                $invoice->save(); 

                                $dueDateFormatted = \Carbon\Carbon::parse($dueDate)->format('d/m/Y');
                                $message =  "Olá, {$invoice->user->name}!\r\n\r\n"
                                    . "Sua fatura {$invoice->num} está atrasada. Oferecemos um desconto de 20% se o pagamento for feito até {$dueDateFormatted}.\r\n"
                                    . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n"
                                    . "Atenciosamente, Equipe G7 Assessoria \r\n";

                                $this->sendWhatsapp(
                                    $charge['invoiceUrl'],
                                    $message,
                                    $invoice->user->phone,
                                    $invoice->sale->seller->api_token_zapapi
                                );
                            }
                            break;     
                        case 3:
                            $value      = $invoice->value - ($invoice->value * 0.10);
                            $commission = $invoice->commission - ($invoice->commission * 0.15);
                            $dueDate    = Carbon::now()->addDays(7);
                            $wallet     = $invoice->sale->seller->wallet;

                            $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
                            if($charge) {

                                $invoice->due_date               = $dueDate;
                                $invoice->value                  = $value;
                                $invoice->commission             = $commission;
                                $invoice->url_payment            = $charge['invoiceUrl'];
                                $invoice->token_payment          = $charge['id'];
                                $invoice->notification_number    += 1;
                                $invoice->save(); 

                                $dueDateFormatted = \Carbon\Carbon::parse($dueDate)->format('d/m/Y');
                                $message =  "Olá, {$invoice->user->name}!\r\n\r\n"
                                    . "Sua fatura {$invoice->num} está atrasada. Oferecemos um desconto de 30% se o pagamento for feito até {$dueDateFormatted}.\r\n"
                                    . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n"
                                    . "Atenciosamente, Equipe G7 Assessoria \r\n";

                                $this->sendWhatsapp(
                                    $charge['invoiceUrl'],
                                    $message,
                                    $invoice->user->phone,
                                    $invoice->sale->seller->api_token_zapapi
                                );
                            }
                            break;
                        case 4:
                            $value      = $invoice->value - ($invoice->value * 0.20);
                            $commission = $invoice->commission - ($invoice->commission * 0.20);
                            $dueDate    = Carbon::now()->addDays(7);
                            $wallet     = $invoice->sale->seller->wallet;

                            $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
                            if($charge) {

                                $invoice->due_date               = $dueDate;
                                $invoice->value                  = $value;
                                $invoice->commission             = $commission;
                                $invoice->url_payment            = $charge['invoiceUrl'];
                                $invoice->token_payment          = $charge['id'];
                                $invoice->notification_number    += 1;
                                $invoice->save(); 

                                $dueDateFormatted = \Carbon\Carbon::parse($dueDate)->format('d/m/Y');
                                $message =  "Assunto: Urgente: Fatura Atrasada \r\n\r\n Olá, {$invoice->user->name}!\r\n\r\n"
                                    . "Sua fatura {$invoice->num} está gravemente atrasada. Oferecemos um desconto de 50% se o pagamento for feito até {$dueDateFormatted}.\r\n"
                                    . "*Após essa data, a multa será aplicada e a garantia do produto será cancelada, o que pode resultar em custos extras e prejuízos adicionais.*\r\n\r\n"
                                    . "Além disso, seu nome voltará a ficar sujo e toda a boa reputação que trabalhamos para recuperar para você será perdida. Não deixe essa oportunidade passar e evite impactos negativos em sua situação financeira e reputacional. \r\n\r\n\r\n"
                                    . "Atenciosamente, Equipe G7 Assessoria \r\n";

                                $this->sendWhatsapp(
                                    $charge['invoiceUrl'],
                                    $message,
                                    $invoice->user->phone,
                                    $invoice->sale->seller->api_token_zapapi
                                );
                            }
                            break;
                        default:
                            return response()->json(['status' => 'success', 'message' => 'Notificação de vencimento gerada!']);
                            break;
                    }
                }

                return response()->json(['status' => 'success', 'message' => 'Não é cobrança de Produto!']);
            }

            return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura encontrada!']);
        }

        return response()->json(['status' => 'success', 'message' => 'Webhook não utilizado!']);
    }

    private function addDiscount($id, $value, $dueDate, $commission = null, $wallet = null) {
        
        $client = new Client();
        
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
                'User-Agent'   => env('APP_NAME')
            ],
            'json' => [
                'value'       => number_format($value, 2, '.', ''),
                'dueDate'     => $dueDate,
                'description' => 'Acordo de cobrança vencida',
            ],
            'verify' => false
        ];
    
        if ($commission > 0) {
            if (!isset($options['json']['split'])) {
                $options['json']['split'] = [];
            }
    
            $options['json']['split'][] = [
                'walletId'        => $wallet,
                'totalFixedValue' => number_format($commission, 2, '.', '')
            ];
        }
    
        try {

            $response = $client->put(env('API_URL_ASSAS') . 'v3/payments/' . $id, $options);
            $body = (string) $response->getBody();

            if ($response->getStatusCode() === 200) {
                $data = json_decode($body, true);
                return [
                    'id'         => $data['id'],
                    'invoiceUrl' => $data['invoiceUrl'],
                ];
            }
    
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $error = json_decode($responseBody, true);

            return false;
        } catch (\Exception $e) {    
            return false;
        }
  
        return false;
    }    

    public function myDocuments() {
        
        $user = auth()->user();

        if(empty($user->api_key)) {
            return [];
        }

        $client = new Client();
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => $user->api_key,
                'User-Agent'   => env('APP_NAME')
            ],
            'verify' => false
        ];

        $response = $client->get(env('API_URL_ASSAS') . 'v3/myAccount/documents', $options);
        $body = (string) $response->getBody();
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
    
            if (isset($data['data'])) {
                return $data['data'];
            } else {
                return [];
            }
        } else {
            return false;
        }
    }

    public function receivable() {

        $client = new Client();
        $user = auth()->user();
        $startDate = $user->created_at->toDateString();
        $finishDate = now()->toDateString();

        $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
            'headers' => [
                'accept'        => 'application/json',
                'access_token'  => $user->api_key,
                'User-Agent'    => env('APP_NAME')
            ],
            'verify' => false,
        ]);

        $body = (string) $response->getBody();
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
            return $data['data'];
        } else {
            return [];
        }
    }

    public function balance() {

        $client = new Client();
        $user = auth()->user();

        $response = $client->request('GET',  env('API_URL_ASSAS') . 'v3/finance/balance', [
            'headers' => [
                'accept'       => 'application/json',
                'access_token' => $user->api_key,
                'User-Agent'   => env('APP_NAME')
            ],
            'verify' => false,
        ]);

        $body = (string) $response->getBody();
        if ($response->getStatusCode() === 200) {

            $data = json_decode($body, true);
            return $data['balance'];
        } else {

            return false;
        }
    }

    public function statistics() {

        $client = new Client();
        $user = auth()->user();

        $response = $client->request('GET',  env('API_URL_ASSAS') . 'v3/finance/split/statistics', [
            'headers' => [
                'accept'        => 'application/json',
                'access_token'  => $user->api_key,
                'User-Agent'    => env('APP_NAME')
            ],
            'verify' => false,
        ]);

        $body = (string) $response->getBody();
        if ($response->getStatusCode() === 200) {

            $data = json_decode($body, true);
            return $data['income'];
        } else {

            return false;
        }
    }

    public function accumulated() {
        try {
            $client = new Client();
            $user = auth()->user();
            $startDate = $user->created_at->toDateString();
            $finishDate = now()->toDateString();
    
            $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
                'headers' => [
                    'accept'        => 'application/json',
                    'access_token'  => $user->api_key,
                    'User-Agent'    => env('APP_NAME')
                ],
                'verify' => false,
            ]);
    
            if ($response->getStatusCode() === 200) {
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
                $filteredData = array_filter($data['data'], function ($item) {
                    return $item['type'] === 'TRANSFER';
                });
    
                $totalValue = array_sum(array_column($filteredData, 'value'));
    
                return abs($totalValue);
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function webhookSing(Request $request) {

        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        if (isset($data['token']) && isset($data['event_type'])) {
            
            if($data['event_type'] === 'doc_signed') {
                $token = $data['token'];

                $sale = Sale::where('token_contract', $token)->first();
                if ($sale && $sale->status != 2) {

                    $sale->status = 2;
                    $sale->save();

                    if($this->createSalePayment($sale->id, true)) {
                        return response()->json(['message' => 'Contrato assinado e Faturas geradas!'], 200);
                    }

                    return response()->json(['message' => 'Contrato assinado e mas não foi possível gerar Faturas!'], 200);
                }
            }

            return response()->json(['message' => 'Nenhuma operação finalizada!'], 200);
        } else {
            
            return response()->json(['error' => 'Token e Event não localizados!'], 200);
        }

        return response()->json(['error' => 'Webhook não utilizado!'], 200);
    }

    public function webhookAccount(Request $request) {

        $this->logRequest($request);

        $jsonData = $request->json()->all();
        $user = User::where('wallet', $jsonData['accountStatus']['id'])->first();
        if($user) {
            switch ($jsonData['event']) {
                case 'ACCOUNT_STATUS_GENERAL_APPROVAL_APPROVED':
                    $user->status = 1;
                    $user->save();
                    break;
                case 'ACCOUNT_STATUS_GENERAL_APPROVAL_PENDING':
                    $user->status = 2;
                    $user->save();
                    break;
            }        
            return response()->json(['status' => 'success', 'message' => 'Tratamento realizado para status da Conta!']);
        }

        return response()->json(['status' => 'success', 'message' => 'Não há nenhuma conta associada ao conteúdo da requisição!']);
    }

    private function logRequest(Request $request) {

        $logPath = public_path('request_log.txt');
    
        $requestData = [
            'headers' => $request->header(),
            'body' => $request->json()->all(),
        ];
    
        $logMessage = "Request Log:\n" . json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";

        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }

    public function withdrawSend($key, $value, $type) {

        $client = new Client();
        
        $user = auth()->user();
        try {
            $response = $client->request('POST', env('API_URL_ASSAS').'v3/transfers', [
                'headers' => [
                    'accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'access_token' => $user->api_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'json' => [
                    'value' => $value,
                    'operationType' => 'PIX',
                    'pixAddressKey' => $key,
                    'pixAddressKeyType' => $type,
                    'description' => 'Saque '.env('APP_NAME'),
                ],
                'verify'  => false,
            ]);
    
            $body = $response->getBody()->getContents();
            $decodedBody = json_decode($body, true);
    
            if ($decodedBody['status'] === 'PENDING') {
                return ['success' => true, 'message' => 'Saque agendado com sucesso'];
            } else {
                return ['success' => false, 'message' => 'Situação do Saque: ' . $decodedBody['status']];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $decodedBody = json_decode($body, true);
    
            return ['success' => false, 'message' => $decodedBody['errors'][0]['description']];
        }
    }

    public function extract() {

        $client = new Client();

        $user = auth()->user();
        $startDate = $user->created_at->toDateString();
        $finishDate = now()->toDateString();

        $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
            'headers' => [
                'accept'        => 'application/json',
                'access_token'  => $user->api_key,
                'User-Agent'    => env('APP_NAME')
            ],
            'verify' => false,
        ]);

        $body = (string) $response->getBody();
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
            return $data['data'];
        } else {
            return [];
        }
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);

        return number_format($valorFloat, 2, '.', '');
    }

    public function requestInvoice($id) {

        $assas = new AssasController();
        $invoices = $assas->createSalePayment($id, true);
        if($invoices <> false) {
            return redirect()->back()->with('success', 'Faturas geradas com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível Gerar Faturas!');
    } 

    public function cancelInvoice($token) {

        $client = new Client();
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
                'User-Agent'   => env('APP_NAME')
            ],
            'verify' => false
        ];

        $response = $client->delete(env('API_URL_ASSAS') . 'v3/payments/'.$token, $options);
        $body = (string) $response->getBody();
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
    
            if(isset($data['deleted']) && $data['deleted'] == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function payMonthly($id) {

        $invoice = Invoice::find($id);
        if(!$invoice || $invoice->status == 1) {
            return redirect()->back()->with('error', 'Não é possível pagar essa Fatura com saldo!');
        }

        $user = auth()->user();
        if($this->balance() < $invoice->value) {
            return redirect()->back()->with('info', 'Não há saldo disponível!');
        }

        $client = new Client();
        $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/payments/{$invoice->token_payment}/pixQrCode", [
            'headers' => [
                'accept'        => 'application/json',
                'access_token'  => $user->api_key,
                'User-Agent'    => env('APP_NAME')
            ],
            'verify' => false,
        ]);

        $body = (string) $response->getBody();
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);

            $payqrcode = $this->payQrCode($data['payload'], $invoice->value, $invoice->description);
            switch($payqrcode) {
                case 'AWAITING_BALANCE_VALIDATION':
                    return redirect()->back()->with('info', 'Saldo em análise! Aguarde alguns segundos.');
                    break;
                case 'AWAITING_INSTANT_PAYMENT_ACCOUNT_BALANCE':
                    return redirect()->back()->with('info', 'Transação em análise! Aguarde alguns segundos.');
                    break;
                case 'AWAITING_CRITICAL_ACTION_AUTHORIZATION':
                    return redirect()->back()->with('info', 'Transação aguardando autorização!');
                    break;
                case 'AWAITING_CHECKOUT_RISK_ANALYSIS_REQUEST':
                    return redirect()->back()->with('info', 'Transação aguardando análise!');
                    break;
                case 'AWAITING_CASH_IN_RISK_ANALYSIS_REQUEST':
                    return redirect()->back()->with('info', 'Transação aguardando análise!');
                    break;
                case 'SCHEDULED':
                    return redirect()->back()->with('success', 'Transação agendada com sucesso!');
                    break;
                case 'AWAITING_REQUEST':
                    return redirect()->back()->with('info', 'Transação aguardando análise!');
                    break;
                case 'REQUESTED':
                    return redirect()->back()->with('info', 'Transação solicitada!');
                    break;
                case 'DONE':
                    return redirect()->back()->with('success', 'Transação realizada com sucesso!');
                    break;
                case 'REFUSED':
                    return redirect()->back()->with('info', 'Transação recusada!');
                    break;
                case 'CANCELLED':
                    return redirect()->back()->with('info', 'Transação cancelada!');
                    break;
                default:
                    return redirect()->back()->with('info', 'Transação em análise!');
                    break;
            }
        } else {
            return redirect()->back()->with('error', 'Não foi possível pagar com o saldo!');
        }
    }

    private function payQrCode($payload, $value, $description, $date = null) {

        $client = new Client();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
                'User-Agent'   => env('APP_NAME')
            ],
            'json' => [
                'qrCode' => [
                    'payload' => $payload
                ], 
                'value'         => number_format($value, 2, '.', ''),
                'description'   => $description,
                'scheduleDate'  => $date ?? now(),
            ],
            'verify' => false
        ];
        
        $response = $client->post(env('API_URL_ASSAS') . 'v3/pix/qrCodes/pay', $options);
        $body = (string) $response->getBody();

        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
            return $data;
        } else {
            return false;
        }
    }
}
