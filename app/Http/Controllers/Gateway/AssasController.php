<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Coupon;
use App\Models\SaleList;
use App\Models\WebHook;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AssasController extends Controller {

    public function createCharge($customer, $billingType, $value, $dueDate = null, $description, $commissions = null, $token = null) {

        if (empty($token)) {
            $token = Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor->token_key;
        }

        try {
            $client = new Client();
    
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'access_token' => $token,
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

    public function updateCharge($id, $dueDate, $value = null, $token = null) {

        if (empty($token)) {
            $token = Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor->token_key;
        }
        $client = new Client();
        
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => $token,
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

    public function cancelCharge($id, $token = null) {

        if (empty($token)) {
            $token = Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor->token_key;
        }
        
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'access_token'  => $token,
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

    public function createCustomer($name, $cpfcnpj, $token = null) {

        if (empty($token)) {
            $token = Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor->token_key;
        }

        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accept'       => 'application/json',
                    'access_token' => $token,
                    'User-Agent'   => env('APP_NAME')
                ],
                'json' => [
                    'name'        => $name,
                    'cpfCnpj'     => $cpfcnpj,
                ],
                'verify' => false
            ];
    
            $response = $client->post(env('API_URL_ASSAS') . 'v3/customers', $options);
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
    
            if ($response->getStatusCode() === 200 && isset($data['id'])) {
                return $data['id'];
            } else {
                Log::error("Erro na criação do cliente Controller AssasController: " . json_encode($data));
                return false;
            }
    
        } catch (RequestException $e) {
            Log::error("Erro de requisição na API Assas CreateCustomer Controller AssasController: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Erro geral na função createCustomer Controller AssasController: " . $e->getMessage());
            return false;
        }
    }
    
    public function createMonthly() {

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('logout')->with('error', 'Você precisa fazer Login para acessar esta área!');
        }
       
        $invoice = Invoice::where('user_id', $user->id)->where('type', 1)->where('status', 0)->exists();
        if ($invoice) {
            return redirect()->route('payments')->with('info', 'Você possui pendências de pagamentos!');
        }
            
        $customer = $this->createCustomer($user->name, $user->cpfcnpj, env('APP_TOKEN_ASSAS'));
        if (!$customer) {
            return redirect()->route('profile')->with('info', 'Verifique seus dados e tente novamente!');
        }

        $uuid = Str::uuid();

        $value = Auth::user()->type == 2 ? env('MONTHLY_AFILIATE') : env('MONTHLY_WHITE_LABEL');
        $commissions = [
            [
                'walletId'          => env('WALLET_EXPRESS'),
                'percentualValue'   => env('WALLET_EXPRESS_PERCENTAGE'),
                'externalReference' => $uuid,
                'description'       => 'Assinatura - '.env('APP_NAME'),
            ],
        ];
        if (!empty(env('WALLET_SOCIO'))) {
            $commissions[] = [
                'walletId'          => env('WALLET_SOCIO'),
                'percentualValue'   => env('WALLET_SOCIO_PERCENTAGE'),
                'externalReference' => $uuid,
                'description'       => 'Assinatura - '.env('APP_NAME'),
            ];
        }

        $charge = $this->createCharge($customer, 'PIX', $value, now()->addDay(), 'Assinatura -'.env('APP_NAME'), $commissions, env('APP_TOKEN_ASSAS'));
        if($charge <> false) {

            $invoice = new Invoice();
            $invoice->uuid          = $uuid;
            $invoice->name          = 'Assinatura - '.env('APP_NAME');
            $invoice->description   = 'Assinatura - '.env('APP_NAME');

            $invoice->user_id       = $user->id;
            $invoice->product_id    = 1;
            $invoice->value         = $value;
            $invoice->status        = 0;
            $invoice->type          = 1;
            $invoice->num           = 1;
            
            $invoice->due_date          = now()->addDay();
            $invoice->payment_url       = $charge['invoiceUrl'];
            $invoice->payment_token     = $charge['id'];
            $invoice->payment_splits    = json_encode($charge['splits']);

            if($invoice->save()) {
                return redirect($charge['invoiceUrl']);
            }
        }

        return redirect()->route('payments')->with('info', 'Falha ao gerar Assinatura, verifique seus dados e tente novamente!');
    }

    public function balance($id = null) {

        $user = $id ? User::find($id) : Auth::user();

        $status = $this->accountStatus($user->token_key);
        if (is_array($status) && (isset($status['general']) && ($status['general'] == 'APPROVED' || $status['general'] == 'AWAITING_APPROVA'))) {
            try {

                $client = new Client();
                if (!$user) {
                    throw new \Exception('Usuário não encontrado.');
                }

                $response = $client->request('GET', env('API_URL_ASSAS') . 'v3/finance/balance', [
                    'headers' => [
                        'accept'       => 'application/json',
                        'access_token' => $user->token_key,
                        'User-Agent'   => env('APP_NAME'),
                    ],
                    'verify' => env('APP_ENV') == 'local' ? false : true,
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode((string) $response->getBody(), true);
                    return $data['balance'] ?? 0;
                }

                return false;
            } catch (\Throwable $e) {
                Log::error('Erro ao buscar saldo de '.$user->name.': ' . $e->getMessage());
                return false;
            }
        } else {
            return false;
        }
    }

    public function createdWebhook (Request $request) {

        if (Auth::check() && (Auth::user()->type == 99 || Auth::user()->type == 1)) {
            try {
                $client = new Client();
                $options = [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'accept'       => 'application/json',
                        'access_token' => Auth::user()->token_key,
                        'User-Agent'   => env('APP_NAME')
                    ],
                    'json' => [
                        'name'          => $request->name,
                        'url'           => $request->url,
                        'email'         => env('MAIL_USERNAME'),
                        'enabled'       => true,
                        'interrupted'   => false,
                        'apiVersion'    => 3,
                        'sendType'      => 'SEQUENTIALLY',
                        'events'        => [
                            'PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED', 'PAYMENT_OVERDUE', 'PAYMENT_DELETED', 'PAYMENT_SPLIT_CANCELLED'
                        ]
                    ],
                    'verify' => false
                ];
        
                $response = $client->post(env('API_URL_ASSAS') . 'v3/webhooks', $options);
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
        
                if ($response->getStatusCode() === 200) {
                    $webhook                = new WebHook();
                    $webhook->user_id       = Auth::user()->id;
                    $webhook->uuid          = $data['id'];
                    $webhook->name          = $data['name'];
                    $webhook->url           = $data['url'];
                    $webhook->email         = $data['email'];
                    $webhook->enabled       = $data['enabled'] == false ? 0 : 1;
                    $webhook->interrupted   = $data['interrupted'] == false ? 0 : 1;
                    $webhook->apiVersion    = $data['apiVersion'];
                    $webhook->sendType      = $data['sendType'];
                    if ($webhook->save()) {
                        return redirect()->back()->with('success', 'WebHook criado sucesso!');
                    }

                    return redirect()->back()->with('info', 'WebHook criado no Banco, mas não foi possível criar na plataforma!');
                } else {
                    Log::error("Erro na criação de WebHook no AssasController: " . json_encode($data));
                    return redirect()->back()->with('info', 'Falha ao tentar criar Novo WebHook: ' .json_encode($data));
                }
        
            } catch (RequestException $e) {
                Log::error("Erro na criação de WebHook no AssasController: " . $e->getMessage());
                return redirect()->back()->with('info', 'Erro na criação de WebHook: ' .$e->getMessage());
            } catch (\Exception $e) {
                Log::error("Erro na criação de WebHook no AssasController: " . $e->getMessage());
                return redirect()->back()->with('info', 'Erro na criação de WebHook: ' .$e->getMessage());
            }
        } else {
            return redirect()->route('logout')->with('info', 'Faça Login para ter acesso aos módulos!');
        }
    }

    public function updatedWebhook (Request $request) {
        if (Auth::check() && (Auth::user()->type == 99 || Auth::user()->type == 1)) {
            try {
                $client = new Client();
                $options = [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'accept'       => 'application/json',
                        'access_token' => Auth::user()->token_key,
                        'User-Agent'   => env('APP_NAME')
                    ],
                    'json' => [
                        'enabled'       => true,
                        'interrupted'   => false,
                    ],
                    'verify' => false
                ];
        
                $response = $client->post(env('API_URL_ASSAS') . 'v3/webhooks'.$request->uuid, $options);
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
        
                if ($response->getStatusCode() === 200) {
                    return redirect()->back()->with('success', 'WebHook atualizado sucesso!');
                } else {
                    Log::error("Erro na atualização de WebHook no AssasController: " . json_encode($data));
                    return redirect()->back()->with('info', 'Falha ao tentar atualizar WebHook: ' .json_encode($data));
                }
        
            } catch (RequestException $e) {
                Log::error("Erro na atualização de WebHook no AssasController: " . $e->getMessage());
                return redirect()->back()->with('info', 'Erro na atualização de WebHook: ' .$e->getMessage());
            } catch (\Exception $e) {
                Log::error("Erro na atualização de WebHook no AssasController: " . $e->getMessage());
                return redirect()->back()->with('info', 'Erro na atualização de WebHook: ' .$e->getMessage());
            }
        }
    }

    public function webhookStatus($id) {
        try {
            $client = new Client();
            
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'access_token' => Auth::user()->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false
            ];
    
            $response = $client->get(env('API_URL_ASSAS') . 'v3/webhooks/' . $id, $options);
            $body = (string) $response->getBody();
    
            if ($response->getStatusCode() === 200) {
                return json_decode($body, true);
            } else {
                Log::error('Erro ao buscar status do webhook');
                return "Sem informações";
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Erro ao buscar status do webhook: ' . $e->getMessage());
            return "Sem informações";
        }
    }

    public function wallet() {

        $extracts     = $this->extract();
        return view('app.Finance.Assas.Wallet.wallet', [
            'balance'   => $this->balance() ?? 0, 
            'extracts'  => $extracts ?? [], 
        ]);
    }

    public function IntegrateWallet() {

        $webhooks = WebHook::where('user_id', Auth::user()->id)->get();
        return view('app.Finance.Assas.Wallet.integrate-wallet', [
            'webhooks' => $webhooks
        ]);
    }

    public function IntegrateToken(Request $request) {

        if (empty($request->token_key) && empty($request->token_wallet)) {
            return redirect()->back()->with('info', 'Informe os dados necessários para integração!'); 
        }

        $user = User::where('id', $request->id)->first();
        if (!$user) {
            return redirect()->route('logout')->with('info', 'Faça Login para acessar os módulos do sistema!'); 
        }

        $status = $this->accountStatus($request->token_key);
        if (is_array($status) && (isset($status['general']) && ($status['general'] == 'APPROVED' || $status['general'] == 'AWAITING_APPROVA'))) {
            $user->token_wallet = $request->token_wallet;
            $user->token_key    = $request->token_key;
            $user->status       = 1;

            if ($user->save()) {
                return redirect()->back()->with('success', 'Tokens válidados com sucesso!');
            }
        } 

        return redirect()->back()->with('info', 'Tokens não válidados! Aguarde aprovação da sua carteira/ou entre em contato com o suporte do banco!');
    }

    public function extract() {
        try {

            $client     = new Client();
            $startDate  = now()->toDateString();
            $finishDate = now()->toDateString();
    
            $response = $client->request('GET', env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
                'headers' => [
                    'accept'       => 'application/json',
                    'access_token' => Auth::user()->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'verify' => false,
            ]);
    
            if ($response->getStatusCode() !== 200) {
                return [];
            }
    
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
    
        } catch (\Exception $e) {
            return [];
        }
    }

    public function withdrawSend(Request $request) {

        $password = $request->password;    
        if (Hash::check($password, Auth::user()->password)) {

            if(empty($request->key) || empty($request->value) || empty($request->type)) {
                return redirect()->back()->with('error', 'Dados incompletos!');
            }
    
            $client = new Client();
            try {
                $response = $client->request('POST', env('API_URL_ASSAS').'v3/transfers', [
                    'headers' => [
                        'accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                        'access_token' => Auth::user()->token_key,
                        'User-Agent'   => env('APP_NAME')
                    ],
                    'json' => [
                        'value'             => $this->formatValue($request->value),
                        'operationType'     => 'PIX',
                        'pixAddressKey'     => $request->key,
                        'pixAddressKeyType' => $request->type,
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

        return redirect()->back()->with('error', 'Senha inválida!');
    }

    private function accountStatus($token_key) {
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
            Log::error('Erro ao buscar status da conta: ' . $e->getMessage());
            return false;
        }
    }

    public function webhook(Request $request) {

        $data = $request->json()->all();
        $event = $data['event'] ?? null;

        if (!in_array($event, ['PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED'])) {
            return response()->json(['status' => 'ignored', 'message' => 'Event not handled.']);
        }

        $paymentToken = $data['payment']['id'] ?? null;
        if (!$paymentToken) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payment token.']);
        }

        $invoice = Invoice::where('payment_token', $paymentToken)->where('status', 0)->first();
        if ($invoice) {
            $invoice->status = 1;

            $sale = Sale::find($invoice->sale_id);
            if ($sale) {
                
                $product = $invoice->product_id ? Product::find($invoice->product_id) : null;
                if ($product && $invoice->num == 1) {
                    $sale->status = 1;
                    $sale->guarantee = Carbon::parse($sale->guarantee)->addMonths(3);

                    $saleList = SaleList::where('start', '<=', now())->where('end', '>=', now())->first();
                    if ($saleList) {
                        $sale->list_id = $saleList->id;
                    }
                }

                $sale->save();

                Notification::create([
                    'name'          => 'Invoice #' . $invoice->id,
                    'description'   => 'Fatura recebida com sucesso!',
                    'type'          => 1,
                    'user_id'       => $sale->seller_id
                ]);

                $seller = $sale->seller;

                if ($seller->type != 4) {

                    $salesCount = $seller->sales()->count();
                    
                    $levels = [
                        10      => ['level' => 2, 'name' => 'CONSULTANT'],
                        30      => ['level' => 3, 'name' => 'LEAD CONSULTANT'],
                        50      => ['level' => 4, 'name' => 'REGIONAL'],
                        100     => ['level' => 5, 'name' => 'REGIONAL MANAGER'],
                        300     => ['level' => 7, 'name' => 'DIRECTOR'],
                        500     => ['level' => 8, 'name' => 'REGIONAL DIRECTOR'],
                        1000    => ['level' => 9, 'name' => 'PRESIDENT VIP'],
                    ];

                    if (isset($levels[$salesCount])) {

                        $seller->type->level = $levels[$salesCount]['level'];
                        Notification::create([
                            'name'          => 'New Level!',
                            'description'   => $seller->type->name . ' reached level: ' . $levels[$salesCount]['name'],
                            'type'          => 2,
                            'user_id'       => $seller->id,
                        ]);
                    }

                    if ($invoice->type == 1) {
                        $seller->status = 1;
                    }

                    $seller->save();
                }
            }

            if (!$invoice->save()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to update invoice.']);
            }
        }

        $updatedRows = Sale::where('payment_token', $paymentToken)->whereIn('status', [0, 2])->update(['status' => 1]);
        if ($updatedRows > 0) {
            return response()->json(['status' => 'success', 'message' => 'Sales status updated successfully.']);
        }

        return response()->json(['status' => 'success', 'message' => 'Operation completed successfully.']);
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
                $unitSponsorProfit = $unitCost - $sponsor->fixed_cost;
                if ($unitSponsorProfit > 0) {
                    $sponsorCommission = $unitSponsorProfit * count($sales);
                    $commissions[] = [
                        'walletId'          => $sponsor->token_wallet,
                        'fixedValue'        => number_format($sponsorCommission - 2, 2, '.', ''),
                        'externalReference' => $uuid,
                        'description'       => 'Fatura referente às vendas N° - ' . $saleNumbers,
                    ];
                    $commissions[] = [
                        'walletId'          => env('APP_WALLET_ASSAS'),
                        'fixedValue'        => number_format($totalCost - $sponsorCommission, 2, '.', ''),
                        'externalReference' => $uuid,
                        'description'       => 'Fatura referente às vendas N° - ' . $saleNumbers,
                    ];
                } else {
                    $commissions[] = [
                        'walletId'          => env('APP_WALLET_ASSAS'),
                        'fixedValue'        => number_format($totalCost - 2, 2, '.', ''),
                        'externalReference' => $uuid,
                        'description'       => 'Fatura referente às vendas N° - ' . $saleNumbers,
                    ];
                }
            } else {
                $commissions[] = [
                    'walletId'          => env('APP_WALLET_ASSAS'),
                    'fixedValue'        => number_format($totalCost - 2, 2, '.', ''),
                    'externalReference' => $uuid,
                    'description'       => 'Fatura referente às vendas N° - ' . $saleNumbers,
                ];
            }

            $token = env('APP_TOKEN_ASSAS');
            $this->cancelPreviousInvoices($sales, $token);

            try {
                $customer = $this->createCustomer($user->name, $user->cpfcnpj, $token);

                $charge = $this->createCharge(
                    $customer,
                    'PIX',
                    $totalValue,
                    now()->addDay(),
                    'Fatura referente às vendas N° - ' . $saleNumbers,
                    $commissions,
                    $token
                );

                if (!$charge || empty($charge['id'])) {
                    throw new \Exception('Erro ao criar fatura!');
                }
            } catch (\Exception $e) {
                throw new \Exception('Erro ao criar cobrança: ' . $e->getMessage());
            }

            $this->createInvoice($uuid, $product, $charge, $user, $totalValue, 'Fatura referente às vendas N° - ' . $saleNumbers, $sponsorCommission);
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
        if (!$user->sponsor) {
            return null;
        }

        $sponsor = $user->sponsor;

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
            throw new \Exception('Nenhuma venda informada.');
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
        $invoice->uuid               = $uuid;
        $invoice->name               = $description;
        $invoice->description        = $description;
        $invoice->user_id            = $user->id;
        $invoice->product_id         = $product->id;
        $invoice->value              = $totalValue;
        $invoice->commission_seller  = 0;
        $invoice->commission_sponsor = $commission;
        $invoice->status             = 2;
        $invoice->type               = 2;
        $invoice->num                = 1;
        $invoice->due_date           = now()->addDay(2);
        $invoice->payment_url        = $charge['invoiceUrl'];
        $invoice->payment_token      = $charge['id'];
        $invoice->save();
    }

    private function cancelPreviousInvoices($sales, $token) {

        foreach ($sales as $sale) {
            if (!empty($sale->payment_token)) {
                
                $invoice = Invoice::where('payment_token', $sale->payment_token)->first();
                if ($invoice) {
                    $canceled = $this->cancelCharge($invoice->payment_token, $token);
                    Log::info('Assas: ', ['canceled' => $canceled]);
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