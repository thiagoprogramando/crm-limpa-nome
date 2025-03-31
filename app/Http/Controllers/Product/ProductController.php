<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Product;

use Illuminate\Http\Request;

class ProductController extends Controller {
    
    public function index() {
        
        $products = Product::withCount([
            'sales' => function ($query) {
                $query->whereIn('status', [1, 2]);
            }
        ])
        ->orderByDesc('sales_count')
        ->paginate(30);
    
        return view('app.Product.list-product', [
            'products' => $products
        ]);
    }

    public function productView() {
        return view('app.Product.create-product');
    }

    public function productCreate(Request $request) {

        $product                = new Product();
        $product->name          = $request->name;
        $product->description   = $request->description;

        $product->value_cost    = $this->formatarValor($request->value_cost);
        $product->value_rate    = $this->formatarValor($request->value_rate);
        $product->value_min     = $this->formatarValor($request->value_min);
        $product->value_max     = $this->formatarValor($request->value_max);

        $product->request_photo  = $request->has('request_photo') ? 1 : 0;
        $product->request_document_photo  = $request->has('request_document_photo') ? 1 : 0;
        $product->request_address  = $request->has('request_address') ? 1 : 0;
        
        $product->request_contract = !empty($request->request_contract) ? 1 : 0;
        $product->subject_contract = $request->subject_contract;
        $product->request_terms    = $request->has('request_terms') ? 1 : 0;
        $product->subject_terms    = $request->subject_terms;

        $product->access_level = $request->access_level;
        $product->status       = $request->status;
        if($product->save()) {
            return redirect()->route('update-product', ['id' => $product->id])->with('success', 'Produto criado com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function productDetails($id) {

        $product = Product::find($id);
        if($product) {
            return view('app.Product.update-product', [
                'product'   => $product,
            ]);
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Produto não encontrados!');
    }

    public function productUpdate(Request $request) {

        $product = Product::find($request->id);
        if($product) {
            
            if($request->name) {
                $product->name = $request->name;
            }
            if($request->description) {
                $product->description = $request->description;
            }

            $product->value_cost = $this->formatarValor($request->value_cost) ?? 0;
            $product->value_rate = $this->formatarValor($request->value_rate) ?? 0;
            $product->value_min  = $this->formatarValor($request->value_min) ?? 0;
            $product->value_max  = $this->formatarValor($request->value_max) ?? 0;
            
            $product->request_address        = $request->has('request_address') ? 1 : 0;
            $product->request_photo          = $request->has('request_photo') ? 1 : 0;
            $product->request_document_photo = $request->has('request_document_photo') ? 1 : 0;
            
            $product->request_contract  = $request->has('request_contract') ? 1 : 0;
            $product->subject_contract  = $request->subject_contract;
            $product->request_terms     = $request->has('request_terms') ? 1 : 0;
            $product->subject_terms     = $request->subject_terms;

            $product->access_level = $request->access_level;
            $product->status       = $request->status;
            if($product->save()) {
                return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
            }
            
            return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }

        return redirect()->back()->with('error', 'Produto não localizado na base, verifique os dados e tente novamente!');
    }

    public function productDelete(Request $request) {

        $product = Product::find($request->id);
        if($product && $product->delete()) {
            return redirect()->back()->with('success', 'Produto excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, ente novamente mais tarde!');
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }
}
