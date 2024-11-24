<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Item;
use App\Models\Payment;
use App\Models\Product;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller {
    
    public function list(Request $request) {

        $products = Product::orderBy('created_at', 'desc')->get();
        return view('app.Product.list', ['products' => $products]);
    }

    public function create() {

        return view('app.Product.create');
    }

    public function createProduct(Request $request) {

        $product                = new Product();
        $product->name          = $request->name;
        $product->description   = $request->description;
        $product->terms_text    = $request->terms_text;

        $product->address       = $request->has('address') ? 1 : 0;
        $product->createuser    = $request->has('createuser') ? 1 : 0;
        $product->terms         = $request->has('terms') ? 1 : 0;
        $product->level         = $request->level;
        $product->contract      = $request->contract;
        $product->active        = $request->active;

        $product->value_cost    = $this->formatarValor($request->value_cost);
        $product->value_rate    = $this->formatarValor($request->value_rate);
        $product->value_min     = $this->formatarValor($request->value_min);
        $product->value_max     = $this->formatarValor($request->value_max);

        if($product->save()) {
            return redirect()->route('updateproduct', ['id' => $product->id])->with('success', 'Produto criado com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function update($id) {

        $product = Product::find($id);
        $payments = Payment::where('id_product', $product->id)->get();
        $itens = Item::where('id_product', $product->id)->get();
        if($product) {
            return view('app.Product.update', [
                'product'   => $product, 
                'payments'  => $payments,
                'itens'     => $itens
            ]);
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Produto não encontrados!');
    }

    public function updateProduct(Request $request) {

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
            
            $product->level         = $request->level;
            $product->contract      = $request->contract ?? '';
            $product->active        = $request->active;
            $product->value_cost    = $this->formatarValor($request->value_cost) ?? 0;
            $product->value_rate    = $this->formatarValor($request->value_rate) ?? 0;
            $product->value_min     = $this->formatarValor($request->value_min) ?? 0;
            $product->value_max     = $this->formatarValor($request->value_max) ?? 0;
    
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

    public function payment(Request $request) {

        $product = Product::find($request->id);
        if($product) {

            $payment                = new Payment();
            $payment->method        = $request->method;
            $payment->installments  = $request->installments;
            $payment->value_rate    = $this->formatarValor($request->value_rate);
            $payment->id_product    = $request->id;

            if($payment->save()) {
                return redirect()->back()->with('success', 'Forma de pagamento incluído com sucesso!');
            }
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Produto não encontrados!');
    }

    public function updatePayment(Request $request) {

        $payment = Payment::find($request->id);
        if($payment) {

            $payment->installments  = $request->installments;
            $payment->value_rate    = $this->formatarValor($request->value_rate);
            if($payment->save()) {
                return redirect()->back()->with('success', 'Dados atualizados com sucesso!');
            }

            return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Método não encontrados!');
    }

    public function deletePayment(Request $request) {

        $payment = Payment::find($request->id);
        if($payment) {

            $payment->delete();
            return redirect()->back()->with('success', 'Registro excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace(['.', ','], '', $valor);

        return number_format(floatval($valor) / 100, 2, '.', '');
    }

    public function createItem(Request $request) {

        $product = Product::find($request->id);
        if($product) {

            $item               = new Item();
            $item->id_product   = $product->id;
            $item->name         = $request->name;
            $item->description  = $request->description;
            $item->type         = $request->type;

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $item->item = $request->file->store('public/item');
            } else {
                $item->item = $request->file;
            }

            if($item->save()) {
                return redirect()->back()->with('success', 'Item incluído com sucesso!');
            }
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados do Produto não encontrados!');
    }

    public function deleteItem(Request $request) {

        $item = Item::find($request->id);
        if($item) {

            if(!empty($item->item) && Storage::exists($item->item)) {
                Storage::delete($item->item);
            }

            $item->delete();
            return redirect()->back()->with('success', 'Registro excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }
}
