<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Exports\SalesExport;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleList;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller {

    public function viewSale($uuid) {
        
        $sale = Sale::where('uuid', $uuid)->first();
        if ($sale) {
            return view('app.Sale.view-sale', [
                'sale' => $sale,
            ]);
        }

        return redirect()->back()->with('error', 'Produto indisponível!');
    }

    public function createSale($product, $user = null) {

        $product = Product::find($product);
        if (!$product) {
            return redirect()->back()->with('error', 'Produto indisponível!');
        }

        if ($user) {
            $user = $user ? User::find($user) : null;
            if (!$user) {
                return redirect()->back()->with('error', 'Dados do cliente inválidos!');
            }
        }

        return view('app.Sale.create-sale', [
            'product' => $product, 
            'user'    => $user ?? null
        ]);
    }

    public function createdClientSale(Request $request) {

        $user = $this->createdUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, Auth::user()->id, Auth::user()->fixed_cost);
        if ($user !== false) {
            return redirect()->route('create-sale', [
                'product' => $request->product_id,
                'user'    => $user->id
                ])->with('success', 'Cliente incluído com sucesso!');
        }

        return redirect()->back()->with('info', 'Não foi possível incluir o cliente, verifique os dados e tente novamente!');
    }

    private function createdUser($name, $email, $cpfcnpj, $birth_date, $phone, $sponsor = null, $cost = null) {
        
        $cpfcnpj    = preg_replace('/\D/', '', $cpfcnpj);
        $email      = preg_replace('/[^\w\d\.\@\-\_]/', '', $email);
        $phone      = preg_replace('/\D/', '', $phone);

        $assas = new AssasController();
    
        $user = User::withTrashed()->where('cpfcnpj', preg_replace('/\D/', '', $cpfcnpj))->first();
        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
        } else {
            $user = new User([
                'cpfcnpj'       => $cpfcnpj,
                'password'      => bcrypt($cpfcnpj),
                'type'          => 3,
            ]);
        }
        
        $user->fill([
            'name'       => $name,
            'email'      => $email,
            'birth_date' => $birth_date,
            'phone'      => $phone,
            'sponsor_id' => $sponsor,
            'fixed_cost' => $cost,
        ]);

        if (empty($user->customer)) {
            $customer = $assas->createCustomer($name, $cpfcnpj, $phone, $email);
            if ($customer === false) {
                return false;
            }
        }

        return $user->save() ? $user : false;
    }

    public function createdPaymentSale(Request $request) {

        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->back()->with('error', 'Produto não disponível!');
        }

        $client = User::find($request->client_id);
        if (!$client) {
            return redirect()->back()->with('error', 'Cliente não disponível!');
        }

        $seller = User::find($request->seller_id);
        if (!$seller) {
            return redirect()->route('logout')->with('error', 'Acesso negado!');
        }
            
        if ((empty($seller->fixed_cost) || $seller->fixed_cost == 0) && $this->formatarValor($request->installments[1]['value'] ?? 0) < $product->value_min) {
            return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$product->value_min.'!');
        }

        if (($seller->fixed_cost > 0 )&& ($this->formatarValor($request->installments[1]['value'] ?? 0) < $seller->fixed_cost)) {
            return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$product->value_min.'!');
        }

        $list = SaleList::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não é possível lançar Vendas no momento, tente novamente mais tarde!');
        }

        $sale = $this->createdSale($seller, $client, $product, $list, $request->payment_method, $request->payment_installments, $request->installments);
        if ($sale) {
            return redirect()->back()->with('success', 'Venda cadastrada com sucesso!'); 
        }

        return redirect()->back()->with('error', 'Não foi possível incluir a venda, verifique os dados e tente novamente!'); 
    }

    private function createdSale($seller, $client, $product, $list, $paymentMethod, $paymentInstallments, $installments) {
        DB::beginTransaction();
    
        try {
            $sale = new Sale();
            $sale->uuid                 = Str::uuid();
            $sale->seller_id            = $seller->id;
            $sale->client_id            = $client->id;
            $sale->product_id           = $product->id;
            $sale->list_id              = $list->id;
            $sale->payment_method       = $paymentMethod;
            $sale->payment_installments = $paymentInstallments;
            $sale->save();
    
            $assas = new AssasController();
    
            foreach ($installments as $key => $installment) {
                $value    = $this->formatarValor($installment['value']);
                $dueDate  = $installment['due_date'];
                $commissions = [];
                $sponsorCommission = 0;
                $totalCommission = 0;
    
                if ($key == 1) {
                    $fixedCost = $seller->fixed_cost;
                    $totalCommission = max($value - $fixedCost, 0);
    
                    $sponsor = $seller->sponsor;
                    if ($sponsor) {
                        $sponsorCommission = max($fixedCost - $sponsor->fixed_cost, 0);
                        if ($sponsorCommission > 0) {
                            $commissions[] = [
                                'wallet'     => $sponsor->wallet,
                                'fixedValue' => $sponsorCommission,
                            ];
                            $totalCommission -= $sponsorCommission;
                        }
                    }
    
                    if ($totalCommission > 0) {
                        $commissions[] = [
                            'wallet'     => $seller->wallet,
                            'fixedValue' => number_format($totalCommission - 1, 2, '.', ''),
                        ];
                        $commissions[] = [
                            'wallet'     => env('WALLET_EXPRESS'),
                            'fixedValue' => number_format(1, 2, '.', ''),
                        ];
                    }
                } else {
                    $percent = $value * 0.05;
                    $totalCommission = $value - $percent;
    
                    $commissions[] = [
                        'wallet'     => $seller->wallet,
                        'fixedValue' => number_format($totalCommission, 2, '.', ''),
                    ];
                    $commissions[] = [
                        'wallet'     => env('WALLET_EXPRESS'),
                        'fixedValue' => number_format(1, 2, '.', ''),
                    ];
                    $g7Value = $percent - 1;
                    if ($g7Value > 0) {
                        $commissions[] = [
                            'wallet'     => env('WALLET_G7'),
                            'fixedValue' => number_format($g7Value, 2, '.', ''),
                        ];
                    }
                }
    
                $payment = $assas->createCharge($client->customer, $paymentMethod, $value, $dueDate, 'Fatura '.$key.' para venda N° '.$sale->id, $commissions);
    
                if (!$payment || !isset($payment['id'], $payment['invoiceUrl'])) {
                    throw new \Exception("Erro ao gera dados de pagamento para nova venda na parcela {$key}");
                }
    
                $invoice = new Invoice();
                $invoice->product_id          = $product->id;
                $invoice->user_id             = $client->id;
                $invoice->sale_id             = $sale->id;
                $invoice->name                = 'Fatura '.$key.' para venda N° '.$sale->id;
                $invoice->description         = 'Fatura '.$key.' para venda N° '.$sale->id;
                $invoice->num                 = $key;
                $invoice->value               = $value;
                $invoice->commission_seller  = $totalCommission ?? 0;
                $invoice->commission_sponsor = $sponsorCommission ?? 0;
                $invoice->type                = 1;
                $invoice->due_date            = $dueDate;
                $invoice->payment_token       = $payment['id'];
                $invoice->payment_url         = $payment['invoiceUrl'];
                $invoice->save();
            }
    
            DB::commit();
            return true;
    
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Erro ao gera dados de pagamento para nova venda: ' . $e->getMessage());
            return false;
        }
    }

    public function managerSale(Request $request) {
        
        $query = Sale::orderBy('created_at', 'desc');
    
        if (!empty($request->uuid)) {
            $query->where('uuid', $request->uuid);
        }

        if (!empty($request->name)) {
            $users = User::where('name', 'LIKE', '%' . $request->name . '%')->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('client_id', $users);
            }
        }

        if (!empty($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }
    
        if (!empty($request->value) && $this->formatarValor($request->value) > 0) {
            $query->where('value', $this->formatarValor($request->value));
        }
    
        if (!empty($request->list_id)) {
            $query->where('list_id', $request->list_id);
        }

        if (!empty($request->product_id)) {
            $query->where('product_id', $request->product_id);
        }
    
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        if (!empty($request->contract_sign)) {
            $query->whereNotNull('contract_sign');
        }

        if (!empty($request->payment_method)) {
            $query->where('payment_method', $request->payment_method);
        }
    
        if (!empty($request->label)) {
            $query->where('label', 'LIKE', '%'.$request->label.'%');
        }

        if (!empty($request->type) && $request->type == 'excel') {
            return Excel::download(new SalesExport($query->get()), 'Vendas.xlsx');
        }
    
        $sales      = $query->paginate(100);
        return view('app.Sale.manager-sale', [
            'sales'     => $sales,
        ]);
    }

    public function updatedSale(Request $request) {

        $sale = Sale::find($request->id);
        if(!$sale) {
            return redirect()->back()->with('error', 'Não encontramos dados da venda!');
        }

        if (!empty($request->status)) {
            $sale->status = $request->status;
        }
        
        if (!empty($request->id_list)) {
            $sale->id_list = $request->id_list;
        }

        if (!empty($request->guarantee)) {
            $sale->guarantee = $request->guarantee;
        }

        if (!empty($request->label)) {
            $sale->label = $request->label;
        }
        
        if($sale->save()) {
            return redirect()->back()->with('success', 'Dados alterados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível alterar os dados da venda!');
    }

    // public function deleteSale(Request $request) {

    //     $sale = Sale::find($request->id);
    //     if (!$sale) {
    //         return redirect()->back()->with('error', 'Não encontramos dados da venda!');
    //     }

    //     $invoices = Invoice::where('id_sale', $sale->id)->get();
    //     foreach ($invoices as $invoice) {
           
    //         $assasController = new AssasController();
    //         if($invoice->status <> 1) {
    //             $assasController->cancelInvoice($invoice->token_payment);
    //         }
            
    //         $invoice->delete();
    //     }

    //     if ($sale->delete()) {
    //         return redirect()->back()->with('success', 'Venda e Faturas excluídas com sucesso!');
    //     }
        
    //     return redirect()->back()->with('error', 'Não foi possível excluir a venda!');
    // }

    // public function reprotocolSale($id) {

    //     $sale = Sale::find($id);
    //     if (!$sale) {
    //         return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');   
    //     }

    //     if ($sale->status <> 1) {
    //         return redirect()->back()->with('info', 'Venda não foi confirmada!');   
    //     }

    //     $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
    //     if (!$list) {
    //         return redirect()->back()->with('error', 'Não há uma lista disponível para reprotocolar a venda!');
    //     }

    //     if (Auth::user()->type !== 1) {
    //         $invoices = Invoice::where('id_sale', $sale->id)->get();
    //         $tomorrow = now()->addDay();
    //         foreach ($invoices as $invoice) {
    //             if ($invoice->due_date <= $tomorrow && $invoice->status == 0) {
    //                 return redirect()->back()->with('error', 'Existem faturas vencidas associadas a Venda!');
    //             }
    //         }
    //     }
        
    //     $sale->id_list = $sale->label !== null 
    //                     ? $sale->id_list 
    //                     : $list->id;

    //     $sale->label = str_contains($sale->label, 'REPROTOCOLADO -') 
    //                 ? null 
    //                 : 'REPROTOCOLADO - ' . now()->format('d/m/Y');

    //     if ($sale->save()) {

    //         if ($sale->label !== null) {
    //             $clientName     = $sale->user->name;
    //             $phone          = $sale->user->phone;
    //             $sellerApiToken = $sale->seller->api_token_zapapi;
            
    //             $message = "*Assunto: Reprotocolamento de Processo Judicial*\r\n\r\n" .
    //                        "{$clientName},\r\n\r\n" .
    //                        "Gostaríamos de informar que o *seu processo* foi *reprotocolado com sucesso.*\r\n\r\n" .
    //                        "A partir de agora, será necessário *aguardar o prazo estimado de 20 a 30 dias*, " .
    //                        "conforme estipulado pelos trâmites judiciais, para a análise e andamento do seu caso.\r\n\r\n" .
    //                        "Estamos acompanhando de perto o andamento do processo e *entraremos em contato assim que houver novidades.*\r\n\r\n" .
    //                        "Agradecemos sua paciência e estamos à disposição para esclarecer qualquer dúvida.";
            
    //             $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sellerApiToken);
    //             return redirect()->back()->with('success', 'Processo reprotocolado!');
    //         } else {
    //             $clientName     = $sale->user->name;
    //             $phone          = $sale->user->phone;
    //             $sellerApiToken = $sale->seller->api_token_zapapi;
            
    //             $message = "*Assunto: Conclusão do Processo Judicial*\r\n\r\n" .
    //                        "{$clientName},\r\n\r\n" .
    //                        "É com satisfação que informamos que o *seu processo foi concluído com sucesso!*\r\n\r\n" .
    //                        "Agradecemos pela confiança em nosso trabalho.";
            
    //             $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sellerApiToken);
    //             return redirect()->back()->with('success', 'Processo concluído!');
    //         }            

    //         return redirect()->back()->with('success', 'Venda alterada com sucesso!');
    //     }

    //     return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');
    // }

    

    // public function approvedAll(Request $request) {

    //     try {
            
    //         $sales = Sale::whereIn('id', $request['ids'])->get();
    //         if ($sales->isEmpty()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Nenhuma venda encontrada!',
    //             ], 404);
    //         }
    
    //         foreach ($sales as $sale) {
    //             $sale->status = 1;
    //             $sale->save();
    //         }
    
    //         return response()->json([
    //             'success'       => true,
    //             'status'        => 'success',
    //             'message'       => 'Vendas aprovadas com sucesso!',
    //             'approved_ids'  => $sales->pluck('id')
    //         ], 200);
    
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success'   => false,
    //             'status'    => 'error',
    //             'message'   => 'Ocorreu um erro ao aprovar as vendas!',
    //             'details'   => $e->getMessage(),
    //         ], 500);
    //     }
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

    //         return true;
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    // }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
