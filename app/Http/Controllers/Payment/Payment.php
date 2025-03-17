<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Payment extends Controller {
    
    public function payments(Request $request) {

        $query = Invoice::where('id_user', Auth::id());
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
    
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('due_date', '>=', $request->start_date);
        }
    
        if ($request->has('finish_date') && !empty($request->finish_date)) {
            $query->whereDate('due_date', '<=', $request->finish_date);
        }
    
        $payments = $query->orderBy('status', 'asc')->paginate(10);

        return view('app.Payment.payment', [
            'payments' => $payments,
        ]);
    }    

    public function receivable(Request $request) {

        $assas       = new AssasController();
        $offset      = $request->offset ?? 0;
        $receivables = $assas->receivable($request->start_date, $request->finish_date, $offset);

        return view('app.Payment.receivable', [
            'receivables' => $receivables['data'],
            'hasMore'     => $receivables['hasMore'],
            'offset'      => $receivables['offset']
        ]);
    }

}
