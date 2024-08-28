<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Models\Lists;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller {

    public function create($id) {

        $product = Product::find($id);

        return view('app.Sale.upload', ['product' => $product]);
    }

    public function createSale(Request $request) {

        $user = $this->createUser($request->name, $request->email, $request->cpfcnpj, $request->birth_date, $request->phone, $request->postal_code, $request->address, $request->complement, $request->city, $request->state, $request->num);
        if($user != false) {

            $product = Product::where('id', $request->product)->first();
            if(!$product) {
                return redirect()->back()->with('error', 'Produto não disponível!');
            }

            if($this->formatarValor($request->value) < $product->value_min) {
                return redirect()->back()->with('error', 'O valor mín de venda é: R$ '.$product->value_min.'!');
            }

            if($this->formatarValor($request->value) > $product->value_max && $product->value_max > 0) {
                return redirect()->back()->with('error', 'O valor max de venda é: R$ '.$product->value_max.'!');
            }

            $list = Lists::where('start', '<=', Carbon::now())->where('end', '>=', Carbon::now())->first();
            if(!$list) {
                return redirect()->back()->with('error', 'Não há uma lista disponível para vendas!');
            }

            if($request->wallet_off) {

                if($request->id_seller) {
                    $user = User::find($request->id_seller);
                    $walletValue = $user->wallet_off;
                } else {
                    $walletValue = Auth::user()->wallet_off;
                }
                
                if($walletValue < $product->value_cost) {
                    return redirect()->route('wallet')->with('error', 'Ops! Sua carteira não tem saldo suficiente!');
                }
            }

            $sale               = new Sale();
            $sale->id_client    = $user->id;
            $sale->id_product   = $request->product;
            $sale->id_list      = $list->id;
            $sale->id_payment   = 0;
            $sale->id_seller    = !empty($request->id_seller) ? $request->id_seller : Auth::id();
            $sale->payment      = 'CARTEIRA DE INVESTIMENTO';
            $sale->installments = 1;
            $sale->status       = 1;
            $sale->wallet_off   = $request->has('wallet_off') ? 1 : null;
            $sale->value        = $this->formatarValor($request->value);
            
            if($sale->save()) {

                if($request->id_seller) {
                    
                    $user = User::find($request->id_seller);
                    $user->wallet_off -= $product->value_cost;
                } else {
                    
                    $user = User::find(Auth::id());
                    $user->wallet_off -= $product->value_cost;
                }

                if($user->save()) {
                    return redirect()->route('manager-sale')->with('success', 'CPF/CNPJ Associado com sucesso, o valor foi descontado da sua Carteira!');
                }   

                return redirect()->route('manager-sale')->with('error', 'CPF/CNPJ Associado com sucesso!');
            }

            return redirect()->route('manager-sale')->with('error', 'Não foi possível associar o CPF/CNPJ!');
        }
    }

    private function createUser($name, $email, $cpfcnpj, $birth_date, $phone, $postal_code = null, $address = null, $complement = null, $city = null, $state = null, $num = null) {

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
