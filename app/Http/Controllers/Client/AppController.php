<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller {
    
    public function app() {

        $sales = Sale::where('client_id', Auth::user()->id)->paginate(10);
        return view('client.app.app', [
            'sales' => $sales
        ]);
    }

    public function invoice($sale = null) {

        if($sale) {
            $invoices = Invoice::where('id_sale', $sale)->paginate(30);
        } else{
            $invoices = Invoice::where('id_user', Auth::user()->id)->orderBy('status', 'asc')->paginate(30);
        }

        return view('client.app.Sale.invoice', [
            'invoices' => $invoices
        ]);
    }

    public function logout() {

        Auth::logout();
        return redirect()->route('login.cliente');
    }
}
