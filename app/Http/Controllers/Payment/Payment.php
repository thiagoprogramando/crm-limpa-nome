<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Payment extends Controller {
    
    public function payments() {

        $payments = Invoice::where('id_user', Auth::id())->get();
        return view('app.Payment.payment', ['payments' => $payments]);
    }

    public function receivable() {

        $assas = new AssasController();
        $receivables = $assas->receivable();
        return view('app.Payment.receivable', ['receivables' => $receivables]);
    }

}