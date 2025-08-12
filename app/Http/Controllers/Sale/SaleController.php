<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Gateway\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleList;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function createSale(Request $request, $product, $user = null) {

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

        $query = Sale::where('status', 2)->orderBy('created_at', 'desc');
        
        $authUser           = Auth::user();
        $affiliateIds       = User::where('sponsor_id', $authUser->id)->pluck('id')->toArray();
        $accessibleUserIds  = array_merge([$authUser->id], $affiliateIds);
    
        if (Auth::user()->type == 1) {
            if (!empty($request->id_seller)) {
                $query->where('seller_id', $request->seller_id);
            }
        } else {
            if (!empty($request->seller_id)) {
                $query->where('seller_id', $request->seller_id);
            } else {
                $query->whereIn('seller_id', $accessibleUserIds);
            }
        }

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
        if (!empty($request->value) && $this->formatValue($request->value) > 0) {
            $query->where('value', $this->formatValue($request->value));
        }
        if (!empty($request->list_id)) {
            $query->where('list_id', $request->list_id);
        }
        if (!empty($request->product_id)) {
            $query->where('product_id', $request->product_id);
        }
        if (!empty($request->label)) {
            $query->where('label', 'LIKE', '%'.$request->label.'%');
        }

        $sales = $query->whereIn('type', [2, 3])->paginate(30);
        return view('app.Sale.create-sale', [
            'product'    => $product, 
            'user'       => $user ?? null,
            'sales'      => $sales,
            'tab'        => $tab ?? 'sale-justified'
        ]);
    }

    public function createdClientSale(Request $request) {

        $user = $this->createdUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, Auth::user()->id, Auth::user()->fixed_cost, Auth::user()->association_id);
        if ($user['status'] === true) {
            return redirect()->route('create-sale', ['product' => $request->product_id, 'user' => $user['id'] ])->with('success', 'Cliente incluído com sucesso!');
        }

        return redirect()->back()->with('info', 'Não foi possível incluir o cliente! '.$user['message']);
    }

    private function createdUser($name, $email = null, $cpfcnpj, $birth_date = null, $phone = null, $sponsor = null, $cost = null, $association_id = null) {
        
        $cpfcnpj    = preg_replace('/\D/', '', $cpfcnpj);
        $email      = preg_replace('/[^\w\d\.\@\-\_]/', '', $email);
        $phone      = preg_replace('/\D/', '', $phone);

        if (str_word_count(trim($name)) < 2) {
            return [
                'status'  => false,
                'message' => 'Informar Nome Completo!'
            ];
        }

        $assas = new AssasController();
    
        $user = User::withTrashed()->where('cpfcnpj', preg_replace('/\D/', '', $cpfcnpj))->first();
        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
        } else {
            $user = new User([
                'uuid'              => str::uuid(),
                'cpfcnpj'           => $cpfcnpj,
                'password'          => bcrypt($cpfcnpj),
                'type'              => 3,
                'association_id'    => $association_id,
                'sponsor_id'        => $sponsor ?? Auth::id(), 
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
                'id'      => $user->id
            ];
        }

        return [
                'status'  => false,
                'message' => 'Verfique os dados do Cliente e tente novamente!'
            ];
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

        $assas = new AssasController();
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

        $list = SaleList::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não há Lista disponível no momento, aguarde uma nova!');
        }

        $sale = $this->createdSale($customer, $seller, $client, $product, $list, $request->payment_method, $request->payment_installments, $request->installments);
        if (!empty($sale['uuid'])) {
            return redirect()->route('view-sale', ['uuid' => $sale['uuid']])->with('success', 'Venda cadastrada com sucesso!'); 
        }

        return redirect()->back()->with('error', 'Não foi possível incluir a venda, verifique os dados e tente novamente!'); 
    }

    private function createdSale($customer, $seller, $client, $product, $list, $paymentMethod, $paymentInstallments, $installments) {

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
                $value              = $this->formatValue($installment['value']);
                $dueDate            = $installment['due_date'];
                $commissions        = [];
                $sponsorCommission  = 0;
                $totalCommission    = 0;
                $uuid               = str::uuid();
    
                if ($key == 1) {
                    $fixedCost       = ($seller->fixed_cost ?? $product->value_cost);
                    $totalCommission = max(($value - $fixedCost) - 5, 0);

                    $sponsor = $seller->sponsor;
                    $sponsorCommission  = 0;
                    if ($sponsor) {
                        $sponsorCommission = max($fixedCost - $sponsor->fixed_cost, 0);
                        if ($sponsorCommission > 0 && $sponsor->token_issuer !== 'MY') {
                            $commissions[] = [
                                'walletId'          => $sponsor->token_wallet,
                                'fixedValue'        => $sponsorCommission - 2,
                                'externalReference' => $uuid,
                                'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                            ];
                        }
                    }
                    
                    $commissions[] = [
                        'walletId'          => env('APP_WALLET_ASSAS'),
                        'fixedValue'        => max(($fixedCost - $sponsorCommission), 0),
                        'externalReference' => $uuid,
                        'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                    ];
    
                    if ($totalCommission > 0 && $seller->token_issuer !== 'MY') {
                        $commissions[] = [
                            'walletId'          => $seller->token_wallet,
                            'fixedValue'        => number_format($totalCommission, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                        ];
                        $commissions[] = [
                            'walletId'          => env('WALLET_EXPRESS'),
                            'fixedValue'        => number_format(1, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                        ];
                    }
                } else {
                    $percent = $value * 0.05;
                    $totalCommission = ($value - $percent - 5);
                    
                    if ($totalCommission > 0 && $seller->type !== 99 && $seller->type !== 1) {
                        $commissions[] = [
                            'walletId'   => $seller->token_wallet,
                            'fixedValue' => number_format($totalCommission, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                        ];
                    }

                    if ($totalCommission > 0) {
                        $commissions[] = [
                            'walletId'   => env('WALLET_EXPRESS'),
                            'fixedValue' => number_format(1, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                        ];
                        $commissions[] = [
                            'walletId'   => env('WALLET_G7'),
                            'fixedValue' => number_format($percent - 1, 2, '.', ''),
                            'externalReference' => $uuid,
                            'description'       => 'Fatura '.$key.' para venda N° '.$sale->id,
                        ];
                    }
                }
    
                $payment = $assas->createCharge($customer, $paymentMethod, $value, $dueDate, 'Fatura '.$key.' para venda N° '.$sale->id, $commissions, env('APP_TOKEN_ASSAS'));
                if (!$payment || !isset($payment['id'], $payment['invoiceUrl'])) {
                    throw new \Exception("Erro ao gerar dados de pagamento para a parcela {$key}");
                }
    
                $invoice = new Invoice();
                $invoice->uuid                = $uuid;
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
                $invoice->status              = 2;
                $invoice->due_date            = $dueDate;
                $invoice->payment_token       = $payment['id'];
                $invoice->payment_url         = $payment['invoiceUrl'];
                $invoice->payment_splits      = json_encode($payment['splits']);
                $invoice->save();
            }
    
            DB::commit();
            return [
                'uuid' => $sale->uuid,
            ];
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Erro na criação de venda:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }
    
    public function createdSaleExcel(Request $request, $product, $tab = null) {

        $product = Product::find($product);
        if (!$product) {
            return redirect()->back()->with('infor', 'Produto indisponível!');
        }

        $list = SaleList::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('infor', 'Não há Lista disponível no momento, aguarde uma nova Lista!');
        }

        if (!$request->hasFile('file')) {
            return redirect()->back()->with('infor', 'Selecione um arquivo para importar!');
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

            $createdUser = $this->createdUser($nome, null, $cpfcnpj, $birth_date, env('APP_TOKEN_ASSAS'));
            if (!$createdUser['status']) {
                continue;
            }

            $sale                      = new Sale();
            $sale->uuid                 = Str::uuid();
            $sale->seller_id            = Auth::id();
            $sale->client_id            = $createdUser['id'];
            $sale->product_id           = $product->id;
            $sale->list_id              = $list->id;
            $sale->payment_method       = 'PIX';
            $sale->payment_installments = 1;
            $sale->type                 = 2;
            $sale->save();

            $createdSales++;
        }

        return redirect()->route('create-sale', ['product' => $product->id, 'user' => null, 'tab' => $tab])->with('success', 'Importação concluída! ' . $createdSales . ' vendas criadas com sucesso!');
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

    public function createdSaleAssociation(Request $request, $product, $tab = null) {

        $product = Product::find($product);
        if (!$product) {
            return redirect()->back()->with('infor', 'Produto indisponível!');
        }

        $list = SaleList::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('infor', 'Não há Lista disponível no momento, aguarde uma nova Lista!');
        }
        
        $client = $this->createdUser($request->name, null, $request->cpfcnpj);
        if ($client['status'] === false) {
            return redirect()->back()->with('infor', 'Não foi possível incluir o cliente! '.$client['message']);
        }   

        $sale = new Sale();
        $sale->uuid                 = Str::uuid();
        $sale->seller_id            = Auth::user()->id;
        $sale->client_id            = $client['id'];
        $sale->product_id           = $product->id;
        $sale->list_id              = $list->id;
        $sale->payment_method       = 'PIX';
        $sale->payment_installments = 1;
        $sale->type                 = 3;  
        if ($sale->save()) {
            return redirect()->route('create-sale', ['product' => $product->id])->with('success', 'Sucesso! Nome incluído com sucesso!');
        }

        return redirect()->back()->with('infor', 'Não foi possível adicionar o nome, verifique os dados e tente novamente!');
    }

    public function listSale(Request $request) {
        
        $query = Sale::orderBy('created_at', 'desc');

        $authUser = Auth::user();
    
        $affiliateIds = User::where('sponsor_id', $authUser->id)->pluck('id')->toArray();
        $accessibleUserIds = array_merge([$authUser->id], $affiliateIds);
    
        if (Auth::user()->type == 1) {
            if (!empty($request->id_seller)) {
                $query->where('seller_id', $request->seller_id);
            }
        } else {
            if (!empty($request->seller_id)) {
                $query->where('seller_id', $request->seller_id);
            } else {
                $query->whereIn('seller_id', $accessibleUserIds);
            }
        }
    
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
    
        if (!empty($request->value) && $this->formatValue($request->value) > 0) {
            $query->where('value', $this->formatValue($request->value));
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
    
        return view('app.Sale.list-sales', [
            'sales'     => $query->paginate(100),
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

    public function deletedSale(Request $request) {

        $sale = Sale::where('uuid', $request->uuid)->first();
        if (!$sale) {
            return redirect()->back()->with('error', 'Venda não encontrada!');
        }

        if ($sale->status == 1) {
            return redirect()->back()->with('info', 'Venda já confirmada, não é possível excluir!');
        }

        $invoices = Invoice::where('sale_id', $sale->id)->orWhere('payment_token', $sale->payment_token)->get();
        foreach ($invoices as $invoice) {
           
            $assasController = new AssasController();
            if($invoice->status <> 1) {
                $assasController->cancelCharge($invoice->payment_token, env('APP_TOKEN_ASSAS'));
            }
            
            $invoice->delete();
        }

        if ($sale->delete()) {
            return redirect()->back()->with('success', 'Venda e Faturas canceladas com sucesso!');
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

        $list = SaleList::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não há uma lista disponível para reprotocolar a venda!');
        }
        
        $sale->list_id = $sale->label !== null 
                        ? $sale->list_id 
                        : $list->id;

        $sale->label = str_contains($sale->label, 'REPROTOCOLADO -') 
                    ? null 
                    : 'REPROTOCOLADO - ' . now()->format('d/m/Y');

        if ($sale->save()) {

            if ($sale->label !== null) {
                return redirect()->back()->with('success', 'Processo reprotocolado!');
            } else {
                return redirect()->back()->with('success', 'Processo concluído!');
            }            

            return redirect()->back()->with('success', 'Venda alterada com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');
    }

    public function approvedAllPayment(Request $request) {

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

    private function formatValue($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
