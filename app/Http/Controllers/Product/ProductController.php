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
        return view('app.Product.create');
    }

    public function productCreate(Request $request) {

        $product                = new Product();
        $product->name          = $request->name;
        $product->description   = $request->description;
        $product->terms_text    = $request->terms_text;

        $product->address           = $request->has('address') ? 1 : 0;
        $product->createuser        = $request->has('createuser') ? 1 : 0;
        $product->terms             = $request->has('terms') ? 1 : 0;
        $product->level             = $request->level;
        $product->contract          = $request->contract;
        $product->contract_subject  = $request->contract_subject;
        $product->active            = $request->active;

        $product->value_cost    = $this->formatarValor($request->value_cost);
        $product->value_rate    = $this->formatarValor($request->value_rate);
        $product->value_min     = $this->formatarValor($request->value_min);
        $product->value_max     = $this->formatarValor($request->value_max);

        if($product->save()) {
            return redirect()->route('updateproduct', ['id' => $product->id])->with('success', 'Produto criado com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function productDetails($id) {

        $product = Product::find($id);
        if($product) {
            return view('app.Product.update', [
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
            if($request->terms_text) {
                $product->terms_text = $request->terms_text;
            }
            
            $product->address       = $request->has('address') ? 1 : 0;
            $product->createuser    = $request->has('createuser') ? 1 : 0;
            $product->terms         = $request->has('terms') ? 1 : 0;
            
            $product->level             = $request->level;
            $product->contract          = $request->contract ?? '';
            $product->contract_subject  = $request->contract_subject ?? '';
            $product->active            = $request->active;
            $product->value_cost        = $this->formatarValor($request->value_cost) ?? 0;
            $product->value_rate        = $this->formatarValor($request->value_rate) ?? 0;
            $product->value_min         = $this->formatarValor($request->value_min) ?? 0;
            $product->value_max         = $this->formatarValor($request->value_max) ?? 0;
    
            if($product->save()) {
                return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
            }    
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function delete(Request $request) {

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
