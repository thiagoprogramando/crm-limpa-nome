<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Coupon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use GuzzleHttp\Client;

class SaleController extends Controller {

    public function create($id) {

        $product = Product::find($id);
        $payments = Payment::where('id_product', $product->id)->get();

        return view('app.Sale.create', [
            'product' => $product, 
            'payments' => $payments
        ]);
    }

    public function createSale(Request $request) {

        $user = $this->createUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, $request->postal_code, $request->address, $request->complement, $request->city, $request->state, $request->num, $request->id_seller);
        if ($user != false) {

            $product = Product::where('id', $request->product)->first();
            if (!$product) {
                return redirect()->back()->with('error', 'Produto não disponível!');
            }

            $seller = User::find($request->id_seller);
            if (!$seller) {
                return redirect()->back()->with('error', 'Consultor de Vendas não localizado no sistema!');
            }

            if (($seller && $seller->fixed_cost > 0) && ($this->formatarValor($request->value) < $seller->fixed_cost)) {
                return redirect()->back()->with('error', 'O valor mín de entrada é: R$ '.$seller->fixed_cost.'!');
            }

            if (empty($seller->fixed_cost)) {
                if ($this->formatarValor($request->value) < $product->value_min) {
                    return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$product->value_min.'!');
                }
            }

            if ($this->formatarValor($request->value) > $product->value_max && $product->value_max > 0) {
                return redirect()->back()->with('error', 'O valor max de venda é: R$ '.$product->value_max.'!');
            }

            $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
            if (!$list) {
                return redirect()->back()->with('error', 'Não há uma lista disponível para vendas!');
            }

            $productCost = ($seller->fixed_cost > 0 ? $seller->fixed_cost : $product->value_cost);
            $commission  = (($this->formatarValor($request->value) - $productCost) - $product->value_rate);

            if ($seller->filiate <> null) {
                $commissionFiliate = max(($seller->fixed_cost - $seller->parent->fixed_cost), 0);
            } else {
                $commissionFiliate = 0;
            }          
    
            $sale = new Sale();
            $sale->id_client    = $user->id;
            $sale->id_product   = $request->product;
            $sale->id_list      = $list->id;
            $sale->id_seller    = !empty($request->id_seller) ? $request->id_seller : Auth::id();

            $sale->payment          = $request->payment;
            $sale->installments     = max(1, $request->installments);
            $sale->status_contract  = 3;
            $sale->status           = 0;

            $sale->value              = $this->formatarValor($request->value);
            $sale->value_total        = $this->formatarValor($request->value_total);
            $sale->commission         = max($commission, 0);
            $sale->commission_filiate = $commissionFiliate;
            $sale->type               = 1;
            if ($sale->save()) {

                $assas = new AssasController();
                $invoice = $assas->createSalePayment($sale->id, true, $request->dueDate);
                if ($invoice) {
                    return redirect()->route('update-sale', ['id' => $sale->id])->with('success', 'Sucesso! Os dados de pagamento foram enviados para o cliente!');
                }

                $sale->delete();
                return redirect()->back()->with('info', 'Verifique os dados e tente novamente!');
            }

            return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }
    }

    private function createUser($name, $email, $cpfcnpj, $birth_date, $phone, $postal_code = null, $address = null, $complement = null, $city = null, $state = null, $num = null, $filiate = null) {
        
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

    public function manager(Request $request) {
        
        $query = Sale::orderBy('created_at', 'desc');
    
        $currentUser = Auth::user();
    
        $affiliateIds = User::where('filiate', $currentUser->id)->pluck('id')->toArray();
        $accessibleUserIds = array_merge([$currentUser->id], $affiliateIds);
    
        if (Auth::user()->type == 1) {
            if (!empty($request->id_seller)) {
                $query->where('id_seller', $request->id_seller);
            }
        } else {
            if (!empty($request->id_seller)) {
                $query->where('id_seller', $request->id_seller);
            } else {
                $query->whereIn('id_seller', $accessibleUserIds);
            }
        }

        if (!empty($request->name)) {
            $users = User::where('name', 'LIKE', '%' . $request->name . '%')->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('id_client', $users);
            }
        }
    
        if (!empty($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }
    
        if (!empty($request->value) && $this->formatarValor($request->value) > 0) {
            $query->where('value', $this->formatarValor($request->value));
        }
    
        if (!empty($request->id_list)) {
            $query->where('id_list', $request->id_list);
        }
    
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
    
        if (!empty($request->label)) {
            $query->where('label', $request->label);
        }
    
        $sales      = $query->paginate(100);
        $sellers    = $currentUser->type == 1 
                        ? User::whereIn('type', [1, 2, 4, 5])->orderBy('name', 'asc')->get() 
                        : User::where('type', [2])->where('filiate', $currentUser->id)->orderBy('name', 'asc')->get();
        $lists      = Lists::orderBy('created_at', 'desc')->get();

        return view('app.Sale.manager', [
            'sales'     => $sales,
            'lists'     => $lists,
            'sellers'   => $sellers
        ]);
    }    

    public function viewSale($id) {

        $sale       = Sale::find($id);
        $invoices   = Invoice::where('id_sale', $sale->id)->orWhere('token_payment', $sale->token_payment)->get();
        $users      = User::whereIn('type', [1, 2, 5])->orderBy('name', 'asc')->get();
        $lists = Lists::orderBy('created_at', 'desc')->get();

        return view('app.Sale.view', [
            'sale'      => $sale, 
            'invoices'  => $invoices,
            'users'     => $users,
            'lists'     => $lists,
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
        
        if($sale->save()) {
            return redirect()->back()->with('success', 'Dados alterados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível alterar os dados da venda!');
    }

    public function deleteSale(Request $request) {

        $sale = Sale::find($request->id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Não encontramos dados da venda!');
        }

        $invoices = Invoice::where('id_sale', $sale->id)->get();
        foreach ($invoices as $invoice) {
           
            $assasController = new AssasController();
            if($invoice->status <> 1) {
                $assasController->cancelInvoice($invoice->token_payment);
            }
            
            $invoice->delete();
        }

        if ($sale->user->type == 3) {
            $user = $sale->user;
            $user->delete();
        }

        if ($sale->delete()) {
            return redirect()->back()->with('success', 'Venda e Faturas excluídas com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível excluir a venda!');
    }

    public function default(Request $request) {
        
        $user       = Auth::user();
        $id_seller  = $request->input('id_seller');
        $id_list    = $request->input('id_list');
        $name       = $request->input('name');
    
        $query = Invoice::query();
    
        if ($user->type == 1) {
            $query->where('due_date', '<', now())->where('status', 0);
        } else {
            $query->whereHas('sale', function ($query) use ($user) {
                $query->where('id_seller', $user->id);
            })->where('due_date', '<', now())->where('status', 0);
        }
    
        if ($id_seller) {
            $query->whereHas('sale', function ($query) use ($id_seller) {
                $query->where('id_seller', $id_seller);
            });
        }
    
        if ($id_list) {
            $query->whereHas('sale', function ($query) use ($id_list) {
                $query->where('id_list', $id_list);
            });
        }
    
        if ($name) {
            $query->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            });
        }
    
        $invoices = $query->paginate(100);
    
        return view('app.Sale.default', [
            'invoices' => $invoices,
            'lists'    => Lists::orderBy('created_at', 'desc')->get(),
            'sellers'  => User::whereIn('type', [1, 2, 4, 5])->orderBy('name', 'asc')->get()
        ]);
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

        $invoices = Invoice::where('id_sale', $sale->id)->get();
        $tomorrow = now()->addDay();
        foreach ($invoices as $invoice) {
            if ($invoice->due_date <= $tomorrow && $invoice->status == 0) {
                return redirect()->back()->with('error', 'Existem faturas vencidas associadas a Venda!');
            }
        }
        
        $sale->id_list = $sale->label === 'REPROTOCOLADO' 
            ? $sale->id_list 
            : $list->id;
        $sale->label   = $sale->label === 'REPROTOCOLADO - ' . now()->format('d/m/Y') 
            ? null 
            : 'REPROTOCOLADO - ' . now()->format('d/m/Y');

        if ($sale->save()) {

            if ($sale->label === 'REPROTOCOLADO') {
                $clientName     = $sale->user->name;
                $phone          = $sale->user->phone;
                $sellerApiToken = $sale->seller->api_token_zapapi;
            
                $message = "*Assunto: Reprotocolamento de Processo Judicial*\r\n\r\n" .
                           "{$clientName},\r\n\r\n" .
                           "Gostaríamos de informar que o *seu processo* foi *reprotocolado com sucesso.*\r\n\r\n" .
                           "A partir de agora, será necessário *aguardar o prazo estimado de 20 a 30 dias*, " .
                           "conforme estipulado pelos trâmites judiciais, para a análise e andamento do seu caso.\r\n\r\n" .
                           "Estamos acompanhando de perto o andamento do processo e *entraremos em contato assim que houver novidades.*\r\n\r\n" .
                           "Agradecemos sua paciência e estamos à disposição para esclarecer qualquer dúvida.";
            
                $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sellerApiToken);
            } else {
                $clientName     = $sale->user->name;
                $phone          = $sale->user->phone;
                $sellerApiToken = $sale->seller->api_token_zapapi;
            
                $message = "*Assunto: Conclusão do Processo Judicial*\r\n\r\n" .
                           "{$clientName},\r\n\r\n" .
                           "É com satisfação que informamos que o *seu processo foi concluído com sucesso!*\r\n\r\n" .
                           "Agradecemos pela confiança em nosso trabalho.";
            
                $this->sendWhatsapp(env('APP_URL') . 'login-cliente', $message, $phone, $sellerApiToken);
            }            

            return redirect()->back()->with('success', 'Venda alterada com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível localizar os dados da Venda!');
    }

    public function deleteInvoice($id) {

        $invoice = Invoice::find($id);
        if(!$invoice) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Fatura!');
        }

        $assasController = new AssasController();
        if($invoice->status <> 1) {
            $cancelInvoice = $assasController->cancelInvoice($invoice->token_payment);

            if($cancelInvoice && $invoice->delete()) {
                return redirect()->back()->with('success', 'Fatura excluída com sucesso!');
            }
        }

        return redirect()->back()->with('error', 'Não é possível excluir uma Fatura já conciliada!');
    }

    public function createInvoice(Request $request) {

        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->back()->with('info', 'Produto indisponível!');
        }

        $sale = Sale::find($request->sale_id);
        if (!$sale) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Venda!');
        }

        if (Auth::user()->type !== 1) {
            $wallet     = $sale->seller->wallet;
            $commission = $request->value;
        } else {
            $wallet     = $request->wallet;
            $commission = $request->commission;
        }

        if (!empty($request->wallet) && $this->formatarValor($request->commission) <= 0) {
            return redirect()->back()->with('info', 'Informe um valor de comissão!');
        }

        $assasController = new AssasController();

        if (empty($sale->user->customer)) {
            $customer = $assasController->createCustomer($sale->user->name, $sale->user->cpfcnpj, $sale->user->phone, $sale->user->email);
        } else {
            $customer = $sale->user->customer;
        }
        
        $assasInvoice = $assasController->createCharge($customer, $request->billingType, $this->formatarValor($request->value), 'Fatura para venda N° '.$sale->id, $request->due_date, 1, $wallet, $this->formatarValor($commission));
        if ($assasInvoice <> false) {

            $invoice                = new Invoice();
            $invoice->id_user       = $sale->id_client;
            $invoice->id_product    = $product->id;
            $invoice->id_sale       = $sale->id;
            $invoice->name          = 'Fatura para venda N° '.$sale->id;
            $invoice->description   = 'Fatura para venda N° '.$sale->id;
            $invoice->token_payment = $assasInvoice['id'];
            $invoice->url_payment   = $assasInvoice['invoiceUrl'];
            $invoice->due_date      = $request->due_date;
            $invoice->value         = $this->formatarValor($request->value);
            $invoice->commission    = $this->formatarValor($request->commission);
            $invoice->status        = 0;
            $invoice->num           = 2;
            $invoice->type          = 3;
            if($invoice->save()) {
                return redirect()->back()->with('success', 'Fatura adicionada com sucesso!');
            }

            return redirect()->back()->with('info', 'Não foi possível adicionar Fatura, verifique os dados e tentar novamente!');
        }
            
        return redirect()->back()->with('info', 'Não foi possível adicionar Fatura, verifique os dados e tentar novamente!');
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

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
