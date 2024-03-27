<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller {
    
    public function wallet() {

        $assas = new AssasController();
        $balance = $assas->balance();
        if($balance == 0 || $balance > 0) {
            $statistics = $assas->statistics();
            $extracts    = $assas->receivable();
            $accumulated    = $assas->accumulated();
            return view('app.User.wallet', ['balance' => $balance, 'statistics' => $statistics, 'extracts' => $extracts, 'accumulated' => $accumulated]);
        }
        
        return view('app.User.wallet', ['balance' => 'sem dados!', 'statistics' => 'sem dados!', 'accumulated' => 'sem dados!', 'extracts' => '']);
    }

}
