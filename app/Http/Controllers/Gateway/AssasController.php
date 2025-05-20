<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Coupon;
use App\Models\SaleList;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssasController extends Controller {

    public function createCharge($customer, $billingType, $value, $dueDate = null, $description, $commissions = null) {

        try {
            $client = new Client();
    
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'access_token' => Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor()->token_key,
                    'User-Agent'   => env('APP_NAME')
                ],
                'json' => [
                    'customer'          => $customer,
                    'billingType'       => $billingType,
                    'value'             => number_format($value, 2, '.', ''),
                    'dueDate'           => isset($dueDate) ? Carbon::parse($dueDate)->toIso8601String() : now()->addDays(1),
                    'description'       => $description,
                    'isAddressRequired' => false,
                    'split'             => env('APP_ENV') !== 'local' ? $commissions : null,
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

    public function createCustomer($name, $cpfcnpj, $mobilePhone, $email) {

        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accept'       => 'application/json',
                    'access_token' => Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor()->token_key,
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
            
        $customer = $this->createCustomer($user->name, $user->cpfcnpj, $user->phone, $user->email);
        if (!$customer) {
            return redirect()->route('profile')->with('info', 'Verifique seus dados e tente novamente!');
        }

        $value = Auth::user()->type == 2 ? env('MONTHLY_AFILIATE') : env('MONTHLY_WHITE_LABEL');
        $commissions = [
            [
                'walletId'      => env('WALLET_EXPRESS'),
                'fixedValue'    => Auth::user()->type == 2 ? ($value - ($value * 0.10)) : $value,
            ],
        ];

        $charge = $this->createCharge($customer, 'PIX', $value, now()->addDay(), 'Assinatura -'.env('APP_NAME'), $commissions);
        if($charge <> false) {

            $invoice = new Invoice();
            $invoice->uuid          = Str::uuid();;
            $invoice->name          = 'Assinatura - '.env('APP_NAME');
            $invoice->description   = 'Assinatura - '.env('APP_NAME');

            $invoice->user_id       = $user->id;
            $invoice->product_id    = 1;
            $invoice->value         = $value;
            $invoice->status        = 0;
            $invoice->type          = 1;
            $invoice->num           = 1;
            
            $invoice->due_date      = now()->addDay();
            $invoice->payment_url   = $charge['invoiceUrl'];
            $invoice->payment_token = $charge['id'];

            if($invoice->save()) {
                return redirect($charge['invoiceUrl']);
            }
        }

        return redirect()->route('payments')->with('info', 'Falha ao gerar Assinatura, verifique seus dados e tente novamente!');
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
        if (!$invoice) {
            return response()->json(['status' => 'success', 'message' => 'No pending invoice found.']);
        }

        if ($data['payment']['billingType'] == 'RECEIVED_IN_CASH' && ($invoice->num == 1 || $invoice->type == 1)) {
            return response()->json(['status' => 'success', 'message' => 'RECEIVED_IN_CASH no accept.']);
        }

        $invoice->status = 1;
        if (!$invoice->save()) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update invoice.']);
        }

        $sale = Sale::find($invoice->sale_id);
        if ($sale) {
            
            $product = $invoice->id_product ? Product::find($invoice->id_product) : null;
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

        $pendingSales = Sale::where('payment_token', $paymentToken)->where('status', 0)->get();
        if ($pendingSales->isNotEmpty()) {
            Sale::whereIn('id', $pendingSales->pluck('id'))->update(['status' => 1]);
            return response()->json(['status' => 'success', 'message' => 'Sales status updated successfully.']);
        }

        return response()->json(['status' => 'success', 'message' => 'Operation completed successfully.']);
    }

    public function updateCharge($id, $dueDate, $value = null) {
        
        $client = new Client();
        
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor()->token_key,
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

    public function balance($id = null) {
        try {
            $client = new Client();

            $user = $id ? User::find($id) : Auth::user();

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
    }

    public function cancelInvoice($token) {
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'access_token'  => Auth::user()->type == 99 ? Auth::user()->token_key : Auth::user()->sponsor()->token_key,
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
            Log::error('Erro ao buscar status da conta: ' . $e->getMessage());
            return false;
        }
    }

    public function IntegrateWallet() {
        return view('app.Finance.Wallet.Integrate-wallet');
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
        } 

        return redirect()->back()->with('info', 'Tokens não válidados! Aguarde aprovação da sua carteira/ou entre em contato com o suporte do banco!');
    }
}