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
use Illuminate\Support\Facades\Auth;

class AssasController extends Controller {

    public function createCharge($customer, $billingType, $value, $dueDate = null, $description, $commissions = null) {

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

    // public function requestInvoice($sale) {
        
    //     $createSalePayment = $this->createSalePayment($sale);
    //     if ($createSalePayment) {
    //         return redirect()->back()->with('success', 'Fatura criada para a venda!'); 
    //     }

    //     return redirect()->back()->with('info', 'Verifique os dados e tente novamente!');
    // }
    
    // public function createMonthly($id) {

    //     $user = User::find($id);
    //     if (!$user) {
    //         return redirect()->route('profile')->with('info', 'Verifique seus dados e tente novamente!');
    //     }
       
    //     $invoice = Invoice::where('user_id', $user->id)->where('type', 1)->where('status', 0)->exists();
    //     if ($invoice) {
    //         return redirect()->route('payments')->with('error', 'Você possui uma mensalidade em aberto!');
    //     }
        
    //     if ($user->customer == null) {
            
    //         $customer = $this->createCustomer($user->name, $user->cpfcnpj, $user->phone, $user->email);
    //         if ($customer) {
    //             $user->customer = $customer;
    //             $user->save();
    //         } else {
    //             return redirect()->route('profile')->with('info', 'Verifique seus dados e tente novamente!');
    //         }
    //     }

    //     $charge = $this->createCharge($user->customer, 'PIX', '49.99', 'Assinatura -'.env('APP_NAME'), now()->addDay(), null, env('WALLET_HEFESTO'), 20);
    //     if($charge <> false) {

    //         $invoice = new Invoice();
    //         $invoice->name          = 'Mensalidade '.env('APP_NAME');
    //         $invoice->description   = 'Mensalidade '.env('APP_NAME');

    //         $invoice->user_id       = $user->id;
    //         $invoice->product_id    = 1;
    //         $invoice->value         = 49.99;
    //         $invoice->commission    = 20;
    //         $invoice->status        = 0;
    //         $invoice->type          = 1;
    //         $invoice->num           = 1;
    //         $invoice->due_date      = now()->addDay();

    //         $invoice->payment_url   = $charge['invoiceUrl'];
    //         $invoice->payment_token = $charge['id'];

    //         if($invoice->save()) {
    //             return redirect($charge['invoiceUrl']);
    //         }
    //     }

    //     return redirect()->back()->with('error', 'Tivemos um pequeno problema, contate o suporte!');
    // }    
    
    // public function createCoupon($parent, $description) {

    //     $couponName = $this->generateCouponName($parent->name);

    //     $coupon                 = new Coupon();
    //     $coupon->name           = $couponName;
    //     $coupon->description    = $description;
    //     $coupon->user_id        = $parent->id;
    //     $coupon->percentage     = 100;
    //     $coupon->qtd            = 1;
    //     if($coupon->save()) {
    //         $message =  "*Surpresa Especial para Você! 🎁* \r\n\r\n"
    //                     . "Como forma de agradecimento por ser um parceiro incrível, preparamos um *cupom de {$coupon->percentage}% de desconto* para você aproveitar na sua próxima mensalidade! \r\n\r\n"
    //                     . "Código do cupom: *{$couponName}* \r\n"
    //                     . "Não deixe essa oportunidade passar! Use o código na sua fatura e aproveite para economizar. \r\n"
    //                     . "Agradecemos por fazer parte da nossa jornada! \r\n\r\n";

    //         $assas = new AssasController();
    //         $assas->sendWhatsapp('', $message, $parent->phone, $parent->api_token_zapapi);
    //     }

    //     return true;
    // }

    // private function generateCouponName(string $name): string {

    //     $baseName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
    //     $existingCouponsCount = Coupon::where('name', 'like', "{$baseName}%")->count();
    //     return $existingCouponsCount > 0 ? "{$baseName}".($existingCouponsCount + 1) : $baseName;
    // }

    // public function webhook(Request $request) {

    //     $jsonData = $request->json()->all();
    //     if ($jsonData['event'] === 'PAYMENT_CONFIRMED' || $jsonData['event'] === 'PAYMENT_RECEIVED') {
            
    //         $token = $jsonData['payment']['id'];
    //         $invoice = Invoice::where('token_payment', $token)->where('status', 0)->first();
    //         if ($invoice) {

    //             $invoice->status = 1;
    //             if (!$invoice->save()) {
    //                 return response()->json(['status' => 'error', 'message' => 'Não foi possível confirmar o pagamento da fatura!']);
    //             }

    //             $sale = Sale::where('id', $invoice->id_sale)->first();
    //             if ($sale) {

    //                 $product = $invoice->id_product <> null ? Product::where('id', $invoice->id_product)->first() : false;
    //                 if ($product && $invoice->num == 1) {
                            
    //                     $sale->status = 1;
    //                     $sale->guarantee = Carbon::parse($sale->guarantee)->addMonths(12);

    //                     $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
    //                     if ($list) {
    //                         $sale->id_list = $list->id;
    //                     }
    //                 }

    //                 $sale->save();

    //                 $notification               = new Notification();
    //                 $notification->name         = 'Fatura N°'.$invoice->id;
    //                 $notification->description  = 'Faturas recebida com sucesso!';
    //                 $notification->type         = 1;
    //                 $notification->user_id      = $invoice->id_seller; 
    //                 $notification->save();

    //                 $seller = User::find($sale->id_seller);
    //                 if ($seller && $seller->type <> 4) {

    //                     $totalSales = Sale::where('id_seller', $seller->id)->where('status', 1)->count();
    //                     switch($totalSales) {
    //                         case 10:
    //                             $seller->level = 2;
    //                             $nivel = 'CONSULTOR'; 
    //                             break;
    //                         case 30:
    //                             $seller->level = 3;
    //                             $nivel = 'CONSULTOR LÍDER'; 
    //                             break;
    //                         case 50:
    //                             $seller->level = 4;
    //                             $nivel = 'REGIONAL'; 
    //                             break;
    //                         case 100:
    //                             $seller->level = 5;
    //                             $nivel = 'GERENTE REGIONAL';
    //                             break;
    //                         case 300:
    //                             $seller->level = 7;
    //                             $nivel = 'DIRETOR';
    //                             break;
    //                         case 500:
    //                             $seller->level = 8;
    //                             $nivel = 'DIRETOR REGIONAL';
    //                             break;
    //                         case 1000:
    //                             $seller->level = 9;
    //                             $nivel = 'PRESIDENTE VIP';
    //                             break;
    //                     }

    //                     if (!empty($nivel)) {
    //                         $notification               = new Notification();
    //                         $notification->name         = 'Novo nível!';
    //                         $notification->description  = $seller->name.' Alcançou o nível: '.$nivel;
    //                         $notification->type         = 2;
    //                         $notification->user_id      = 14; 
    //                         $notification->save();
    //                     }

    //                     // if ($seller->salesSeller()->where('status', 1)->count() % 10 === 0) {
    //                     //     $this->createCoupon($seller, 'Promoção 10 vendas ganha 1 nome!');
    //                     // }

    //                     $seller->save();
    //                 }
    //             }

    //             $sales = Sale::where('token_payment', $token)->where('status', 0)->get();
    //             if ($sales->isNotEmpty()) {

    //                 Sale::whereIn('id', $sales->pluck('id'))
    //                     ->update(['status' => 1]);
                
    //                 return response()->json(['success' => 'success', 'message' => 'Status das vendas atualizado com sucesso!']);
    //             }

    //             $client = User::find($invoice->user_id);
    //             if ($client && $invoice->num == 1) {
    //                 $this->sendWhatsapp(env('APP_URL').'login-cliente', "Olá, ".$client->name."!\r\n\r\nAgradecemos pelo seu pagamento! \r\n\r\n\r\n Tenha a certeza de que sua situação está em boas mãos. \r\n\r\n\r\n *Nos próximos 30 dias úteis*, nossa equipe especializada acompanhará de perto todo o processo para garantir que seu nome seja limpo o mais rápido possível. \r\n\r\n\r\n Estamos à disposição para qualquer dúvida ou esclarecimento. \r\n\r\n Você pode acompanhar o processo acessando nosso sistema no link abaixo: \r\n\r\n", $client->phone, $seller->api_token_zapapi);
    //             } else {
    //                 $this->sendWhatsapp(env('APP_URL').'login-cliente', $client->name."!\r\n\r\nAgradecemos por manter o compromisso e realizar o pagamento do boleto, o que garante a continuidade e a validade da garantia do serviço. \r\n\r\n Acesse o Painel do cliente👇", $client->phone, $seller->api_token_zapapi);
    //             }

    //             if ($seller && $invoice->num == 1 && $invoice->type == 3) {
    //                 $message =  "Olá, {$seller->name}, Espero que esteja bem! 😊\r\n\r\n"
    //                             . "Gostaria de informar que uma nova venda foi realizada com sucesso.🤑💸\r\n\r\n"
    //                             . "Cliente: {$client->name}\r\n"
    //                             . "Produto/Serviço: {$product->name}\r\n"
    //                             . "Valor Total: R$ " . number_format($sale->value, 2, ',', '.') . "\r\n"
    //                             . "Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
    //                             . "Obrigado pelo excelente trabalho!🥇\r\n\r\n";

    //                 $this->sendWhatsapp("", $message, $seller->phone, $seller->api_token_zapapi);
    //             }

    //             if ($seller && $invoice->num != 1 && $invoice->type == 3 && $invoice->commission > 0) {
    //                 $message =  "Olá, {$seller->name}, Espero que esteja bem! 😊\r\n\r\n"
    //                             . "Gostaria de informar que uma nova COMISSÃO FOI RECEBIDA com sucesso.🤑💸\r\n\r\n"
    //                             . "Cliente: {$client->name}\r\n"
    //                             . "Produto/Serviço: {$product->name}\r\n"
    //                             . "Fatura N° {$invoice->num}\r\n"
    //                             . "Valor apróximado: R$ " . number_format($invoice->commission, 2, ',', '.') . "\r\n"
    //                             . "Data da Venda: " . $sale->created_at->format('d/m/Y H:i') . "\r\n\r\n"
    //                             . "Obrigado pelo excelente trabalho!🥇\r\n\r\n";

    //                 $this->sendWhatsapp("", $message, $seller->phone, $seller->api_token_zapapi);
    //             }
                
    //             return response()->json(['status' => 'success', 'message' => 'Operação Finalizada!']);
    //         }
            
    //         return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura/Venda encontrada!']);
    //     }

    //     if($jsonData['event'] === 'PAYMENT_OVERDUE') {

    //         $token = $jsonData['payment']['id'];
    //         $invoice = Invoice::where('token_payment', $token)->where('status', 0)->first();
    //         if($invoice) {

    //             if(($invoice->type == 2 || $invoice->type == 3) && $invoice->num > 1) {
    //                 switch ($invoice->notification_number) {
    //                     case 1:
    //                         $value      = $invoice->value - ($invoice->value * 0.10);
    //                         $commission = $invoice->commission - ($invoice->commission * 0.15);
    //                         $dueDate    = Carbon::now()->addDays(7);
    //                         $wallet     = $invoice->sale->seller->wallet ?? null;

    //                         $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
    //                         if($charge) {

    //                             $invoice->due_date               = $dueDate;
    //                             $invoice->value                  = $value;
    //                             $invoice->commission             = $commission;
    //                             $invoice->url_payment            = $charge['invoiceUrl'];
    //                             $invoice->token_payment          = $charge['id'];
    //                             $invoice->notification_number    += 1;
    //                             $invoice->save(); 

    //                             $dueDateFormatted = Carbon::parse($dueDate)->format('d/m/Y');
    //                             $message =  "Olá, {$invoice->user->name}!\r\n\r\n"
    //                                 . "Sua fatura {$invoice->num} está atrasada. Oferecemos um desconto de 10% se o pagamento for feito até {$dueDateFormatted}.\r\n"
    //                                 . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n";

    //                             $this->sendWhatsapp(
    //                                 $charge['invoiceUrl'],
    //                                 $message,
    //                                 $invoice->user->phone,
    //                                 $invoice->sale->seller->api_token_zapapi
    //                             );
    //                         }
    //                         break;
    //                     case 2:
    //                         $value      = $invoice->value - ($invoice->value * 0.10);
    //                         $commission = $invoice->commission - ($invoice->commission * 0.15);
    //                         $dueDate    = Carbon::now()->addDays(7);
    //                         $wallet     = $invoice->sale->seller->wallet ?? null;

    //                         $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
    //                         if($charge) {

    //                             $invoice->due_date               = $dueDate;
    //                             $invoice->value                  = $value;
    //                             $invoice->commission             = $commission;
    //                             $invoice->url_payment            = $charge['invoiceUrl'];
    //                             $invoice->token_payment          = $charge['id'];
    //                             $invoice->notification_number    += 1;
    //                             $invoice->save(); 

    //                             $dueDateFormatted = \Carbon\Carbon::parse($dueDate)->format('d/m/Y');
    //                             $message =  "Olá, {$invoice->user->name}!\r\n\r\n"
    //                                 . "Sua fatura {$invoice->num} está atrasada. Oferecemos um desconto de 20% se o pagamento for feito até {$dueDateFormatted}.\r\n"
    //                                 . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n";

    //                             $this->sendWhatsapp(
    //                                 $charge['invoiceUrl'],
    //                                 $message,
    //                                 $invoice->user->phone,
    //                                 $invoice->sale->seller->api_token_zapapi
    //                             );
    //                         }
    //                         break;     
    //                     case 3:
    //                         $value      = $invoice->value - ($invoice->value * 0.10);
    //                         $commission = $invoice->commission - ($invoice->commission * 0.15);
    //                         $dueDate    = Carbon::now()->addDays(7);
    //                         $wallet     = $invoice->sale->seller->wallet ?? null;

    //                         $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
    //                         if($charge) {

    //                             $invoice->due_date               = $dueDate;
    //                             $invoice->value                  = $value;
    //                             $invoice->commission             = $commission;
    //                             $invoice->url_payment            = $charge['invoiceUrl'];
    //                             $invoice->token_payment          = $charge['id'];
    //                             $invoice->notification_number    += 1;
    //                             $invoice->save(); 

    //                             $dueDateFormatted = \Carbon\Carbon::parse($dueDate)->format('d/m/Y');
    //                             $message =  "Olá, {$invoice->user->name}!\r\n\r\n"
    //                                 . "Sua fatura {$invoice->num} está atrasada. Oferecemos um desconto de 30% se o pagamento for feito até {$dueDateFormatted}.\r\n"
    //                                 . "*Após essa data, a multa será aplicada e a garantia será perdida.*\r\n\r\n";

    //                             $this->sendWhatsapp(
    //                                 $charge['invoiceUrl'],
    //                                 $message,
    //                                 $invoice->user->phone,
    //                                 $invoice->sale->seller->api_token_zapapi
    //                             );
    //                         }
    //                         break;
    //                     case 4:
    //                         $value      = $invoice->value - ($invoice->value * 0.20);
    //                         $commission = $invoice->commission - ($invoice->commission * 0.20);
    //                         $dueDate    = Carbon::now()->addDays(7);
    //                         $wallet     = $invoice->sale->seller->wallet ?? null;

    //                         $charge = $this->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
    //                         if($charge) {

    //                             $invoice->due_date               = $dueDate;
    //                             $invoice->value                  = $value;
    //                             $invoice->commission             = $commission;
    //                             $invoice->url_payment            = $charge['invoiceUrl'];
    //                             $invoice->token_payment          = $charge['id'];
    //                             $invoice->notification_number    += 1;
    //                             $invoice->save(); 

    //                             $dueDateFormatted = \Carbon\Carbon::parse($dueDate)->format('d/m/Y');
    //                             $message =  "Assunto: Urgente: Fatura Atrasada \r\n\r\n Olá, {$invoice->user->name}!\r\n\r\n"
    //                                 . "Sua fatura {$invoice->num} está gravemente atrasada. Oferecemos um desconto de 50% se o pagamento for feito até {$dueDateFormatted}.\r\n"
    //                                 . "*Após essa data, a multa será aplicada e a garantia do produto será cancelada, o que pode resultar em custos extras e prejuízos adicionais.*\r\n\r\n"
    //                                 . "Além disso, seu nome voltará a ficar sujo e toda a boa reputação que trabalhamos para recuperar para você será perdida. Não deixe essa oportunidade passar e evite impactos negativos em sua situação financeira e reputacional. \r\n\r\n\r\n";

    //                             $this->sendWhatsapp(
    //                                 $charge['invoiceUrl'],
    //                                 $message,
    //                                 $invoice->user->phone,
    //                                 $invoice->sale->seller->api_token_zapapi
    //                             );
    //                         }
    //                         break;
    //                     default:
    //                         return response()->json(['status' => 'success', 'message' => 'Notificação de vencimento gerada!']);
    //                         break;
    //                 }
    //             }

    //             return response()->json(['status' => 'success', 'message' => 'Não é cobrança de Produto!']);
    //         }

    //         return response()->json(['status' => 'success', 'message' => 'Nenhum Fatura encontrada!']);
    //     }

    //     return response()->json(['status' => 'success', 'message' => 'Webhook não utilizado!']);
    // }

    public function updateCharge($id, $dueDate, $value = null) {
        
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

    // public function receivable($startDate = null, $finishDate = null, $offset = 0) {
    //     try {

    //         $client     = new Client();
    //         $user       = Auth::user();
    //         $startDate  = $startDate  ?? now()->toDateString();
    //         $finishDate = $finishDate ?? now()->toDateString();
    
    //         $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/financialTransactions?limit=100&startDate={$startDate}&finishDate={$finishDate}&offset={$offset}&order=asc", [
    //             'headers' => [
    //                 'accept'        => 'application/json',
    //                 'access_token'  => $user->api_key,
    //                 'User-Agent'    => env('APP_NAME')
    //             ],
    //             'verify' => env('APP_ENV') == 'local' ? false : true,
    //         ]);

    //         $body = (string) $response->getBody();
    //         if ($response->getStatusCode() === 200) {
    //             $data = json_decode($body, true);
    //             return [
    //                 'data'    => $data['data'],
    //                 'hasMore' => $data['hasMore'],
    //                 'offset'  => $offset
    //             ];
    //         } else {
    //             return [
    //                 'data'    => [],
    //                 'hasMore' => false,
    //                 'offset'  => $offset
    //             ];
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Erro ao consultar Extrato '.$user->name.': ' . $e->getMessage());
    //         return [
    //             'data'    => [],
    //             'hasMore' => false,
    //             'offset'  => $offset
    //         ];
    //     }
    // }

    // public function balance($id = null) {
    //     try {
    //         $client = new Client();

    //         $user = $id ? User::find($id) : Auth::user();

    //         if (!$user) {
    //             throw new \Exception('Usuário não encontrado.');
    //         }

    //         $response = $client->request('GET', env('API_URL_ASSAS') . 'v3/finance/balance', [
    //             'headers' => [
    //                 'accept'       => 'application/json',
    //                 'access_token' => $user->api_key,
    //                 'User-Agent'   => env('APP_NAME'),
    //             ],
    //             'verify' => env('APP_ENV') == 'local' ? false : true,
    //         ]);

    //         if ($response->getStatusCode() === 200) {
    //             $data = json_decode((string) $response->getBody(), true);
    //             return $data['balance'] ?? 0;
    //         }

    //         return false;
    //     } catch (\Throwable $e) {
    //         // Log::error('Erro ao buscar saldo de '.$user->name.': ' . $e->getMessage());
    //         return false;
    //     }
    // }

    // public function statistics() {
    //     try {
    //         $client = new Client();
    //         $user = Auth::user();

    //         if (!$user) {
    //             throw new \Exception('Usuário não autenticado.');
    //         }

    //         $response = $client->request('GET', env('API_URL_ASSAS') . 'v3/finance/split/statistics', [
    //             'headers' => [
    //                 'accept'       => 'application/json',
    //                 'access_token' => $user->api_key,
    //                 'User-Agent'   => env('APP_NAME')
    //             ],
    //             'verify' => env('APP_ENV') == 'local' ? false : true,
    //         ]);

    //         if ($response->getStatusCode() === 200) {
    //             $data = json_decode((string) $response->getBody(), true);
    //             return $data['income'] ?? 0;
    //         }

    //         return false;
    //     } catch (\Throwable $e) {
    //         // Log::error('Erro ao buscar estatísticas financeiras de '.$user->name.': ' . $e->getMessage());
    //         return false;
    //     }
    // }

    // public function accumulated() {
    //     try {
    //         $client = new Client();
    //         $user = Auth::user();
    //         $startDate = $user->created_at->toDateString();
    //         $finishDate = now()->toDateString();
    
    //         $response = $client->request('GET',  env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
    //             'headers' => [
    //                 'accept'        => 'application/json',
    //                 'access_token'  => $user->api_key,
    //                 'User-Agent'    => env('APP_NAME')
    //             ],
    //             'verify' => env('APP_ENV') == 'local' ? false : true,
    //         ]);
    
    //         if ($response->getStatusCode() === 200) {
    //             $body = (string) $response->getBody();
    //             $data = json_decode($body, true);
    //             $filteredData = array_filter($data['data'], function ($item) {
    //                 return $item['type'] === 'TRANSFER';
    //             });
    
    //             $totalValue = array_sum(array_column($filteredData, 'value'));
    
    //             return abs($totalValue);
    //         } else {
    //             return 0;
    //         }
    //     } catch (\Exception $e) {
    //         return 0;
    //     }
    // }

    // public function webhookSing(Request $request) {

    //     $jsonData = $request->getContent();
    //     $data = json_decode($jsonData, true);
    //     if (isset($data['token']) && isset($data['event_type'])) {
            
    //         if($data['event_type'] === 'doc_signed') {
    //             $token = $data['token'];

    //             $sale = Sale::where('token_contract', $token)->first();
    //             if ($sale) {

    //                 $sale->status_contract = 1;
    //                 if($sale->save()) {
    //                     return response()->json(['message' => 'Contrato assinado!'], 200);
    //                 }

    //                 return response()->json(['message' => 'Não foi possível atualizar a Venda!'], 200);
    //             }
    //         }

    //         return response()->json(['message' => 'Nenhuma operação finalizada!'], 200);
    //     } else {
            
    //         return response()->json(['error' => 'Token e Event não localizados!'], 200);
    //     }

    //     return response()->json(['error' => 'Webhook não utilizado!'], 200);
    // }

    // public function withdrawSend($key, $value, $type) {

    //     $client = new Client();
        
    //     $user = Auth::user();
    //     try {
    //         $response = $client->request('POST', env('API_URL_ASSAS').'v3/transfers', [
    //             'headers' => [
    //                 'accept'       => 'application/json',
    //                 'Content-Type' => 'application/json',
    //                 'access_token' => $user->api_key,
    //                 'User-Agent'   => env('APP_NAME')
    //             ],
    //             'json' => [
    //                 'value' => $value,
    //                 'operationType' => 'PIX',
    //                 'pixAddressKey' => $key,
    //                 'pixAddressKeyType' => $type,
    //                 'description' => 'Saque '.env('APP_NAME'),
    //             ],
    //             'verify'  => false,
    //         ]);
    
    //         $body = $response->getBody()->getContents();
    //         $decodedBody = json_decode($body, true);
    
    //         if ($decodedBody['status'] === 'PENDING') {
    //             return ['success' => true, 'message' => 'Saque agendado com sucesso'];
    //         } else {
    //             return ['success' => false, 'message' => 'Situação do Saque: ' . $decodedBody['status']];
    //         }
    //     } catch (\GuzzleHttp\Exception\RequestException $e) {
    //         $response = $e->getResponse();
    //         $body = $response->getBody()->getContents();
    //         $decodedBody = json_decode($body, true);
    
    //         return ['success' => false, 'message' => $decodedBody['errors'][0]['description']];
    //     }
    // }

    // public function extract() {
    //     try {

    //         $client = new Client();
    //         $user = Auth::user();
    //         $startDate = $user->created_at->toDateString();
    //         $finishDate = now()->toDateString();
    
    //         $response = $client->request('GET', env('API_URL_ASSAS') . "v3/financialTransactions?startDate={$startDate}&finishDate={$finishDate}&order=desc", [
    //             'headers' => [
    //                 'accept'       => 'application/json',
    //                 'access_token' => $user->api_key,
    //                 'User-Agent'   => env('APP_NAME')
    //             ],
    //             'verify' => env('APP_ENV') == 'local' ? false : true,
    //         ]);
    
    //         if ($response->getStatusCode() !== 200) {
    //             return [];
    //         }
    
    //         return json_decode((string) $response->getBody(), true)['data'] ?? [];
    
    //     } catch (\Exception $e) {
    //         // Log::error('Erro ao buscar extrato de '.$user->name.': ' . $e->getMessage());
    //         return [];
    //     }
    // }

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

    // public function payMonthly($id) {
    //     try {
    //         $invoice = Invoice::find($id);
    //         if (!$invoice || $invoice->status == 1) {
    //             return redirect()->back()->with('error', 'Não é possível pagar essa Fatura com saldo!');
    //         }
    
    //         $user = User::find($invoice->user_id);
    //         if (!$user) {
    //             return redirect()->back()->with('info', 'Dados não localizados!');
    //         }
    
    //         $balance = $this->balance();
    //         if ($balance !== false && $balance < $invoice->value) {
    //             return redirect()->back()->with('info', 'Não há saldo disponível!');
    //         }
    
    //         $client = new Client();
    //         $response = $client->request('GET', env('API_URL_ASSAS') . "v3/payments/{$invoice->token_payment}/pixQrCode", [
    //             'headers' => [
    //                 'accept'       => 'application/json',
    //                 'access_token' => $user->api_key,
    //                 'User-Agent'   => env('APP_NAME')
    //             ],
    //             'verify' => env('APP_ENV') == 'local' ? false : true,
    //         ]);
    
    //         if ($response->getStatusCode() !== 200) {
    //             return redirect()->back()->with('error', 'Não foi possível pagar com o saldo!');
    //         }
    
    //         $data = json_decode((string) $response->getBody(), true);
    //         $payqrcode = $this->payQrCode($data['payload'], $invoice->value, $invoice->description);
    
    //         $statusMessages = [
    //             'AWAITING_BALANCE_VALIDATION' => 'Saldo em análise! Aguarde alguns segundos.',
    //             'AWAITING_INSTANT_PAYMENT_ACCOUNT_BALANCE' => 'Transação em análise! Aguarde alguns segundos.',
    //             'AWAITING_CRITICAL_ACTION_AUTHORIZATION' => 'Transação aguardando autorização!',
    //             'AWAITING_CHECKOUT_RISK_ANALYSIS_REQUEST' => 'Transação aguardando análise!',
    //             'AWAITING_CASH_IN_RISK_ANALYSIS_REQUEST' => 'Transação aguardando análise!',
    //             'SCHEDULED' => 'Transação agendada com sucesso!',
    //             'AWAITING_REQUEST' => 'Transação aguardando análise!',
    //             'REQUESTED' => 'Transação solicitada!',
    //             'DONE' => 'Transação realizada com sucesso!',
    //             'REFUSED' => 'Transação recusada!',
    //             'CANCELLED' => 'Transação cancelada!',
    //         ];
    
    //         return redirect()->back()->with($payqrcode === 'DONE' ? 'success' : 'info', $statusMessages[$payqrcode] ?? 'Transação em análise!');
    
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Ocorreu um erro inesperado! ' . $e->getMessage());
    //     }
    // }    

    // private function payQrCode($payload, $value, $description, $date = null) {
    //     try {
    //         $client = new Client();

    //         $options = [
    //             'headers' => [
    //                 'Content-Type'  => 'application/json',
    //                 'access_token'  => env('API_TOKEN_ASSAS'),
    //                 'User-Agent'    => env('APP_NAME')
    //             ],
    //             'json' => [
    //                 'qrCode' => [
    //                     'payload' => $payload
    //                 ],
    //                 'value'        => number_format($value, 2, '.', ''),
    //                 'description'  => $description,
    //                 'scheduleDate' => $date ?? now(),
    //             ],
    //             'verify' => false
    //         ];

    //         $response = $client->post(env('API_URL_ASSAS') . 'v3/pix/qrCodes/pay', $options);
    //         $body = (string) $response->getBody();

    //         if ($response->getStatusCode() === 200) {
    //             $data = json_decode($body, true);
    //             return $data;
    //         }

    //         return false;
    //     } catch (\Throwable $e) {
    //         Log::error('Erro ao criar QR Code de pagamento: ' . $e->getMessage());
    //         return false;
    //     }
    // }

    // public function createPayment(Request $request) {

    //     DB::beginTransaction();

    //     try {
            
    //         $user       = $this->validateUser($request->customer);
    //         $filiate    = $this->validateFiliate($user);

    //         $sales      = $this->getSales($request['ids']);
    //         $saleIds    = $sales->pluck('id')->toArray();
    //         $totalValue = $this->calculateTotalValue($sales, $user); 
    //         $commission = ($user->fixed_cost - $filiate->fixed_cost) * $sales->count();

    //         $charge = $this->createCharge($user->customer, 'PIX', $totalValue, 'Fatura referente às vendas N° - '.implode(', ', $saleIds), now()->addDay(), 1, null, null, $filiate, $commission);
    //         if (!$charge || empty($charge['id'])) {
    //             return $this->jsonError('Erro ao criar fatura!', 500);
    //         }

    //         $this->createInvoice($charge, $user, $totalValue, 'Fatura referente às vendas N° - '.implode(', ', $saleIds), $commission);

    //         Sale::whereIn('id', $request['ids'])->update(['token_payment' => $charge['id']]);
    //         DB::commit();

    //         return $this->jsonSuccess('Fatura criada com sucesso!', [
    //             'invoiceUrl' => $charge['invoiceUrl'],
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->jsonError('Erro no processo: ' . $e->getMessage(), 500);
    //     }
    // }

    // public function accountStatus($api_key) {
    //     try {
    //         $client = new Client();
            
    //         $options = [
    //             'headers' => [
    //                 'Content-Type' => 'application/json',
    //                 'access_token' => $api_key,
    //                 'User-Agent'   => env('APP_NAME')
    //             ],
    //             'verify' => false
    //         ];
    
    //         $response = $client->get(env('API_URL_ASSAS') . 'v3/myAccount/status/', $options);
    //         $body = (string) $response->getBody();
    
    //         if ($response->getStatusCode() === 200) {
    //             return json_decode($body, true);
    //         } else {
    //             return false;
    //         }
    //     } catch (\GuzzleHttp\Exception\ClientException $e) {
    //         return false;
    //     }
    // }

    // private function validateUser($customer) {
    //     $user = User::where('customer', $customer)->first();
    //     if (!$user || empty($user->customer)) {
    //         throw new \Exception('Verifique seus dados e tente novamente');
    //     }
    //     return $user;
    // }

    // private function validateFiliate($user) {
    //     if (!$user->filiate) {
    //         return null;
    //     }

    //     $filiate = User::find($user->filiate);
    //     if (!$filiate) {
    //         throw new \Exception('Verifique seus dados e tente novamente');
    //     }

    //     return $filiate;
    // }

    // private function getSales($ids) {
    //     $sales = Sale::whereIn('id', $ids)->where('status', 0)->with('product')->get();
    //     if ($sales->isEmpty()) {
    //         throw new \Exception('Nenhuma venda encontrada.');
    //     }
    //     return $sales;
    // }

    // private function calculateTotalValue($sales, $user) {
        
    //     $totalValue = 0;
    //     foreach ($sales as $sale) {
    //         if ($sale->product) {
    //             $totalValue += $user->fixed_cost > 0
    //                 ? $user->fixed_cost
    //                 : $sale->product->value_cost;
    //         }
    //     }

    //     if ($totalValue <= 0) {
    //         throw new \Exception('Valor total inválido!');
    //     }

    //     return $totalValue;
    // }

    // private function createInvoice($charge, $user, $totalValue, $description, $commission) {

    //     $invoice                     = new Invoice();
    //     $invoice->name               = $description;
    //     $invoice->description        = $description;
    //     $invoice->user_id            = $user->id;
    //     $invoice->id_product         = 0;
    //     $invoice->value              = $totalValue;
    //     $invoice->commission         = 0;
    //     $invoice->commission_filiate = $commission;
    //     $invoice->status             = 0;
    //     $invoice->type               = 2;
    //     $invoice->num                = 1;
    //     $invoice->due_date           = now()->addDay(2);
    //     $invoice->url_payment        = $charge['invoiceUrl'];
    //     $invoice->token_payment      = $charge['id'];
    //     $invoice->save();
    // }

    // private function jsonSuccess($message, $data = []) {
    //     return response()->json(array_merge(['success' => true, 'message' => $message], $data));
    // }

    // private function jsonError($message, $code) {
    //     return response()->json(['success' => false, 'message' => $message], $code);
    // }

    // private function sendInvoice($url_payment, $id, $message = null, $token = null) {

    //     $user = User::find($id);
    //     if ($user) {

    //         $client = new Client();

    //         $url = $token ?: 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-link';
    //         try {

    //             $response = $client->post($url, [
    //                 'headers' => [
    //                     'Content-Type'  => 'application/json',
    //                     'Accept'        => 'application/json',
    //                     'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
    //                 ],
    //                 'json' => [
    //                     'phone'           => '55' . $user->phone,
    //                     'message'         => $message ?? "Prezado(a) ".$user->name.", estamos enviando o link para pagamento da sua contratação aos serviços da nossa assessoria.  \r\n\r\n\r\n FAZER O PAGAMENTO CLIQUE NO LINK 👇🏼💳 \r\n",
    //                     'image'           => env('APP_URL_LOGO'),
    //                     'linkUrl'         => $url_payment,
    //                     'title'           => 'Pagamento de Fatura',
    //                     'linkDescription' => 'Link para Pagamento Digital',
    //                 ],
    //                 'verify' => false
    //             ]);

    //             return true;
    //         } catch (\Exception $e) {
    //             Log::error('Ao enviar notificação ao cliente Controller AssasController: '. $e->getMessage());
    //             return false;
    //         }
    //     }

    //     return false;
    // }

    // private function sendWhatsapp($link, $message, $phone, $token = null) {

    //     $client = new Client();
    //     $url = $token ?: 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-link';
    
    //     try {
    //         $response = $client->post($url, [
    //             'headers' => [
    //                 'Content-Type'  => 'application/json',
    //                 'Accept'        => 'application/json',
    //                 'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
    //             ],
    //             'json' => [
    //                 'phone'           => '55' . $phone,
    //                 'message'         => $message,
    //                 'image'           => env('APP_URL_LOGO'),
    //                 'linkUrl'         => $link,
    //                 'title'           => 'Assinatura de Documento',
    //                 'linkDescription' => 'Link para Assinatura Digital',
    //             ],
    //             'verify' => false
    //         ]);
    
    //         if ($response->getStatusCode() == 200) {
    //             return true;
    //         } else {
    //             return false;
    //         }
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    // }
}
