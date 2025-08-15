<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Item;
use App\Models\Payment;
use App\Models\Product;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller {
    
    public function index() {
        
        $products = Product::orderBy('name')->orderBy('created_at', 'desc')->get();
        return view('app.Product.list-products', [
            'products' => $products
        ]);
    }

    public function form() {
        
        return view('app.Product.create-product');
    }

    public function store(Request $request) {

        $product                = new Product();
        $product->name          = $request->name;
        $product->description   = $request->description;

        $product->request_address      = $request->has('request_address') ? 1 : 0;
        $product->request_selfie       = $request->has('request_selfie') ? 1 : 0;
        $product->request_contact      = $request->has('request_contact') ? 1 : 0;
        $product->request_serasa       = $request->has('request_serasa') ? 1 : 0;
        $product->request_spc          = $request->has('request_spc') ? 1 : 0;
        $product->request_boa_vista    = $request->has('request_boa_vista') ? 1 : 0;
        $product->request_no_document  = $request->has('request_no_document') ? 1 : 0;

        $product->level     = $request->level;
        $product->status    = $request->status;

        $product->request_contract  = $request->has('request_contract') ? 1 : 0;
        $product->contract_subject  = $request->contract_subject;

        $product->value_cost    = $this->formatarValor($request->value_cost);
        $product->value_rate    = $this->formatarValor($request->value_rate);
        $product->value_min     = $this->formatarValor($request->value_min);
        $product->value_max     = $this->formatarValor($request->value_max);

        if($product->save()) {
            return redirect()->route('product', ['id' => $product->id])->with('success', 'Produto criado com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function show($id) {

        $product = Product::find($id);
        if($product) {
            return view('app.Product.view-product', [
                'product'   => $product,
            ]);
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Produto não encontrados!');
    }

    public function update(Request $request) {

        $product = Product::find($request->id);
        if($product) {
            
            $product->name        = $request->name;
            $product->description = $request->description;

            $product->request_address      = $request->has('request_address') ? 1 : 0;
            $product->request_selfie       = $request->has('request_selfie') ? 1 : 0;
            $product->request_contact      = $request->has('request_contact') ? 1 : 0;
            $product->request_serasa       = $request->has('request_serasa') ? 1 : 0;
            $product->request_spc          = $request->has('request_spc') ? 1 : 0;
            $product->request_boa_vista    = $request->has('request_boa_vista') ? 1 : 0;
            $product->request_no_document  = $request->has('request_no_document') ? 1 : 0;

            $product->level  = $request->level;
            $product->status = $request->status;

            $product->request_contract = $request->has('request_contract') ? 1 : 0;
            $product->contract_subject = $request->contract_subject;

            $product->value_cost = $this->formatarValor($request->value_cost);
            $product->value_rate = $this->formatarValor($request->value_rate);
            $product->value_min  = $this->formatarValor($request->value_min);
            $product->value_max  = $this->formatarValor($request->value_max);
    
            if($product->save()) {
                return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
            }    
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function destroy(Request $request) {

        $product = Product::find($request->id);
        if($product) {

            $product->delete();
            return redirect()->back()->with('success', 'Produto excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Produto não encontrados!');
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }
}
