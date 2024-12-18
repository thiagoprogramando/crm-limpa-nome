<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
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

        return view('app.Sale.upload', ['product' => $product]);
    }

    public function createSale(Request $request) {

        $client = $this->createUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, $request->postal_code, $request->address, $request->complement, $request->city, $request->state, $request->num);
        if (!$client) {
            return redirect()->back()->with('error', 'Erro ao cadastrar o cliente, verifique os dados e tente novamente');
        }

        $product = Product::where('id', $request->product)->first();
        if(!$product) {
            return redirect()->back()->with('error', 'Produto não disponível!');
        }

        $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
        if(!$list) {
            return redirect()->back()->with('info', 'Não há lista disponível para associar o CPF/CNPJ!');
        }

        DB::beginTransaction();
        try {

            $sale               = new Sale();
            $sale->id_client    = $client->id;
            $sale->id_product   = $product->id;
            $sale->id_list      = $list->id;
            $sale->id_seller    = $request->id_seller ?? Auth::id();
            $sale->payment      = $request->wallet_off ? 'CARTEIRA VIP' : 'OUTRO MÉTODO';
            $sale->installments = 1;
            $sale->status       = 0;
            $sale->value        = $this->formatarValor($request->value);
            $sale->save();

            DB::commit();
            return redirect()->route('manager-sale')->with('success', 'Venda criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('manager-sale')->with('error', 'Erro ao criar a venda. Tente novamente.');
        }
    }

    private function createUser($name, $email, $cpfcnpj, $birth_date = null, $phone = null, $postal_code = null, $address = null, $complement = null, $city = null, $state = null, $num = null) {

        $user = User::where('cpfcnpj', str_replace(['.', '-'], '', $cpfcnpj))->orWhere('email', $email)->first();
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
