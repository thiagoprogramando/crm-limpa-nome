<?php

namespace App\Http\Controllers\WhiteLabel;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller {
    
    public function profileContract() {

        if (Auth::user()->white_label_contract == 1) {
            return view('app.WhiteLabel.profile');
        }

        return redirect()->back()->with('info', 'Sem permissão para acessar o módulo!');
    }
}
