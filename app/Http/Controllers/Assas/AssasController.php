<?php

namespace App\Http\Controllers\Assas;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AssasController extends Controller {

    public function createSalePayment($id) {

        $sale      = Sale::find($id);
        $product   = Product::find($sale->id_product);
        $payment   = Payment::find($sale->id_payment);
        $client    = User::find($sale->id_client);
        $user      = User::find($sale->id_seller);
    
        $commission = ($sale->value - $product->value_cost) - $product->value_rate;
        $value      = ($sale->value + $payment->value_rate);
    
        switch($sale->payment) {
            case 'BOLETO':
                return $this->invoiceBoleto($value, $commission, $sale, $user->wallet, $client);
                break;
            case 'CREDIT_CARD':
                return $this->invoiceCard($value, $commission, $sale, $user->wallet, $client);
                break;
            case 'PIX':
                return $this->invoiceBoleto($value, $commission, $sale, $user->wallet, $client);
                break;
            default:
                return false;
                break;
        }
    
        return false;
    }    

    private function invoiceBoleto($value, $commission, $sale, $wallet, $client) {

        $installmentValue       = $value / $sale->installments;
        $installmentCommission  = $commission / $sale->installments;

        if ($installmentValue < 390) {
            $firstInstallmentValue          = 390;
            $firstInstallmentCommission     = 0;
            $remainingValue                 = $value - $firstInstallmentValue;
            $remainingCommission            = $commission - $firstInstallmentCommission;
            $installmentValue               = $remainingValue / ($sale->installments - 1);
            $installmentCommission          = $remainingCommission / ($sale->installments - 1);
        } else {
            $firstInstallmentValue      = $installmentValue;
            $firstInstallmentCommission = $installmentCommission;
        }

        $customer = $this->createCustomer($client->name, $client->cpfcnpj, $client->phone, $client->email);

        for ($i = 1; $i <= $sale->installments; $i++) {

            $invoice                = new Invoice();
            $invoice->id_user       = $sale->id_client;
            $invoice->id_sale       = $sale->id;
            $invoice->id_product    = $sale->id_product;

            $invoice->name          = env('APP_NAME').' - Fatura';
            $invoice->description   = 'Fatura N°'.$i.' venda N°'.$sale->id;

            $invoice->value         = ($i == 1) ? $firstInstallmentValue : $installmentValue;
            $invoice->commission    = ($i == 1) ? $firstInstallmentCommission : $installmentCommission;
            $invoice->due_date      =  now()->addDay();
            $invoice->num           =  $i;
            $invoice->type          =  3;
            $invoice->status        =  0;

            $charge = $this->createCharge(
                $customer,
                $sale->payment, 
                ($i == 1) ? $firstInstallmentValue : $installmentValue, 
                'Fatura N°'.$i.' venda N°'.$sale->id,
                ($i == 1) ? now()->addDay() : now()->addDay(),
                null,
                $wallet,
                ($i == 1) ? $firstInstallmentCommission : $installmentCommission
            );

            if($charge) {
                $invoice->url_payment   = $charge['invoiceUrl'];
                $invoice->token_payment = $charge['id'];
            }

            $invoice->save();
        }

        return true;
    }

    private function invoiceCard($value, $commission, $sale, $wallet, $client) {

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

        if($invoice->save()) {
            return true;
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

        $charge = $this->createCharge($user->customer, 'PIX', '99.00', 'Assinatura -'.env('APP_NAME'), now()->addDay(), 0, 'afd76f74-6dd8-487b-b251-28205161e1e6', 20);
        if($charge != false) {

            $invoice = new Invoice();
            $invoice->name          = 'Mensalidade '.env('APP_NAME');
            $invoice->description   = 'Mensalidade '.env('APP_NAME');

            $invoice->id_user       = $user->id;
            $invoice->id_product    = 0;
            $invoice->value         = 99;
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

    private function createCustomer($name, $cpfcnpj, $mobilePhone, $email) {
        
        $client = new Client();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
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

    private function createCharge($customer, $billingType, $value, $description, $dueDate, $installments = null, $wallet= null, $commission = null) {

        $client = new Client();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
            ],
            'json' => [
                'customer'          => $customer,
                'billingType'       => $billingType,
                'value'             => number_format($value > 80 ? $value - 1 : $value, 2, '.', ''),
                'dueDate'           => $dueDate,
                'description'       => $description,
                'installmentCount'  => $installments != null ? $installments : 1,
                'installmentValue'  => $installments != null ? number_format(($value / intval($installments)), 2, '.', '') : $value,
            ],
            'verify' => false
        ];

        if ($wallet != null && $commission > 0) {
            if (!isset($options['json']['split'])) {
                $options['json']['split'] = [];
            }
        
            $options['json']['split'][] = [
                'walletId'          => $wallet,
                'totalFixedValue' => number_format($commission, 2, '.', '')
            ];
        }
        
        // if ($value > 80) {
        //     if (!isset($options['json']['split'])) {
        //         $options['json']['split'] = [];
        //     }
        
        //     $options['json']['split'][] = [
        //         'walletId'          => 'afd76f74-6dd8-487b-b251-28205161e1e6',
        //         'totalFixedValue'   => 1,
        //     ];
        // }

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

                $client = new Client();

                $options = [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'access_token' => env('API_TOKEN_ASSAS'),
                    ],
                    'json' => [
                        'name'          => $user->name,
                        'email'         => $user->email,
                        'cpfCnpj'       => $user->cpfcnpj,
                        'birthDate'     => $user->birth_date,
                        'mobilePhone'   => $user->phone,
                        'address'       => $user->address,
                        'addressNumber' => $user->num,
                        'province'      => $user->province,
                        'postalCode'    => $user->postalCode,
                        'companyType'   => strlen($user->cpfcnpj) === 11 ? '' : 'MEI',
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

                $sale   = Sale::where('id', $invoice->id_sale)->first();
                
                $product = $invoice->id_product != null ? Product::where('id', $invoice->id_product)->first() : false;
                if($product) {
                    if($invoice->num == 1) {
                        if($sale) {
                            $sale->status = 1;
                            $sale->save();
                        }
                    }
                }

                $seller = User::find($sale->id_seller);
                $totalSales = Sale::where('id_seller', $seller->id)->where('status', 1)->count();
                switch($totalSales) {
                    case 10:
                        $seller->level = 2;
                        break;
                    case 100:
                        $seller->level = 3;
                        break;
                    case 1000:
                        $seller->level = 4;
                        break;
                }
                $seller->save();

                if($invoice->type == 1) {

                    $key = $this->createKey($invoice->id);
                    if($key) {
                        return response()->json(['status' => 'success', 'message' => 'Operação Finalizada & ApiKey criada!']);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Operação Finalizada, mas houve um erro na criação da ApiKey!']);
                }

                $client = User::find($invoice->id_user);
                $this->sendWhatsapp(env('APP_URL').'/consulta/'.$sale->id, "Olá, ".$client->name."! Recebemos o seu pagamento, *segue link para acessar Faturas, consultar processos* e demais informações sobre seus contratos. \r\n\r\n PRONTO AGORA SÓ ACOMPANHAR 👇🏼📲", $client->phone);

                return response()->json(['status' => 'success', 'message' => 'Operação Finalizada!']);
            }
            
            return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura encontrada!']);
        }

        return response()->json(['status' => 'success', 'message' => 'Webhook não utilizado!']);
    }

    public function myDocuments() {
        $user = auth()->user();

        $client = new Client();
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => $user->api_key,
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
                'accept' => 'application/json',
                'access_token' => $user->api_key,
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
                'accept' => 'application/json',
                'access_token' => $user->api_key,
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
                'accept' => 'application/json',
                'access_token' => $user->api_key,
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
                    'accept' => 'application/json',
                    'access_token' => $user->api_key,
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

                    if($this->createSalePayment($sale->id)) {
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
}