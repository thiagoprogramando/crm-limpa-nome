<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LinkController extends Controller {

    public function index(Request $request) {

        $query      = Link::orderBy('created_at', 'desc');
        $sellers    = User::whereIn('type', [1, 2, 4, 5])->orderBy('name', 'asc')->get(); 
        $products   = Product::where('status', 1)->orderBy('name', 'asc')->get();      

        return view('app.Link.index', [
            'links'     => $query->paginate(30),
            'sellers'   => $sellers,
            'products'  => $products
        ]);
    }
    
    public function store(Request $request) {

        $link = new Link();
        $link->uuid                 = Str::uuid();
        $link->title                = $request->title ?? null;
        $link->description          = $request->description ?? null;
        $link->value                = $this->formatValue($request->value);
        $link->product_id           = $request->product_id ?? null;
        $link->user_id              = $request->user_id ?? Auth::user()->id;
        $link->payment_method       = $request->payment_method;
        $link->payment_installments = $request->payment_installments;
        
        $installments = [];
        if ($request->has('installments') && is_array($request->installments)) {
            foreach ($request->installments as $i => $data) {
                $installments[$i] = [
                    'value'    => isset($data['value']) ? $this->formatValue($data['value']) : null,
                    'due_date' => $data['due_date'] ?? null,
                ];
            }
        }

        $link->payment_json_installments = $installments;
        if ($link->save()) {
            return redirect()->back()->with('success', 'Link criado com sucesso!');
        }

        return redirect()->back()->with('error', 'Verifique os dados e tente novamente!');
    }

    public function destroy($uuid) {

        $link = Link::where('uuid', $uuid)->first();
        if ($link && $link->delete()) {
            return redirect()->back()->with('success', 'Link removido com sucesso!');
        }

        return redirect()->back()->with('error', 'Link não encontrado!');
    }

    private function formatValue($valor) {

        if ($valor === null) {
            return 0.00;
        }

        $valor = preg_replace('/[^0-9\.,]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return number_format((float) $valor, 2, '.', '');
    }
}
