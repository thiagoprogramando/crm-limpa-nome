<?php

namespace App\Http\Controllers\Assas;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Coupon;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssasController extends Controller {

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
                Log::error('Ao enviar notificação ao cliente: '. $e->getMessage());
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
       
        $invoice = Invoice::where('user_id', $user->id)->where('type', 1)->whereIn('status', [0, 2])->exists();
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

        $charge = $this->createCharge($user->customer, 'PIX', '49.99', 'Assinatura -'.env('APP_NAME'), now()->addDay(), null, env('WALLET_EXPRESS'), 23, env('WALLET_G7'), 23);
        if($charge <> false) {

            $invoice = new Invoice();
            $invoice->name          = 'Mensalidade '.env('APP_NAME');
            $invoice->description   = 'Mensalidade '.env('APP_NAME');

            $invoice->user_id            = $user->id;
            $invoice->product_id         = 0;
            $invoice->value              = 49.99;
            $invoice->commission_seller  = 23;
            $invoice->commission_filiate = 23;
            $invoice->status             = 2;
            $invoice->type               = 1;
            $invoice->num                = 1;
            $invoice->due_date           = now()->addDay();

            $invoice->payment_url   = $charge['invoiceUrl'];
            $invoice->payment_token = $charge['id'];

            $user->status = 2;

            if($invoice->save() && $user->save()) {
                return redirect($charge['invoiceUrl']);
            }
        }

        return redirect()->back()->with('error', 'Tivemos um pequeno problema, contate o suporte!');
    }

    public function createCustomer($name, $cpfcnpj, $mobilePhone = null, $email = null) {

        try {
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
                    'name'        => $name,
                    'cpfCnpj'     => $cpfcnpj,
                    'mobilePhone' => $mobilePhone,
                    'email'       => $email,
                ],
                'verify' => false
            ];
    
            $response = $client->post(env('API_URL_ASSAS') . 'v3/customers', $options);
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
    
            if ($response->getStatusCode() === 200 && isset($data['id'])) {
                if ($user) {
                    $user->customer = $data['id'];
                    $user->save();
                }
                return $data['id'];
            } else {
                Log::error("Erro na criação do cliente: " . json_encode($data));
                return false;
            }
    
        } catch (RequestException $e) {
            Log::error("Erro de requisição na API Assas CreateCustomer: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Erro geral na função createCustomer: " . $e->getMessage());
            return false;
        }
    }

    public function createCharge($customer, $billingType, $value, $description, $dueDate = null, $commissions = null) {
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
                    'isAddressRequired' => false,
                    'split'             => $commissions,
                ],
                'verify' => false
            ];
    
            $response = $client->post(env('API_URL_ASSAS') . 'v3/payments', $options);
            $body = (string) $response->getBody();
    
            if ($response->getStatusCode() === 200) {
                $data = json_decode($body, true);
                return [
                    'id'            => $data['id'],
                    'invoiceUrl'    => $data['invoiceUrl'],
                    'splits'        => $data['split'] ?? [],
                ];
            } else {
                Log::error('Erro ao Gerar Fatura (Controller AssasController) de '.$customer.': ' . $body);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao Gerar Fatura (Controller AssasController) de '.$customer.': ' . $e->getMessage());
            return false;
        }
    }
    
    public function cancelCharge($id) {
        
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'access_token'  => env('API_TOKEN_ASSAS'),
                    'User-Agent'    => env('APP_NAME')
                ],
                'verify' => false
            ];

            $response = $client->delete(env('API_URL_ASSAS') . 'v3/payments/' . $id, $options);
            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                return isset($data['deleted']) && $data['deleted'] === true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar fatura (ID: ' . $id . '): ' . $e->getMessage());
            return false;
        }
    }

    public function updateCharge($id, $dueDate) {

        $client = new Client();
        
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => env('API_TOKEN_ASSAS'),
                'User-Agent'   => env('APP_NAME')
            ],
            'json' => [
                'dueDate'     => $dueDate,
            ],
            'verify' => false
        ];
    
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
            Log::error("Erro AssasController updateCharge: " . json_decode($responseBody, true));
            return false;
        } catch (\Exception $e) {    
            return false;
        }
  
        return false;
    }

    public function sendWhatsapp($link, $message, $phone, $token = null) {

        $client = new Client();
        $url    = $token ?: env('APP_TOKEN_WHATSAPP');
    
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
            
            $user = User::find($invoice->user_id);
            if ($user) {
                // if (($user->parent->afiliates()->count() % 5 === 0) &&  $user->invoices()->where('type', 1)->count() <= 1 && $user->created_at > Carbon::create(2025, 8, 5)) {
                //     $this->createCoupon($user->parent, 'Promoção 10 Afiliados 1 Mensalidade!');
                // }

                $user->status = 1;
                if ($user->save()) {
                    return ['status' => true];
                }
            }
        }
    
        return ['status' => false, 'error' => 'Dados do usuário ou Faturas não localizados'];
    }
    
    public function createCoupon($parent, $description) {

        $couponName = $this->generateCouponName($parent->name);

        $coupon                 = new Coupon();
        $coupon->name           = $couponName;
        $coupon->description    = $description;
        $coupon->user_id        = $parent->id;
        $coupon->percentage     = 100;
        $coupon->qtd            = 1;
        if($coupon->save()) {
            $message =  "*Surpresa Especial para Você! 🎁* \r\n\r\n"
                        . "Como forma de agradecimento por ser um parceiro incrível, preparamos um *cupom de {$coupon->percentage}% de desconto* para você aproveitar na sua próxima mensalidade! \r\n\r\n"
                        . "Código do cupom: *{$couponName}* \r\n"
                        . "Não deixe essa oportunidade passar! Use o código na sua fatura e aproveite para economizar. \r\n"
                        . "Agradecemos por fazer parte da nossa jornada! \r\n\r\n";

            $assas = new AssasController();
            $assas->sendWhatsapp('', $message, $parent->phone, $parent->getTokenWhatsapp());
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
            $invoice = Invoice::where('payment_token', $token)->whereIn('status', [0, 2])->first();
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

                $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();

                $sale = $invoice->sale;
                if ($sale) {
                    if ($invoice->product && $invoice->num == 1) {
                        $sale->status = 1;
                        $sale->guarantee = $sale->guarantee
                            ? Carbon::parse($sale->guarantee)->addMonths(3)
                            : now()->addMonths(3);

                        if ($list) {
                            $sale->list_id = $list->id;
                        }
                    }

                    $sale->save();

                    Notification::create([
                        'name'        => 'Fatura N°'.$invoice->id,
                        'description' => 'Faturas recebida com sucesso!',
                        'type'        => 1,
                        'user_id'     => $invoice->seller_id,
                    ]);
                }


                $sales = Sale::where('payment_token', $token)->whereIn('status', [0, 2])->get();
                if ($sales->isNotEmpty()) {

                    Sale::whereIn('id', $sales->pluck('id'))
                        ->update(['status' => 1, 'list_id' => $list->id]);
                
                    return response()->json(['success' => 'success', 'message' => 'Status das vendas atualizado com sucesso!']);
                }

                $client = $invoice->user;
                if ($client) {
                    if ($invoice->num == 1) {
                        $message = "Olá, {$client->name}!\r\n\r\n".
                                "Agradecemos pelo seu pagamento! \r\n\r\n".
                                "Tenha a certeza de que sua situação está em boas mãos. \r\n\r\n".
                                "*Nos próximos 30 dias úteis*, nossa equipe especializada acompanhará ".
                                "de perto todo o processo para garantir que seu nome seja limpo o mais rápido possível. \r\n\r\n".
                                "Estamos à disposição para qualquer dúvida ou esclarecimento. \r\n\r\n".
                                "Você pode acompanhar o processo acessando nosso sistema no link abaixo: \r\n\r\n";
                    } else {
                        $message = "Olá, {$client->name}!\r\n\r\n".
                                "Agradecemos por manter o compromisso e realizar o pagamento do boleto, ".
                                "o que garante a continuidade e a validade da garantia do serviço. \r\n\r\n".
                                "Acesse o Painel do cliente👇";
                    }

                    $this->sendWhatsapp(
                        env('APP_URL').'login-cliente',
                        $message,
                        $client->phone,
                        $client->getTokenWhatsapp()
                    );
                }

                $seller = $sale->seller;
                if ($seller && $invoice->type == 3 && $invoice->product) {
                    if ($invoice->num == 1) {
                        
                        $message = "Olá, {$seller->name}, espero que esteja bem! 😊\r\n\r\n"
                                . "Uma nova *venda* foi realizada com sucesso.🤑💸\r\n\r\n"
                                . "👤 Cliente: {$client->name}\r\n"
                                . "📦 Produto/Serviço: {$invoice->product->name}\r\n"
                                . "💰 Valor Total: R$ " . number_format($sale->value, 2, ',', '.') . "\r\n"
                                . "📅 Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
                                . "Obrigado pelo excelente trabalho!🥇";
                    } elseif ($invoice->commission > 0) {
                        
                        $message = "Olá, {$seller->name}, espero que esteja bem! 😊\r\n\r\n"
                                . "Uma nova *comissão* foi recebida com sucesso.🤑💸\r\n\r\n"
                                . "👤 Cliente: {$client->name}\r\n"
                                . "📦 Produto/Serviço: {$invoice->product->name}\r\n"
                                . "🧾 Fatura Nº {$invoice->num}\r\n"
                                . "💰 Valor aproximado: R$ " . number_format($invoice->commission, 2, ',', '.') . "\r\n"
                                . "📅 Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
                                . "Continue assim, parabéns pelo trabalho!🥇";
                    }

                    if (!empty($message)) {
                        $this->sendWhatsapp("", $message, $seller->phone, $seller->getTokenWhatsapp());
                    }
                }
                
                return response()->json(['status' => 'success', 'message' => 'Operação Finalizada!']);
            }
            
            return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura/Venda encontrada!']);
        }

        return response()->json(['status' => 'success', 'message' => 'Webhook não utilizado!']);
    }

    public function updateInvoice($id, $value, $dueDate, $callback = null, $commission = null, $wallet = null) {
        
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
                if ($callback) {

                    $invoice = Invoice::where('payment_token', $id)->first();
                    if ($invoice) {
                        $invoice->payment_token = $data['id'];
                        $invoice->url_payment   = $data['invoiceUrl'];
                        $invoice->due_date      = $dueDate;
                        $invoice->save();
                    }

                    return redirect()->back()->with('success', 'Fatura atualizada com sucesso!');
                } else {
                    return [
                        'id'         => $data['id'],
                        'invoiceUrl' => $data['invoiceUrl'],
                    ];
                }
            }
    
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($callback) {
                return redirect()->back()->with('error', 'Não foi possível atualizar essa Fatura!');
            } else {
                return false;
            }
        } catch (\Exception $e) {    
            if ($callback) {
                return redirect()->back()->with('error', 'Não foi possível atualizar essa Fatura!');
            } else {
                return false;
            }
        }
  
        if ($callback) {
            return redirect()->back()->with('error', 'Não foi possível atualizar essa Fatura!');
        } else {
            return false;
        }
    } 

    public function myDocuments() {
        try {
            
            $user = auth()->user();
    
            if (empty($user->token_key)) {
                return [];
            }
    
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'access_token' => $user->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false
            ];
    
            $response = $client->get(env('API_URL_ASSAS') . 'v3/myAccount/documents', $options);
            $body = (string) $response->getBody();
    
            if ($response->getStatusCode() === 200) {
                $data = json_decode($body, true);
    
                return $data['data'] ?? [];
            }
        } catch (\Exception $e) {
            return [];
        }
    
        return [];
    }    

    public function receivable($startDate = null, $finishDate = null, $offset = 0) {

        $client     = new Client();
        $user       = auth()->user();
        $startDate  = $startDate  ?? now()->toDateString();
        $finishDate = $finishDate ?? now()->toDateString();

        $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/financialTransactions?limit=100&startDate={$startDate}&finishDate={$finishDate}&offset={$offset}&order=asc", [
            'headers' => [
                'accept'        => 'application/json',
                'access_token'  => $user->token_key,
                'User-Agent'    => env('APP_NAME')
            ],
            'verify' => false,
        ]);

        $body = (string) $response->getBody();
        if ($response->getStatusCode() === 200) {
            $data = json_decode($body, true);
            return [
                'data'    => $data['data'],
                'hasMore' => $data['hasMore'],
                'offset'  => $offset
            ];
        } else {
            return [
                'data'    => [],
                'hasMore' => false,
                'offset'  => $offset
            ];
        }
    }

    public function balance($id = null) {
        try {
            $client = new Client();

            $user = $id ? User::find($id) : auth()->user();

            if (!$user) {
                throw new \Exception('Usuário não encontrado.');
            }

            $response = $client->request('GET', env('API_URL_ASSAS') . 'v3/finance/balance', [
                'headers' => [
                    'accept'       => 'application/json',
                    'access_token' => $user->token_key,
                    'User-Agent'   => env('APP_NAME'),
                ],
                'verify' => false,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                return $data['balance'] ?? 0;
            }

            return false;
        } catch (\Throwable $e) {
            // Log::error('Erro ao buscar saldo de '.$user->name.': ' . $e->getMessage());
            return false;
        }
    }

    public function statistics() {
        try {
            $client = new Client();
            $user = auth()->user();

            if (!$user) {
                throw new \Exception('Usuário não autenticado.');
            }

            $response = $client->request('GET', env('API_URL_ASSAS') . 'v3/finance/split/statistics', [
                'headers' => [
                    'accept'       => 'application/json',
                    'access_token' => $user->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                return $data['income'] ?? 0;
            }

            return false;
        } catch (\Throwable $e) {
            // Log::error('Erro ao buscar estatísticas financeiras de '.$user->name.': ' . $e->getMessage());
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
                    'access_token'  => $user->token_key,
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

    public function withdrawSend($key, $value, $type) {

        $client = new Client();
        
        $user = auth()->user();
        try {
            $response = $client->request('POST', env('API_URL_ASSAS').'v3/transfers', [
                'headers' => [
                    'accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'access_token' => $user->token_key,
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
        try {

            $client = new Client();
            $user = auth()->user();
            $startDate = $user->created_at->toDateString();
            $finishDate = now()->toDateString();
    
            $response = $client->request('GET', env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
                'headers' => [
                    'accept'       => 'application/json',
                    'access_token' => $user->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false,
            ]);
    
            if ($response->getStatusCode() !== 200) {
                return [];
            }
    
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
    
        } catch (\Exception $e) {
            // Log::error('Erro ao buscar extrato de '.$user->name.': ' . $e->getMessage());
            return [];
        }
    }

    public function cancelInvoice($token) {
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'access_token'  => env('API_TOKEN_ASSAS'),
                    'User-Agent'    => env('APP_NAME')
                ],
                'verify' => false
            ];

            $response = $client->delete(env('API_URL_ASSAS') . 'v3/payments/' . $token, $options);

            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);

                return isset($data['deleted']) && $data['deleted'] === true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar fatura (Token: ' . $token . '): ' . $e->getMessage());
            return false;
        }
    }

    public function payMonthly($id) {
        try {
            $invoice = Invoice::find($id);
            if (!$invoice || $invoice->status == 1) {
                return redirect()->back()->with('error', 'Não é possível pagar essa Fatura com saldo!');
            }
    
            $user = User::find($invoice->user_id);
            if (!$user) {
                return redirect()->back()->with('info', 'Dados não localizados!');
            }
    
            $balance = $this->balance();
            if ($balance !== false && $balance < $invoice->value) {
                return redirect()->back()->with('info', 'Não há saldo disponível!');
            }
    
            $client = new Client();
            $response = $client->request('GET', env('API_URL_ASSAS') . "v3/payments/{$invoice->payment_token}/pixQrCode", [
                'headers' => [
                    'accept'       => 'application/json',
                    'access_token' => $user->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false,
            ]);
    
            if ($response->getStatusCode() !== 200) {
                return redirect()->back()->with('error', 'Não foi possível pagar com o saldo!');
            }
    
            $data = json_decode((string) $response->getBody(), true);
            $payqrcode = $this->payQrCode($data['payload'], $invoice->value, $invoice->description);
    
            $statusMessages = [
                'AWAITING_BALANCE_VALIDATION' => 'Saldo em análise! Aguarde alguns segundos.',
                'AWAITING_INSTANT_PAYMENT_ACCOUNT_BALANCE' => 'Transação em análise! Aguarde alguns segundos.',
                'AWAITING_CRITICAL_ACTION_AUTHORIZATION' => 'Transação aguardando autorização!',
                'AWAITING_CHECKOUT_RISK_ANALYSIS_REQUEST' => 'Transação aguardando análise!',
                'AWAITING_CASH_IN_RISK_ANALYSIS_REQUEST' => 'Transação aguardando análise!',
                'SCHEDULED' => 'Transação agendada com sucesso!',
                'AWAITING_REQUEST' => 'Transação aguardando análise!',
                'REQUESTED' => 'Transação solicitada!',
                'DONE' => 'Transação realizada com sucesso!',
                'REFUSED' => 'Transação recusada!',
                'CANCELLED' => 'Transação cancelada!',
            ];
    
            return redirect()->back()->with($payqrcode === 'DONE' ? 'success' : 'info', $statusMessages[$payqrcode] ?? 'Transação em análise!');
    
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocorreu um erro inesperado! ' . $e->getMessage());
        }
    }    

    private function payQrCode($payload, $value, $description, $date = null) {
        try {
            $client = new Client();

            $options = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'access_token'  => env('API_TOKEN_ASSAS'),
                    'User-Agent'    => env('APP_NAME')
                ],
                'json' => [
                    'qrCode' => [
                        'payload' => $payload
                    ],
                    'value'        => number_format($value, 2, '.', ''),
                    'description'  => $description,
                    'scheduleDate' => $date ?? now(),
                ],
                'verify' => false
            ];

            $response = $client->post(env('API_URL_ASSAS') . 'v3/pix/qrCodes/pay', $options);
            $body = (string) $response->getBody();

            if ($response->getStatusCode() === 200) {
                $data = json_decode($body, true);
                return $data;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Erro ao criar QR Code de pagamento: ' . $e->getMessage());
            return false;
        }
    }

    public function accountStatus($token_key) {
        try {
            $client = new Client();
            
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'access_token' => $token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false
            ];
    
            $response = $client->get(env('API_URL_ASSAS') . 'v3/myAccount/status/', $options);
            $body = (string) $response->getBody();
    
            if ($response->getStatusCode() === 200) {
                return json_decode($body, true);
            } else {
                return false;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return false;
        }
    }

    public function createPayment(Request $request) {

        DB::beginTransaction();
        try {

            $user = $this->validateUser($request->user_id);
            if (!$user->name || !$user->cpfcnpj) {
                throw new \Exception('Verifique seus dados e tente novamente!');
            }

            $product = $this->validateProduct($request->product_id);
            $sponsor = $this->validateSponsor($user);
            $sales   = $this->getSales($request['ids']);

            $saleIds     = $sales->pluck('id')->toArray();
            $saleNumbers = implode(', ', $saleIds);

            $totalValue = $this->calculateTotalValue($sales, $user, $product);
            $unitCost   = $user->fixed_cost ?? $product->value_cost;
            $totalCost  = $unitCost * count($sales);

            $commissions        = [];
            $sponsorCommission  = 0;
            $uuid               = Str::uuid();

            if ($sponsor) {
                $unitSponsorProfit = ($unitCost - $sponsor->fixed_cost);
                if ($unitSponsorProfit > 0) {

                    $sponsorCommission = ($unitSponsorProfit * count($sales));
                    $commissions[] = [
                        'walletId'          => $sponsor->token_wallet,
                        'fixedValue'        => number_format($sponsorCommission  - 3, 2, '.', ''),
                        'externalReference' => $uuid,
                        'description'       => 'Comissão de Patrocinador para venda N° - ' . $saleNumbers,
                    ];
                    $commissions[] = [
                        'walletId'          => env('WALLET_G7'),
                        'fixedValue'        => number_format($totalCost - $sponsorCommission, 2, '.', ''),
                        'externalReference' => $uuid,
                        'description'       => 'Comissão Associação para venda '.env('APP_NAME').' N° - ' . $saleNumbers,
                    ];
                } else {

                    $commissions[] = [
                        'walletId'          => env('WALLET_G7'),
                        'fixedValue'        => number_format($totalCost - 3, 2, '.', ''),
                        'externalReference' => $uuid,
                        'description'       => 'Comissão Associação para venda N° - ' . $saleNumbers,
                    ];
                }
            } else {

                $commissions[] = [
                    'walletId'          => env('WALLET_G7'),
                    'fixedValue'        => number_format($totalCost - 3, 2, '.', ''),
                    'externalReference' => $uuid,
                    'description'       => 'Comissão Associação para venda '.env('APP_NAME').' N° - ' . $saleNumbers,
                ];
            }

            $commissions[] = [
                'walletId'          => env('WALLET_EXPRESS'),
                'fixedValue'        => number_format(1, 2, '.', ''),
                'externalReference' => $uuid,
                'description'       => 'Comissão % para venda '.env('APP_NAME').' N° - ' . $saleNumbers,
            ];

            $this->cancelPreviousInvoices($sales, $request->token);

            try {
                $customer = $this->createCustomer($user->name, $user->cpfcnpj);

                $charge = $this->createCharge($customer, 'PIX', $totalValue, 'Fatura referente às vendas N° - ' . $saleNumbers, now()->addDay(), $commissions);

                if (!$charge || empty($charge['id'])) {
                    throw new \Exception('Erro ao criar fatura!');
                }

            } catch (\Exception $e) {
                throw new \Exception('Erro ao criar cobrança: ' . $e->getMessage());
            }

            $this->createInvoice($uuid, $product, $charge, $user, $totalValue, 'Fatura referente às vendas N° - ' . $saleNumbers, $sponsorCommission, $commissions);
            $this->associateSalesWithCharge($sales, $charge['id']);

            DB::commit();

            return $this->jsonSuccess('Fatura criada com sucesso!', [
                'invoiceUrl' => $charge['invoiceUrl'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonError('Erro no processo: ' . $e->getMessage(), 500);
        }
    }

    private function validateUser($id) {

        $user = User::find($id);
        if (!$user) {
            throw new \Exception('Verifique seus dados e tente novamente.');
        }
        return $user;
    }

    private function validateSponsor($user) {

        if (!$user->parent) {
            return null;
        }

        $sponsor = $user->parent;

        if (!$sponsor || !$sponsor instanceof User) {
            throw new \Exception('Patrocinador inválido!');
        }

        return $sponsor;
    }

    private function validateProduct($productId) {

        $product = Product::find($productId);
        if (!$product) {
            throw new \Exception('Produto indisponível!');
        }
        return $product;
    }

    private function getSales($ids) {

        if (!is_array($ids) || empty($ids)) {
            throw new \Exception('Nenhuma venda infomada.');
        }

        $sales = Sale::whereIn('id', $ids)
            ->whereIn('status', [0, 2])
            ->with('product')
            ->get();

        if ($sales->isEmpty()) {
            throw new \Exception('Nenhuma venda encontrada.');
        }

        return $sales;
    }

    private function calculateTotalValue($sales, $user, $product) {

        $totalValue = 0;
        foreach ($sales as $sale) {
            $totalValue += $user->fixed_cost > 0
                ? $user->fixed_cost
                : ($sale->product->value_cost ?? $product->value_cost);
        }

        if ($totalValue <= 0) {
            throw new \Exception('Valor total inválido!');
        }

        return $totalValue;
    }

    private function createInvoice($uuid, $product, $charge, $user, $totalValue, $description, $commission) {
        $invoice = new Invoice();
        $invoice->name               = $description;
        $invoice->description        = $description;
        $invoice->user_id            = $user->id;
        $invoice->product_id         = $product->id;
        $invoice->value              = $totalValue;
        $invoice->commission_seller  = 0;
        $invoice->commission_filiate = $commission;
        $invoice->status             = 2;
        $invoice->type               = 2;
        $invoice->num                = 1;
        $invoice->due_date           = now()->addDay(2);
        $invoice->payment_url        = $charge['invoiceUrl'];
        $invoice->payment_token      = $charge['id'];
        $invoice->payment_splits     = json_encode($charge['splits'] ?? []);
        $invoice->save();
    }

    private function cancelPreviousInvoices($sales, $token) {

        foreach ($sales as $sale) {

            if (!empty($sale->payment_token)) {
                
                $invoice = Invoice::where('payment_token', $sale->payment_token)->first();
                if ($invoice) {

                    $canceled = $this->cancelCharge($invoice->payment_token, $token);
                    if ($invoice->delete()) {

                        $sale->payment_token = null;
                        $sale->save();
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    private function associateSalesWithCharge($sales, $chargeId) {
        Sale::whereIn('id', $sales->pluck('id')->toArray())
            ->update(['payment_token' => $chargeId]);
    }

    private function jsonSuccess($message, $data = []) {
        return response()->json(array_merge(['success' => true, 'message' => $message, 'data' => $data]));
    }

    private function jsonError($message, $code) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $code);
    }

    private function formatValue($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }
}
