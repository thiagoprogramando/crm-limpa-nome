<?php

namespace App\Http\Controllers\Trash;

use App\Http\Controllers\Controller;

use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;

class RecoverController extends Controller {
    
    public function recoverSale(Request $request) {
        
        $sale = Sale::onlyTrashed()->find($request->id);
        if (!$sale) {
            return redirect()->back()->with('error', 'Venda não encontrada ou já restaurada!');
        }
    
        $sale->restore();
    
        return redirect()->route('list-sales')->with('success', 'Venda recuperada com sucesso!');
    }
    
    public function recoverUser(Request $request) {
        
        $user = User::onlyTrashed()->find($request->id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado ou já restaurado!');
        }
    
        $user->restore();
    
        return redirect()->back()->with('success', 'Usuário recuperado com sucesso!');
    }
}
