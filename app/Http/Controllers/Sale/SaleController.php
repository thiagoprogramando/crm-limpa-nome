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

        $user = $this->createdUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, Auth::user()->id, Auth::user()->fixed_cost, Auth::user()->association_id);
        if ($user['status'] === true) {
            return redirect()->route('create-sale', ['product' => $request->product_id, 'user'    => $user['id'] ])->with('success', 'Cliente incluído com sucesso!');
        }

        return redirect()->back()->with('info', 'Não foi possível incluir o cliente! '.$user['message']);
    }

    private function createdUser($name, $email = null, $cpfcnpj, $birth_date, $phone = null, $sponsor = null, $cost = null, $association_id = null) {
        
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
                'uuid'          => str::uuid(),
                'cpfcnpj'       => $cpfcnpj,
                'password'      => bcrypt($cpfcnpj),
                'type'          => 3,
            ]);
        }
        
        $user->fill([
            'name'            => $name,
            'email'           => $email,
            'birth_date'      => $birth_date,
            'phone'           => $phone,
            'sponsor_id'      => $sponsor,
            'association_id'  => $association_id,
            'fixed_cost'      => $cost,
        ]);

        
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
        $customer = $assas->createCustomer($client->name, $client->cpfcnpj, $client->phone, $client->email);
        if ($customer === false) {
            return false;
        }

        $seller = User::find($request->seller_id);
        if (!$seller) {
            return redirect()->route('logout')->with('error', 'Acesso negado!');
        }
            
        if ((empty($seller->fixed_cost) || $seller->fixed_cost == 0) && $this->formatValue($request->installments[1]['value'] ?? 0) < $product->value_min) {
            return redirect()->back()->with('error', 'O valor mín de Entrada é: R$ '.$product->value_min.'!');
        }

        if (($seller->fixed_cost > 0 ) && ($this->formatValue($request->installments[1]['value'] ?? 0) < $seller->fixed_cost)) {
            return redirect()->back()->with('error', 'O valor mín de Entrada é: R$ '.$seller->fixed_cost.'!');
        }

        $list = SaleList::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('error', 'Não é possível lançar Vendas no momento, tente novamente mais tarde!');
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
                $value    = $this->formatValue($installment['value']);
                $dueDate  = $installment['due_date'];
                $commissions = [];
                $sponsorCommission = 0;
                $totalCommission = 0;
    
                if ($key == 1) {
                    $fixedCost          = ($seller->fixed_cost ?? $product->value_cost) - 5;
                    $totalCommission    = max($value - $fixedCost, 0);

                    $commissions[] = [
                        'walletId'   => env('WALLET_EXPRESS'),
                        'fixedValue' => $fixedCost,
                    ];
    
                    $sponsor = $seller->sponsor;
                    if ($sponsor && $seller->type !== 99) {
                        $sponsorCommission = max($fixedCost - $sponsor->fixed_cost, 0);
                        if ($sponsorCommission > 0) {
                            $commissions[] = [
                                'walletId'   => $sponsor->token_wallet,
                                'fixedValue' => $sponsorCommission,
                            ];
                        }
                    }
    
                    if ($totalCommission > 0 && $seller->type !== 99) {
                        $commissions[] = [
                            'walletId'   => $seller->token_wallet,
                            'fixedValue' => number_format($totalCommission, 2, '.', ''),
                        ];
                    }
                } else {
                    $percent = $value * 0.05;
                    $totalCommission = ($value - $percent - 5);
                    
                    if ($totalCommission > 0 && $seller->type !== 99) {
                        $commissions[] = [
                            'walletId'   => $seller->token_wallet,
                            'fixedValue' => number_format($totalCommission, 2, '.', ''),
                        ];
                    }
                }
    
                $payment = $assas->createCharge($customer, $paymentMethod, $value, $dueDate, 'Fatura '.$key.' para venda N° '.$sale->id, $commissions);
                if (!$payment || !isset($payment['id'], $payment['invoiceUrl'])) {
                    throw new \Exception("Erro ao gera dados de pagamento para nova venda na parcela {$key}");
                }
    
                $invoice = new Invoice();
                $invoice->uuid                = str::uuid();
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
            return [
                'uuid' => $sale->uuid,
            ];
    
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Erro ao gera dados de pagamento para nova venda: ' . $e->getMessage());
            return false;
        }
    }

    public function listSale(Request $request) {
        
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

        $invoices = Invoice::where('sale_id', $sale->id)->get();
        foreach ($invoices as $invoice) {
           
            $assasController = new AssasController();
            if($invoice->status <> 1) {
                $assasController->cancelCharge($invoice->payment_token);
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

    private function formatValue($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
