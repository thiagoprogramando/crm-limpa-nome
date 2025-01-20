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
use App\Models\Coupon;

use Carbon\Carbon;
use GuzzleHttp\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssasController extends Controller {

    public function createSalePayment($id, $notification = null, $dueDate = null) {

        $sale      = Sale::find($id);
        $client    = User::find($sale->id_client);
        $seller    = User::find($sale->id_seller);
        $filiate   = User::where('id', $seller->filiate)->first() ?? null;
        
        $commission  = $sale->commission;

        if ($seller->type == 4) {
            $commission = 0;
        }

        switch ($sale->payment) {
            case 'BOLETO':
                return $this->invoiceBoleto($sale->value, $commission, $sale, $seller->wallet, $client, $filiate, $notification, $dueDate);
                break;
            case 'CREDIT_CARD':
                return $this->invoiceCard($sale->value, $commission, $sale, $seller->wallet, $client, $filiate);
                break;
            case 'PIX':
                return $this->invoiceBoleto($sale->value, $commission, $sale, $seller->wallet, $client, $filiate, $notification, $dueDate);
                break;
            default:
                return false;
                break;
        }

        return false;
    }

    private function invoiceBoleto($value, $commission, $sale, $wallet, $client, $filiate = null, $notification = null, $dueDate = null) {

        if (Invoice::where('id_sale', $sale->id)->count() >= 1) {
            return true;
        }

        $customer = $this->createCustomer($client->name, $client->cpfcnpj, $client->phone, $client->email);
        if ($customer == false) {
            return false;
        }

        if ($filiate) {
            $commission_filiate = max($sale->seller->fixed_cost - $filiate->fixed_cost, 0);
        } else {
            $commission_filiate = 0;
        }

        $charge = $this->createCharge($customer, $sale->payment, $value, 'Fatura N° 1 da venda N° '.$sale->id, $dueDate, null, $wallet, $commission, $filiate, $commission_filiate);  
        if ($charge == false) {
            return false;
        }
        
        $invoice = new Invoice();
        $invoice->id_user               = $sale->id_client;
        $invoice->id_sale               = $sale->id;
        $invoice->id_product            = $sale->id_product;
        $invoice->name                  = env('APP_NAME').' - Fatura';
        $invoice->description           = 'Fatura N° 1 da venda N° '.$sale->id;
        $invoice->url_payment           = $charge['invoiceUrl'];
        $invoice->token_payment         = $charge['id'];
        $invoice->value                 = $value;
        $invoice->commission            = $commission;
        $invoice->commission_filiate    = $commission_filiate;
        $invoice->due_date              = isset($dueDate) ? Carbon::parse($dueDate)->format('Y-m-d H:i:s') : now()->addDays(1)->format('Y-m-d H:i:s');
        $invoice->num                   = 1;
        $invoice->type                  = 3;
        $invoice->status                = 0;
        if ($invoice->save()) {

            if ($notification == true) {
                $message = "Prezado(a) {$sale->user->name}, estamos enviando o link para pagamento da sua contratação aos serviços da nossa assessoria. \r\n\r\n\r\n"."Consulte os termos do seu contrato aqui👇🏼 \r\n".env('APP_URL')."preview-contract/".$sale->id."\r\n\r\n\r\n"."PARA FAZER O PAGAMENTO CLIQUE NO LINK 👇🏼💳";
                return $this->sendInvoice($invoice->url_payment, $sale->id_client, $message, $sale->seller->api_token_zapapi);
            }
            
            return true;
        }
        
        return false;
    }        
    
    private function invoiceCard($value, $commission, $sale, $wallet, $client, $filiate = null) {

        if ($filiate) {
            $commission_filiate = $sale->seller->fixed_cost - $filiate->fixed_cost;
        } else {
            $commission_filiate = 0;
        }

        $invoice                = new Invoice();
        $invoice->id_user       = $sale->id_client;
        $invoice->id_sale       = $sale->id;
        $invoice->id_product    = $sale->id_product;

        $invoice->name          = env('APP_NAME').' - Fatura';
        $invoice->description   = 'Fatura única para venda N°'.$sale->id;

        $invoice->value                 = $value;
        $invoice->commission            = $commission;
        $invoice->commission_filiate    = $commission_filiate;
        $invoice->due_date              = now()->addDay();
        $invoice->num                   = 1;
        $invoice->type                  = 3;
        $invoice->status                = 0;

        $charge = $this->createCharge(
            $this->createCustomer($client->name, $client->cpfcnpj, $client->phone, $client->email),
            $sale->payment, 
            $value, 
            'Fatura única para venda N°'.$sale->id,
            now()->addDay(),
            $sale->installments,
            $wallet,
            $commission,
            $filiate,
            $commission_filiate
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

    private function sendInvoice($url_payment, $id, $message = null, $token = null) {

        $user = User::find($id);
        if ($user) {

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
                        'message'         => $message ?? "Prezado(a) ".$user->name.", estamos enviando o link para pagamento da sua contratação aos serviços da nossa assessoria.  \r\n\r\n\r\n FAZER O PAGAMENTO CLIQUE NO LINK 👇🏼💳 \r\n",
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
        if (!$user) {
            return redirect()->route('profile')->with('info', 'Verifique seus dados e tente novamente!');
        }
       
        $invoice = Invoice::where('id_user', $user->id)->where('type', 1)->where('status', 0)->exists();
        if ($invoice) {
            return redirect()->route('payments')->with('error', 'Você possui uma mensalidade em aberto!');
        }
        
        if ($user->customer == null) {
            
            $customer = $this->createCustomer($user->name, $user->cpfcnpj, $user->phone, $user->email);
            if ($customer) {
                $user->customer = $customer;
                $user->save();
            } else {
                return redirect()->route('profile')->with('info', 'Verifique seus dados e tente novamente!');
            }
        }

        $charge = $this->createCharge($user->customer, 'PIX', '49.99', 'Assinatura -'.env('APP_NAME'), now()->addDay(), null, env('WALLET_HEFESTO'), 20);
        if($charge <> false) {

            $invoice = new Invoice();
            $invoice->name          = 'Mensalidade '.env('APP_NAME');
            $invoice->description   = 'Mensalidade '.env('APP_NAME');

            $invoice->id_user       = $user->id;
            $invoice->id_product    = 0;
            $invoice->value         = 49.99;
            $invoice->commission    = 20;
            $invoice->status        = 0;
            $invoice->type          = 1;
            $invoice->num           = 1;
            $invoice->due_date      = now()->addDay();

            $invoice->url_payment   = $charge['invoiceUrl'];
            $invoice->token_payment = $charge['id'];

            if($invoice->save()) {
                return redirect($charge['invoiceUrl']);
            }
        }

        return redirect()->back()->with('error', 'Tivemos um pequeno problema, contate o suporte!');
    }

    public function createCustomer($name, $cpfcnpj, $mobilePhone, $email) {

        $user = User::where('cpfcnpj', $cpfcnpj)->first();
        if ($user && $user->customer) {
            return $user->customer;
        }
        
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
            
            $user->customer = $data['id'];
            $user->save();

            return $data['id'];
        } else {
            return false;
        }
    }

    public function createCharge($customer, $billingType, $value, $description, $dueDate = null, $installments = null, $wallet = null, $commission = null, $filiate = null, $commission_filiate = null) {
        try {
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
                    'dueDate'           => isset($dueDate) ? Carbon::parse($dueDate)->toIso8601String() : now()->addDays(1),
                    'description'       => $description,
                    'installmentCount'  => $installments != null ? $installments : 1,
                    'installmentValue'  => $installments != null ? number_format(($value / intval($installments)), 2, '.', '') : $value,
                    'isAddressRequired' => false
                ],
                'verify' => false
            ];
    
            if (($filiate <> null) && ($commission_filiate > 0)) {
                $options['json']['split'][] = [
                    'walletId'        => $filiate->wallet,
                    'totalFixedValue' => number_format($commission_filiate, 2, '.', '')
                ];
            }
    
            if ($value > 0) {
                $g7Commission = ($value == 49.99) ? 0 : $commission * 0.05;
                $commission   = ($commission - $g7Commission) - 1;
    
                if ($wallet == env('WALLET_HEFESTO')) {
                    $g7Commission = 0;
                }
    
                if ($g7Commission > 0 && $commission > 0) {
                    $options['json']['split'][] = [
                        'walletId'        => env('WALLET_G7'),
                        'totalFixedValue' => number_format($g7Commission, 2, '.', '')
                    ];
    
                    $options['json']['split'][] = [
                        'walletId'        => env('WALLET_HEFESTO'),
                        'totalFixedValue' => 1
                    ];
                }
    
                if (!empty($wallet) && $commission > 0) {
                    $options['json']['split'][] = [
                        'walletId'        => $wallet,
                        'totalFixedValue' => number_format($commission, 2, '.', '')
                    ];
                }
            }
    
            $response = $client->post(env('API_URL_ASSAS') . 'v3/payments', $options);
            $body = (string) $response->getBody();
    
            if ($response->getStatusCode() === 200) {
                $data = json_decode($body, true);
                return [
                    'id'            => $data['id'],
                    'invoiceUrl'    => $data['invoiceUrl'],
                ];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }    

    public function sendWhatsapp($link, $message, $phone, $token = null) {

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

    public function createKey($id) {

        $invoice = Invoice::find($id);
        if ($invoice) {
            
            $user = User::find($invoice->id_user);
            if ($user) {
                
                // if ($user->api_key != null) {
                //     return ['status' => true];
                // }

                if ($user->parent->afiliates()->count() % 2 === 0) {
                    $this->createCoupon($user->parent);
                }

                $user->status = 1;
                if ($user->save()) {
                    return ['status' => true];
                }
    
                // $client = new Client();
                // $options = [
                //     'headers' => [
                //         'Content-Type' => 'application/json',
                //         'access_token' => env('API_TOKEN_ASSAS'),
                //         'User-Agent'   => env('APP_NAME'),
                //     ],
                //     'json' => [
                //         'name'          => $user->name,
                //         'email'         => explode('@', $user->email)[0].rand(1, 999).'@ampaysolucoes.com',
                //         'cpfCnpj'       => $user->cpfcnpj,
                //         'birthDate'     => $user->birth_date,
                //         'mobilePhone'   => $user->phone,
                //         'address'       => '09750-730 Rua José Versolato, 101 - Vila da Saúde, São Bernardo do Campo',
                //         'addressNumber' => '101',
                //         'province'      => 'São Paulo',
                //         'postalCode'    => '09750730',
                //         'companyType'   => strlen($user->cpfcnpj) === 11 ? '' : 'MEI',
                //         'incomeValue'   => 1000,
                //         "accountStatusWebhook" => [
                //             "url"           => env('APP_URL')."api/webhook-account",
                //             "email"         => env('APP_EMAIL_SUPORT'),
                //             "interrupted"   => false,
                //             "enabled"       => true,
                //             "apiVersion"    => 3,
                //         ],
                //         "transferWebhook"      => [
                //             "url"           => env('APP_URL')."api/webhook-account",
                //             "email"         => env('APP_EMAIL_SUPORT'),
                //             "interrupted"   => false,
                //             "enabled"       => true,
                //             "apiVersion"    => 3,
                //         ],
                //         "paymentWebhook"       => [
                //             "url"           => env('APP_URL')."api/webhook-account",
                //             "email"         => env('APP_EMAIL_SUPORT'),
                //             "interrupted"   => false,
                //             "enabled"       => true,
                //             "apiVersion"    => 3,
                //         ],
                //         "invoiceWebhook"        => [
                //             "url"           => env('APP_URL')."api/webhook-account",
                //             "email"         => env('APP_EMAIL_SUPORT'),
                //             "interrupted"   => false,
                //             "enabled"       => true,
                //             "apiVersion"    => 3,
                //         ],
                //     ],
                //     'verify' => false,
                // ];
    
                // try {

                //     $response = $client->post(env('API_URL_ASSAS') . 'v3/accounts', $options);
                //     if ($response->getStatusCode() === 200) {
                        
                //         $body             = (string)$response->getBody();
                //         $data             = json_decode($body, true);
                //         $user->api_key    = $data['apiKey'];
                //         $user->wallet     = $data['walletId'];
                //         $user->wallet_id  = $data['id'];
                //         $user->status     = 2;
                //         $user->type       = 2;
                //         if ($user->save()) {
                //             return ['status' => true];
                //         }
                //     }

                //     return ['status' => false, 'error' => 'Erro desconhecido.'];

                // } catch (\GuzzleHttp\Exception\ClientException $e) {
                    
                //     $responseBody = (string)$e->getResponse()->getBody();
                //     $errorData = json_decode($responseBody, true);

                //     return [
                //         'status' => false,
                //         'error' => $errorData['errors'][0]['description'] ?? 'Erro desconhecido.',
                //     ];
                // }
            }
        }
    
        return ['status' => false, 'error' => 'Dados do usuário ou Faturas não localizados'];
    }
    
    public function createCoupon($parent) {

        $couponName = $this->generateCouponName($parent->name);

        $coupon                 = new Coupon();
        $coupon->name           = $couponName;
        $coupon->description    = 'Promoção 2 Afiliados 1 Mensalidade';
        $coupon->id_user        = $parent->id;
        $coupon->percentage     = 100;
        $coupon->qtd            = 1;
        if($coupon->save()) {
            $message =  "*Surpresa Especial para Você! 🎁* \r\n\r\n"
                        . "Como forma de agradecimento por ser um parceiro incrível, preparamos um *cupom de {$coupon->percentage}% de desconto* para você aproveitar na sua próxima mensalidade! \r\n\r\n"
                        . "Código do cupom: *{$couponName}* \r\n"
                        . "Não deixe essa oportunidade passar! Use o código na sua fatura e aproveite para economizar. \r\n"
                        . "Agradecemos por fazer parte da nossa jornada! \r\n\r\n";

            $assas = new AssasController();
            $assas->sendWhatsapp('', $message, $parent->phone, $parent->api_token_zapapi);
        }

        return true;
    }

    private function generateCouponName(string $name): string {

        $baseName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $existingCouponsCount = Coupon::where('name', 'like', "{$baseName}%")->count();
        return $existingCouponsCount > 0 ? "{$baseName}".($existingCouponsCount + 1) : $baseName;
    }

    public function webhook(Request $request) {

        $jsonData = $request->json()->all();
        if ($jsonData['event'] === 'PAYMENT_CONFIRMED' || $jsonData['event'] === 'PAYMENT_RECEIVED') {
            
            $token = $jsonData['payment']['id'];
            $invoice = Invoice::where('token_payment', $token)->where('status', 0)->first();
            if ($invoice) {

                $invoice->status = 1;
                if (!$invoice->save()) {
                    return response()->json(['status' => 'error', 'message' => 'Não foi possível confirmar o pagamento da fatura!']);
                }

                if ($invoice->type == 1) {

                    $key = $this->createKey($invoice->id);
                    if ($key == true || $key == 1) {
                        return response()->json(['status' => 'success', 'message' => 'Operação Finalizada & ApiKey criada!']);
                    }

                    return response()->json(['status' => 'success', 'message' => 'Operação Finalizada, mas houve um erro na criação da ApiKey!']);
                }

                $sale = Sale::where('id', $invoice->id_sale)->first();
                $sale->guarantee = Carbon::parse($sale->guarantee)->addMonths(12);
                
                // if ($sale && ($sale->id_payment <> null)) {
                //     $invoices = $this->createSalePayment($sale->id);
                //     if($invoices == false) {
                //         return response()->json(['status' => 'error', 'message' => 'Não foi possível Gerar Faturas para essa venda!']);
                //     }
                // }
                
                $product = $invoice->id_product <> null ? Product::where('id', $invoice->id_product)->first() : false;
                if ($product && $invoice->num == 1 && $sale) {
                        
                    $sale->status = 1;
                    $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
                    if ($list) {
                        $sale->id_list = $list->id;
                    }
                }

                if ($sale) {
                    
                    $notification               = new Notification();
                    $notification->name         = 'Fatura N°'.$invoice->id;
                    $notification->description  = 'Faturas recebida com sucesso!';
                    $notification->type         = 1;
                    $notification->id_user      = $invoice->id_seller; 
                    $notification->save();

                    $seller = User::find($sale->id_seller);
                    if ($seller && $seller->type <> 4) {

                        $totalSales = Sale::where('id_seller', $seller->id)->where('status', 1)->count();
                        switch($totalSales) {
                            case 10:
                                $seller->level = 2;
                                $nivel = 'CONSULTOR'; 
                                break;
                            case 30:
                                $seller->level = 3;
                                $nivel = 'CONSULTOR LÍDER'; 
                                break;
                            case 50:
                                $seller->level = 4;
                                $nivel = 'REGIONAL'; 
                                break;
                            case 100:
                                $seller->level = 5;
                                $nivel = 'GERENTE REGIONAL'; 
                                break;
                        }

                        $seller->save();

                        if (!empty($nivel)) {
                            $notification               = new Notification();
                            $notification->name         = 'Novo nível!';
                            $notification->description  = $seller->name.' Alcançou o nível: '.$nivel;
                            $notification->type         = 2;
                            $notification->id_user      = 14; 
                            $notification->save();
                        }
                    }

                    $sale->save();
                }

                $client = User::find($invoice->id_user);
                if ($client && $invoice->num == 1) {
                    $this->sendWhatsapp(env('APP_URL').'login-cliente', "Olá, ".$client->name."!\r\n\r\nAgradecemos pelo seu pagamento! \r\n\r\n\r\n Tenha a certeza de que sua situação está em boas mãos. \r\n\r\n\r\n *Nos próximos 30 dias úteis*, nossa equipe especializada acompanhará de perto todo o processo para garantir que seu nome seja limpo o mais rápido possível. \r\n\r\n\r\n Estamos à disposição para qualquer dúvida ou esclarecimento. \r\n\r\n Você pode acompanhar o processo acessando nosso sistema no link abaixo: \r\n\r\n", $client->phone, $seller->api_token_zapapi);
                } else {
                    $this->sendWhatsapp(env('APP_URL').'login-cliente', $client->name."!\r\n\r\nAgradecemos por manter o compromisso e realizar o pagamento do boleto, o que garante a continuidade e a validade da garantia do serviço. \r\n\r\n Acesse o Painel do cliente👇", $client->phone, $seller->api_token_zapapi);
                }

                if ($seller && $invoice->num == 1 && $invoice->type == 3) {
                    $message =  "Olá, {$seller->name}, Espero que esteja bem! 😊\r\n\r\n"
                                . "Gostaria de informar que uma nova venda foi realizada com sucesso.🤑💸\r\n\r\n"
                                . "Cliente: {$client->name}\r\n"
                                . "Produto/Serviço: {$product->name}\r\n"
                                . "Valor Total: R$ " . number_format($sale->value, 2, ',', '.') . "\r\n"
                                . "Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
                                . "Obrigado pelo excelente trabalho!🥇\r\n\r\n";

                    $this->sendWhatsapp("", $message, $seller->phone, $seller->api_token_zapapi);
                }

                if ($seller && $invoice->num != 1 && $invoice->type == 3 && $invoice->commission > 0) {
                    $message =  "Olá, {$seller->name}, Espero que esteja bem! 😊\r\n\r\n"
                                . "Gostaria de informar que uma nova COMISSÃO FOI RECEBIDA com sucesso.🤑💸\r\n\r\n"
                                . "Cliente: {$client->name}\r\n"
                                . "Produto/Serviço: {$product->name}\r\n"
                                . "Fatura N° {$invoice->num}\r\n"
                                . "Valor apróximado: R$ " . number_format($invoice->commission, 2, ',', '.') . "\r\n"
                                . "Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
                                . "Obrigado pelo excelente trabalho!🥇\r\n\r\n";

                    $this->sendWhatsapp("", $message, $seller->phone, $seller->api_token_zapapi);
                }
                
                return response()->json(['status' => 'success', 'message' => 'Operação Finalizada!']);
            }

            $sales = Sale::where('token_payment', $token)->where('status', 0)->get();
            if ($sales->isNotEmpty()) {

                Sale::whereIn('id', $sales->pluck('id'))
                    ->update(['status' => 1]);
            
                return response()->json(['success' => 'success', 'message' => 'Status das vendas atualizado com sucesso!']);
            }
            
            return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura/Venda encontrada!']);
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
                            $wallet     = $invoice->sale->seller->wallet ?? null;

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
                                    . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n";

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
                            $wallet     = $invoice->sale->seller->wallet ?? null;

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
                                    . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n";

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
                            $wallet     = $invoice->sale->seller->wallet ?? null;

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
                                    . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n";

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
                            $wallet     = $invoice->sale->seller->wallet ?? null;

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
                                    . "Além disso, seu nome voltará a ficar sujo e toda a boa reputação que trabalhamos para recuperar para você será perdida. Não deixe essa oportunidade passar e evite impactos negativos em sua situação financeira e reputacional. \r\n\r\n\r\n";

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

    public function addDiscount($id, $value, $dueDate, $commission = null, $wallet = null) {
        
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
        
        if(env('APP_ENV') <> 'local') {
            if ($commission > 0) {
                if (!isset($options['json']['split'])) {
                    $options['json']['split'] = [];
                }
        
                $options['json']['split'][] = [
                    'walletId'        => $wallet,
                    'totalFixedValue' => number_format($commission, 2, '.', '')
                ];
            }
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

        return [];
        
        // $user = auth()->user();

        // if(empty($user->api_key)) {
        //     return [];
        // }

        // $client = new Client();
        // $options = [
        //     'headers' => [
        //         'Content-Type' => 'application/json',
        //         'access_token' => $user->api_key,
        //         'User-Agent'   => env('APP_NAME')
        //     ],
        //     'verify' => false
        // ];

        // $response = $client->get(env('API_URL_ASSAS') . 'v3/myAccount/documents', $options);
        // $body = (string) $response->getBody();
        
        // if ($response->getStatusCode() === 200) {
        //     $data = json_decode($body, true);
    
        //     if (isset($data['data'])) {
        //         return $data['data'];
        //     } else {
        //         return [];
        //     }
        // } else {
        //     return false;
        // }
    }

    public function receivable() {

        $client     = new Client();
        $user       = auth()->user();
        $startDate  = $user->created_at->toDateString();
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
                if ($sale) {

                    $sale->status_contract = 1;
                    if($sale->save()) {
                        return response()->json(['message' => 'Contrato assinado!'], 200);
                    }

                    return response()->json(['message' => 'Não foi possível atualizar a Venda!'], 200);
                }
            }

            return response()->json(['message' => 'Nenhuma operação finalizada!'], 200);
        } else {
            
            return response()->json(['error' => 'Token e Event não localizados!'], 200);
        }

        return response()->json(['error' => 'Webhook não utilizado!'], 200);
    }

    public function webhookAccount(Request $request) {

        $jsonData = $request->json()->all();
        if (isset($jsonData['accountStatus']) && isset($jsonData['accountStatus']['id'])) {
            $user = User::where('wallet_id', $jsonData['accountStatus']['id'])->first();

            if ($user) {
                switch ($jsonData['event'] ?? '') {
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
        }

        return response()->json(['status' => 'success', 'message' => 'Não há nenhuma conta associada ao conteúdo da requisição!']);
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

    // public function requestInvoice($id) {

    //     $invoices = $this->createSalePayment($id, true);
    //     if ($invoices <> false) {
    //         return redirect()->back()->with('success', 'Faturas geradas com sucesso!');
    //     }

    //     return redirect()->back()->with('error', 'Não foi possível Gerar Faturas!');
    // } 

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

    public function createPayment(Request $request) {

        DB::beginTransaction();

        try {
            
            $user       = $this->validateUser($request->customer);
            $filiate    = $this->validateFiliate($user);

            $sales      = $this->getSales($request['ids']);
            $totalValue = $this->calculateTotalValue($sales, $user);

            $commission = $user->filiate ? ($user->fixed_cost - ($filiate->cost ?? 150)) : 0;

            $charge = $this->createCharge(
                $user->customer,
                'PIX',
                $totalValue,
                'Fatura referente às vendas N° - '.implode(', ', $request['ids']),
                now()->addDay(),
                1,
                null,
                0,
                $filiate,
                $commission
            );

            if (!$charge || empty($charge['id'])) {
                return $this->jsonError('Erro ao criar fatura!', 500);
            }

            $this->createInvoice($charge, $user, $totalValue, 'Fatura referente às vendas N° - '.implode(', ', $request['ids']));

            Sale::whereIn('id', $request['ids'])->update(['token_payment' => $charge['id']]);

            DB::commit();

            return $this->jsonSuccess('Fatura criada com sucesso.', [
                'invoiceUrl' => $charge['invoiceUrl'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonError('Erro no processo: ' . $e->getMessage(), 500);
        }
    }

    private function validateUser($customer) {
        $user = User::where('customer', $customer)->first();
        if (!$user || empty($user->customer)) {
            throw new \Exception('Verifique seus dados e tente novamente');
        }
        return $user;
    }

    private function validateFiliate($user) {
        if (!$user->filiate) {
            return null;
        }

        $filiate = User::find($user->filiate);
        if (!$filiate) {
            throw new \Exception('Verifique seus dados e tente novamente');
        }

        return $filiate;
    }

    private function getSales($ids) {
        $sales = Sale::whereIn('id', $ids)->with('product')->get();
        if ($sales->isEmpty()) {
            throw new \Exception('Nenhuma venda encontrada.');
        }
        return $sales;
    }

    private function calculateTotalValue($sales, $user) {
        $totalValue = 0;

        foreach ($sales as $sale) {
            if ($sale->product) {
                $totalValue += $user->fixed_cost > 0
                    ? $user->fixed_cost + $sale->product->value_rate
                    : $sale->product->value_cost + $sale->product->value_rate;
            }
        }

        if ($totalValue <= 0) {
            throw new \Exception('Valor total inválido.');
        }

        return $totalValue;
    }

    private function createInvoice($charge, $user, $totalValue, $description) {
        $invoice = new Invoice();
        $invoice->name = $description;
        $invoice->description = $description;
        $invoice->id_user = $user->id;
        $invoice->id_product = 0;
        $invoice->value = $totalValue;
        $invoice->commission = 0;
        $invoice->status = 0;
        $invoice->type = 2;
        $invoice->num = 1;
        $invoice->due_date = now()->addDay(2);
        $invoice->url_payment = $charge['invoiceUrl'];
        $invoice->token_payment = $charge['id'];
        $invoice->save();
    }

    private function jsonSuccess($message, $data = []) {
        return response()->json(array_merge(['success' => true, 'message' => $message], $data));
    }

    private function jsonError($message, $code) {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

}
