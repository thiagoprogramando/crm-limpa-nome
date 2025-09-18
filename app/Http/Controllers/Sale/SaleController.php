<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Exports\SalesExport;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Link;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SaleController extends Controller {

    public function index(Request $request) {
        
        $query = Sale::orderBy('created_at', 'desc');
    
        $currentUser = Auth::user();
    
        $affiliateIds = User::where('filiate', $currentUser->id)->pluck('id')->toArray();
        $accessibleUserIds = array_merge([$currentUser->id], $affiliateIds);
    
        if (Auth::user()->type == 1) {
            if (!empty($request->seller_id)) {
                $query->where('seller_id', $request->seller_id);
            }
        } else {
            if (!empty($request->seller_id)) {
                $query->where('seller_id', $request->seller_id);
            } else {
                $query->whereIn('seller_id', $accessibleUserIds);
            }
        }

        if (!empty($request->name)) {
            $users = User::where('name', 'LIKE', '%' . $request->name . '%')->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('client_id', $users);
            }
        }

        if (!empty($request->cpfcnpj)) {
            $cpfcnpj = preg_replace('/\D/', '', $request->cpfcnpj);
            $users = User::where('cpfcnpj', $cpfcnpj)->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('client_id', $users);
            }
        }

        if (!empty($request->id)) {
            $query->where('id', $request->id);
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
    
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
    
        if (!empty($request->label)) {
            $query->where('label', 'LIKE', '%'.$request->label.'%');
        }

        if (!empty($request->type) && $request->type == 'excel') {
            return Excel::download(new SalesExport($query->get()), 'Vendas.xlsx');
        }
    
        $sales      = $query->paginate(100);
        $sellers    = $currentUser->type == 1 
                        ? User::whereIn('type', [1, 2, 4, 5])->orderBy('name', 'asc')->get() 
                        : User::where('type', [2])->where('filiate', $currentUser->id)->orderBy('name', 'asc')->get();
        $lists      = Lists::orderBy('created_at', 'desc')->get();

        return view('app.Sale.list-sales', [
            'sales'     => $sales,
            'lists'     => $lists,
            'sellers'   => $sellers
        ]);
    }

    public function show($id) {

        $sale       = Sale::find($id);
        if (!$sale) {
            return redirect()->back()->with('info', 'Não encontramos dados da venda!');
        }

        $invoices = Invoice::where('sale_id', $sale->id)->orWhere('payment_token', $sale->payment_token)->get();

        return view('app.Sale.view', [
            'sale'      => $sale, 
            'invoices'  => $invoices,
        ]);
    }

    public function create(Request $request, $id, $type = null, $user = null) {

        $product = Product::find($id);
        if (!$product) {
            return redirect()->back()->with('info', 'Produto indisponível!');
        }

        if ($user) {
            $user = User::find($user);
            if (!$user) {
                return redirect()->back()->with('info', 'Usuário não encontrado!');
            }
        }

        if ($product->status != 1) {
            return redirect()->back()->with('info', 'Em breve!!');
        }

        $query = Sale::where('seller_id', Auth::user()->id)->whereIn('status', [0, 2])->orderBy('created_at', 'desc');

        if (!empty($request->name)) {
            $users = User::where('name', 'LIKE', '%' . $request->name . '%')->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('client_id', $users);
            }
        }
        if (!empty($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }
        if (!empty($request->value) && $this->formatValue($request->value) > 0) {
            $query->where('value', $this->formatValue($request->value));
        }
        if (!empty($request->list_id)) {
            $query->where('list_id', $request->list_id);
        }
        if (!empty($request->product_id)) {
            $query->where('product_id', $request->product_id);
        }
        if ($type) {
            $query->where('type', $type);
        }

        return view('app.Sale.create', [
            'product' => $product, 
            'user'    => $user,
            'type'    => $type,
            'sales'   => $query->get(),
        ]);
    }

    public function createdClientSale(Request $request) {

        if (empty(Auth::user()->token_wallet) || empty(Auth::user()->token_wallet)) {
            return redirect()->back()->with('info', 'Necessário Integrar  APIs de uma Carteira Digital!');
        }

        $user = $this->createdUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, Auth::user()->id, Auth::user()->fixed_cost);
        if ($user['status'] === true) {
            return redirect()->route('create-sale', ['product' => $request->product_id, 'type' => 1, 'user' => $user['id'] ])->with('success', 'Cliente incluído com sucesso!');
        }

        return redirect()->back()->with('info', 'Não foi possível incluir o cliente! '.$user['message']);
    }

    public function createdPaymentSale(Request $request) {

        $assas = new AssasController();

        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->back()->with('error', 'Produto não disponível!');
        }

        $client = User::find($request->client_id);
        if (!$client) {
            return redirect()->back()->with('error', 'Cliente não disponível!');
        }

        $customer = $assas->createCustomer($client->name, $client->cpfcnpj);
        if ($customer === false) {
            return false;
        }

        $seller = User::find($request->seller_id);
        if (!$seller) {
            return redirect()->route('logout')->with('error', 'Acesso negado!');
        }

        if (($product->value_min > 0) && ($this->formatValue($request->installments[1]['value'] ?? 0) < $product->value_min)) {
            return redirect()->back()->with('error', 'O valor mín de Entrada é: R$ '.$product->value_min.'!');
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não há Lista disponível no momento, aguarde uma nova!');
        }

        $sale = $this->createdSale($customer, $seller, $client, $product, $list, $request->payment_method, $request->payment_installments, $request->installments);
        if (!empty($sale['id'])) {
            return redirect()->route('view-sale', ['id' => $sale['id']])->with('success', 'Venda cadastrada com sucesso!'); 
        }

        return redirect()->back()->with('error', 'Não foi possível incluir a venda, verifique os dados e tente novamente!'); 
    }

    private function createdSale($customer, $seller, $client, $product, $list, $paymentMethod, $paymentInstallments, $installments) {

        DB::beginTransaction();

        $assas = new AssasController();
    
        try {
            $sale = new Sale();
            $sale->seller_id            = $seller->id;
            $sale->client_id            = $client->id;
            $sale->product_id           = $product->id;
            $sale->list_id              = $list->id;
            $sale->payment_method       = $paymentMethod;
            $sale->payment_installments = $paymentInstallments;
            $sale->type                 = 1;
            $sale->status               = 2;
            $sale->save();
    
            foreach ($installments as $key => $installment) {
                $value              = $this->formatValue($installment['value']);
                $dueDate            = $installment['due_date'];
                $commissions        = [];
                $sponsorCommission  = 0;
                $totalCommission    = 0;
                $uuid               = Str::uuid();
    
                if ($key == 1) {
                    $fixedCost       = ($seller->fixed_cost ?? $product->value_cost);
                    $netValue        = $value - $fixedCost;
                    $totalCommission = max($netValue - ($netValue * 0.05) - 3, 0);

                    $sponsor = $seller->parent;
                    $sponsorCommission  = 0;
                    if ($sponsor) {
                        $sponsorCommission = max($fixedCost - $sponsor->fixed_cost, 0);
                        if ($sponsorCommission > 0) {
                            $commissions[] = [
                                'walletId'          => $sponsor->token_wallet,
                                'fixedValue'        => $sponsorCommission,
                                'externalReference' => $uuid,
                                'description'       => 'Comissão de Patrocinador para venda N° '.$sale->id,
                            ];
                        }
                    }
                    
                    $cost = $totalCommission == 0 ? ($fixedCost - $sponsorCommission) - 2 : ($fixedCost - $sponsorCommission);
                    $commissions[] = [
                        'walletId'          => env('WALLET_G7'),
                        'fixedValue'        => max(($cost), 0),
                        'externalReference' => $uuid,
                        'description'       => 'Comissão Associação para venda N° '.$sale->id,
                    ];
    
                    if ($totalCommission > 0) {
                        $commissions[] = [
                            'walletId'          => $seller->token_wallet,
                            'fixedValue'        => number_format($totalCommission, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Comissão vendedor da Fatura '.$key.' para venda N° '.$sale->id,
                        ];
                        $commissions[] = [
                            'walletId'          => env('WALLET_EXPRESS'),
                            'fixedValue'        => number_format(1, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Comissão % para venda N° '.$sale->id,
                        ];
                    }
                } else {

                    $percent         = $value * 0.05;
                    $totalCommission = max(($value - $percent - 2), 0);

                    if ($totalCommission > 0) {
                        $commissions[] = [
                            'walletId'          => $seller->token_wallet,
                            'fixedValue'        => number_format($totalCommission, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Comissão vendedor da Fatura '.$key.' para venda N° '.$sale->id,
                        ];

                        if ($percent > 1) {
                            $commissions[] = [
                                'walletId'          => env('WALLET_EXPRESS'),
                                'fixedValue'        => number_format(1, 2, '.', ''),
                                'externalReference' => $uuid,
                                'description'       => 'Comissão % para venda N° '.$sale->id,
                            ];
        
                            $commissions[] = [
                                'walletId'          => env('WALLET_G7'),
                                'fixedValue'        => number_format($percent - 1, 2, '.', ''),
                                'externalReference' => $uuid,
                                'description'       => 'Comissão % para venda N° '.$sale->id,
                            ];
                        }
                    }
                }
    
                $payment = $assas->createCharge($customer, $paymentMethod, $value, 'Fatura '.$key.' para venda N° '.$sale->id, $dueDate, $commissions);
                if (!$payment || !isset($payment['id'], $payment['invoiceUrl'])) {
                    throw new \Exception("Erro ao gerar dados de pagamento para a parcela {$key}");
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
                $invoice->commission_filiate = $sponsorCommission ?? 0;
                $invoice->type                = 2;
                $invoice->status              = 2;
                $invoice->due_date            = $dueDate;
                $invoice->payment_token       = $payment['id'];
                $invoice->payment_url         = $payment['invoiceUrl'];
                $invoice->payment_splits      = json_encode($payment['splits']);
                if ($invoice->save()) {
                    if ($key == 1) {
                        $message = "Prezado(a) {$sale->client->name}, estamos enviando o link para pagamento da sua contratação aos serviços da nossa assessoria. \r\n\r\n\r\n"."Consulte os termos do seu contrato aqui👇🏼 \r\n".env('APP_URL')."preview-contract/".$sale->id."\r\n\r\n\r\n"."PARA FAZER O PAGAMENTO CLIQUE NO LINK 👇🏼💳";
                        $this->sendWhatsapp($payment['invoiceUrl'], $message, $sale->client->phone, $sale->client->getTokenWhatsapp());
                    }
                }
            }
    
            DB::commit();
            return [
                'id' => $sale->id,
            ];
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Erro na criação de venda:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }    

    public function update(Request $request) {

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
        
        if($sale->save()) {
            return redirect()->back()->with('success', 'Dados alterados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível alterar os dados da venda!');
    }

    public function destroy(Request $request) {

        $sale = Sale::find($request->id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Não encontramos dados da venda!');
        }

        $invoices = Invoice::where('sale_id', $sale->id)->get();
        foreach ($invoices as $invoice) {
           
            $assasController = new AssasController();
            if($invoice->status <> 1) {
                $assasController->cancelInvoice($invoice->token_payment);
            }
            
            $invoice->delete();
        }

        if ($sale->delete()) {
            return redirect()->back()->with('success', 'Venda e Faturas excluídas com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível excluir a venda!');
    }

    public function reprotocolSale($id) {

        $sale = Sale::find($id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');   
        }

        if ($sale->status <> 1) {
            return redirect()->back()->with('info', 'Venda não foi confirmada!');   
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não há uma lista disponível para reprotocolar a venda!');
        }

        if (Auth::user()->type !== 1) {
            $invoices = Invoice::where('id_sale', $sale->id)->get();
            $tomorrow = now()->addDay();
            foreach ($invoices as $invoice) {
                if ($invoice->due_date <= $tomorrow && $invoice->status == 0) {
                    return redirect()->back()->with('error', 'Existem faturas vencidas associadas a Venda!');
                }
            }
        }
        
        $sale->list_id = $sale->label !== null 
                        ? $sale->list_id 
                        : $list->id;

        $sale->label = str_contains($sale->label, 'REPROTOCOLADO -') 
                    ? null 
                    : 'REPROTOCOLADO - ' . now()->format('d/m/Y');

        if ($sale->save()) {

            if ($sale->label !== null) {

                $clientName  = $sale->client->name;
                $phone       = $sale->client->phone;
                $message     = "*Assunto: Reprotocolamento de Processo Judicial*\r\n\r\n" .
                           "{$clientName},\r\n\r\n" .
                           "Gostaríamos de infomar que o *seu processo* foi *reprotocolado com sucesso.*\r\n\r\n" .
                           "A partir de agora, será necessário *aguardar o prazo estimado de 20 a 30 dias*, " .
                           "conforme estipulado pelos trâmites judiciais, para a análise e andamento do seu caso.\r\n\r\n" .
                           "Estamos acompanhando de perto o andamento do processo e *entraremos em contato assim que houver novidades.*\r\n\r\n" .
                           "Agradecemos sua paciência e estamos à disposição para esclarecer qualquer dúvida.";
            
                $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sale->seller->getTokenWhatsapp());
                return redirect()->back()->with('success', 'Processo reprotocolado!');
            } else {

                $clientName  = $sale->client->name;
                $phone       = $sale->client->phone;
                $message     = "*Assunto: Conclusão do Processo Judicial*\r\n\r\n" .
                           "{$clientName},\r\n\r\n" .
                           "É com satisfação que infomamos que o *seu processo foi concluído com sucesso!*\r\n\r\n" .
                           "Agradecemos pela confiança em nosso trabalho.";
            
                $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sale->seller->getTokenWhatsapp());
                return redirect()->back()->with('success', 'Processo concluído!');
            }            

            return redirect()->back()->with('success', 'Venda alterada com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');
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

    private function createUser($name, $email, $cpfcnpj, $birth_date, $phone, $filiate = null) {
        
        $cpfcnpj = preg_replace('/\D/', '', $cpfcnpj);
        $email = preg_replace('/[^\w\d\.\@\-\_]/', '', $email);
    
        $user = User::withTrashed()->where('cpfcnpj', preg_replace('/\D/', '', $cpfcnpj))->first();
        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
        } else {
            $user = new User([
                'cpfcnpj' => $cpfcnpj,
                'password' => bcrypt($cpfcnpj),
                'type' => 3,
            ]);
            $user->filiate = $filiate; 
        }
        
        $user->fill([
            'name'       => $name,
            'email'      => $email,
            'birth_date' => $birth_date,
            'phone'      => $phone,
        ]);

        if (!$user->exists) {
            $user->password = bcrypt($cpfcnpj);
            $user->type     = 3;
        }

        return $user->save() ? $user : false;
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

    public function createdSaleAssociation(Request $request, $product, $type = null) {

        $product = Product::find($product);
        if (!$product) {
            return redirect()->back()->with('info', 'Produto indisponível!');
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('info', 'Não há Lista disponível no momento, aguarde uma nova Lista!');
        }
        
        $client = $this->createdUser($request->name, null, $request->cpfcnpj);
        if ($client['status'] === false) {
            return redirect()->back()->with('info', 'Não foi possível incluir o cliente! '.$client['message']);
        }   

        $sale = new Sale();
        $sale->seller_id            = $request->seller_id ?? Auth::id();
        $sale->client_id            = $client['id'];
        $sale->product_id           = $product->id;
        $sale->list_id              = $list->id;
        $sale->payment_method       = 'PIX';
        $sale->payment_installments = 1;
        $sale->type                 = 2;
        $sale->status               = 2;
        if ($sale->save()) {
            return redirect()->back()->with('success', 'Sucesso! Dados enviados com sucesso!');
        }

        return redirect()->back()->with('info', 'Não foi possível adicionar o nome, verifique os dados e tente novamente!');
    }

    public function createdSaleExcel(Request $request, $product, $type = null) {

        $product = Product::find($product);
        if (!$product) {
            return redirect()->back()->with('info', 'Produto indisponível!');
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('info', 'Não há Lista disponível no momento, aguarde uma nova Lista!');
        }

        if (!$request->hasFile('file')) {
            return redirect()->back()->with('info', 'Selecione um arquivo para importar!');
        }

        $file           = $request->file('file');
        $spreadsheet    = IOFactory::load($file->getPathname());
        $worksheet      = $spreadsheet->getActiveSheet();
        $rows           = $worksheet->toArray();

        $data           = []; 
        $createdSales   = 0;

        for ($i = 3; $i < count($rows); $i++) {

            $row         = $rows[$i];
            $nome        = trim($row[0] ?? '');
            $cpfcnpj     = trim($row[1] ?? '');
            $birth_date  = trim($row[2] ?? '');    

            if (empty($nome) || empty($cpfcnpj)) {
                continue;
            }

            if (!(self::validateCpf($cpfcnpj) || self::validateCnpj($cpfcnpj))) {
                continue;
            }

            $createdUser = $this->createdUser($nome, null, $cpfcnpj, $birth_date);
            if (!$createdUser['status']) {
                continue;
            }

            $sale                       = new Sale();
            $sale->seller_id            = Auth::id();
            $sale->client_id            = $createdUser['id'];
            $sale->product_id           = $product->id;
            $sale->list_id              = $list->id;
            $sale->payment_method       = 'PIX';
            $sale->payment_installments = 1;
            $sale->type                 = 2;
            $sale->status               = 2;
            $sale->save();

            $createdSales++;
        }

        return redirect()->route('create-sale', ['product' => $product->id, 'type' => $type])->with('success', 'Importação concluída! ' . $createdSales . ' vendas criadas com sucesso!');
    }

    public function externalSale($uuid) {

        $link = Link::where('uuid', $uuid)->first();
        if (!$link) {
            return redirect()->route('login.cliente')->with('info', 'Link inválido ou expirado!');
        }

        return view('app.Sale.form-sale', [
            'link' => $link
        ]);
    }

    public function createdExternalSale(Request $request, $product, $link, $type = null) {

        $assas = new AssasController();

        $link = Link::where('uuid', $link)->first();
        if (!$link) {
            return redirect()->back()->with('info', 'Link expirado! Entre em contato com o vendedor!');
        }

        $product = Product::find($product);
        if (!$product) {
            return redirect()->back()->with('info', 'Produto indisponível no momento! Entre em contato com o vendedor!');
        }
        
        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('info', 'Não foi possível concluir a venda, verique os dados e tente novamente!');
        }

        $seller = User::find($link->user_id);
        if (!$seller) {
            return redirect()->back()->with('info', 'Não foi possível concluir a venda, verique os dados e tente novamente!');
        }
        
        $client = $this->createdUser($request->name, null, $request->cpfcnpj, $request->birth_date, $request->phone, $seller->id, $seller->fixed_cost);
        if ($client['status'] === false) {
            return redirect()->back()->with('info', 'Ops! Parece que há informações incorretas, verique seus dados e tente novamente!');
        }

        $client = User::find($client['id']);
        if (!$client) {
            return redirect()->back()->with('error', 'Ops! Parece que há informações incorretas, verique seus dados e tente novamente!');
        }

        $customer = $assas->createCustomer($client['name'], $client['cpfcnpj']);
        if ($customer === false) {
            return redirect()->back()->with('info', 'Ops! Parece que há informações incorretas, verique seus dados e tente novamente!');
        }

        $sale = $this->createdSale($customer, $seller, $client, $product, $list, $link->payment_method, $link->payment_installments, $link->payment_json_installments);
        if (!empty($sale['id'])) {
            return redirect()->back()->with('success', 'Seus dados foram enviados com sucesso! Enviaremos os próximos passos para o seu whatsapp!'); 
        }

        return redirect()->back()->with('error', 'Ops! Parece que há informações incorretas, verique seus dados e tente novamente!'); 
    }

    private static function validateCpf($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    private static function validateCnpj($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) != 14) return false;

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $c = 0, $p = $t - 7; $c < $t; $c++, $p--) {
                $p = $p < 2 ? 9 : $p;
                $d += $cnpj[$c] * $p;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) return false;
        }
        return true;
    }

    private function createdUser($name, $email = null, $cpfcnpj, $birth_date = null, $phone = null, $filiate = null, $cost = null) {
        
        $cpfcnpj    = preg_replace('/\D/', '', $cpfcnpj);
        $email      = preg_replace('/[^\w\d\.\@\-\_]/', '', $email);
        $phone      = preg_replace('/\D/', '', $phone);

        if (str_word_count(trim($name)) < 2) {
            return [
                'status'  => false,
                'message' => 'infomar Nome Completo!'
            ];
        }

        $assas = new AssasController();
    
        $user = User::withTrashed()->where('cpfcnpj', preg_replace('/\D/', '', $cpfcnpj))->first();
        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }

            $user->fixed_cost   = $cost;
            
            if ($user->filiate == null) {
                $user->filiate = $filiate ?? Auth::id();
            }
        } else {
            $user = new User([
                'cpfcnpj'           => $cpfcnpj,
                'password'          => bcrypt($cpfcnpj),
                'type'              => 3,
                'filiate'           => $filiate ?? Auth::id(), 
                'fixed_cost'        => $cost,  
            ]);
        }
        
        if (!empty($name)) {
            $user->name = $name;
        }
        if (!empty($email)) {
            $user->email = $email;
        }
        if (!empty($birth_date)) {
            $user->birth_date = $birth_date;
        }
        if (!empty($phone)) {
            $user->phone = $phone;
        }
        
        $customer = $assas->createCustomer($name, $cpfcnpj);
        if ($customer === false) {
            return [
                'status'  => false,
                'message' => 'Verfique os dados do Cliente e tente novamente!'
            ];
        }

        if ($user->save()) {
            return [
                'status'  => true,
                'id'      => $user->id,
                'name'    => $user->name,
                'cpfcnpj' => $user->cpfcnpj,
            ];
        }

        return [
                'status'  => false,
                'message' => 'Verfique os dados do Cliente e tente novamente!'
            ];
    }

    private function formatValue($valor) {

        if ($valor === null) {
            return 0.00;
        }

        $valor = trim(preg_replace('/[^\d.,]/', '', $valor));
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor); 
            $valor = str_replace(',', '.', $valor);
        }

        return number_format((float) $valor, 2, '.', '');
    }

}
