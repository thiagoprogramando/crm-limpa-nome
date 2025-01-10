<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller {

    public function create($id) {

        $product = Product::find($id);
        $sales = Sale::where('id_seller', Auth::user()->id)->where('status', '!==', 1)->get();

        return view('app.Sale.upload', [
            'product' => $product,
            'sales'   => $sales
        ]);
    }

    public function createSale(Request $request) {

        $client = $this->createUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, $request->postal_code, $request->address, $request->complement, $request->city, $request->state, $request->num);
        if (!$client) {
            return redirect()->back()->with('error', 'Erro ao cadastrar o cliente, verifique os dados e tente novamente');
        }
        
        $seller = User::find($request->id_seller);
        if (!$seller) {
            return redirect()->back()->with('error', 'Dados do CONSULTOR DE VENDAS não localizados no sistema!');
        }

        $product = Product::where('id', $request->product)->first();
        if (!$product) {
            return redirect()->back()->with('error', 'Produto não disponível!');
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if (!$list) {
            return redirect()->back()->with('info', 'Não há lista disponível para associar o CPF/CNPJ!');
        }

        DB::beginTransaction();
        try {

            $sale               = new Sale();
            $sale->id_client    = $client->id;
            $sale->id_product   = $product->id;
            $sale->id_list      = $list->id;
            $sale->id_seller    = $request->id_seller ?? Auth::id();
            $sale->payment      = 'ENVIO MANUAL';
            $sale->installments = 1;
            $sale->status       = 0;
            $sale->value        = $seller->fixed_cost;
            $sale->save();

            DB::commit();
            return redirect()->back()->with('success', 'Nome enviado para a Lista!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao enviar o nome, tente novamente!');
        }
    }

    public function createInvoice($id) {

        $sale = Sale::find($id);
        if (!$sale) {
            return redirect()->back()->with('info', 'Nome não localizado na base de dados!');
        }

        $assas = new AssasController();
        $charge = $assas->createCharge(Auth::user()->customer, 'PIX', Auth::user()->fixed_cost, 'Fatura da venda N°'.$sale->id, now()->addDay(), null, null, null, Auth::user()->parent, max(0, max(0, Auth::user()->fixed_cost - Auth::user()->parent->fixed_cost)));  
        if ($charge == false) {
            return redirect()->back()->with('info', 'Verifique seus dados e tente novamente!');
        }

        $invoice = new Invoice();
        $invoice->id_user               = Auth::user()->id;
        $invoice->id_sale               = $sale->id;
        $invoice->id_product            = $sale->id_product;
        $invoice->name                  = env('APP_NAME').' - Fatura';
        $invoice->description           = 'Fatura da venda N° '.$sale->id;
        $invoice->url_payment           = $charge['invoiceUrl'];
        $invoice->token_payment         = $charge['id'];
        $invoice->value                 = Auth::user()->fixed_cost;
        $invoice->commission            = 0;
        $invoice->commission_filiate    = max(0, Auth::user()->fixed_cost - Auth::user()->parent->fixed_cost);
        $invoice->due_date              = now()->addDay();
        $invoice->num                   = 1;
        $invoice->type                  = 3;
        $invoice->status                = 0;
        $invoice->save();

        $sale->token_payment        = $charge['id'];
        $sale->commission           = 0;
        $sale->commission_filiate   = max(0, Auth::user()->fixed_cost - Auth::user()->parent->fixed_cost);
        if ($sale->save()) {
            return redirect($charge['invoiceUrl']);
        }

        return redirect()->back()->with('info', 'Verifique seus dados e tente novamente!');
    }

    private function createUser($name, $email, $cpfcnpj, $birth_date = null, $phone = null, $postal_code = null, $address = null, $complement = null, $city = null, $state = null, $num = null) {

        $user = User::where('cpfcnpj', str_replace(['.', '-'], '', $cpfcnpj))->first();
        if($user) {
            return $user;
        }
        
        $user               = new User();
        $user->name         = $name;
        $user->email        = preg_replace('/[^\w\d\.\@\-\_]/', '', $email);
        $user->cpfcnpj      = preg_replace('/\D/', '', $cpfcnpj);
        $user->birth_date   = date('Y-m-d', strtotime($birth_date));
        $user->password     = bcrypt(str_replace(['.', '-'], '', $cpfcnpj));
        $user->phone        = $phone;
        $user->postal_code  = $postal_code;
        $user->address      = $address;
        $user->complement   = $complement;
        $user->city         = $city;
        $user->state        = $state;
        $user->num          = $num;
        $user->type         = 3;
        if($user->save()) {
            return $user;
        }

        return false;
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
