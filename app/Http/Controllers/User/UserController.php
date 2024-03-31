<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {
    
    public function profile() {

        $documents = new AssasController();
        $mydocuments = $documents->myDocuments();

        return view('app.User.profile', ['mydocuments' => $mydocuments]);
    }

    public function updateProfile(Request $request) {

        $data = [   
            'name'          => $request->name,
            'cpfcnpj'       => $request->cpfcnpj,
            'phone'         => $request->phone,
            'email'         => $request->email,
            'birth_date'    => $request->birth_date,
            'postal_code'   => $request->postal_code,
            'address'       => $request->address,
            'state'         => $request->state,
            'city'          => $request->city,
            'complement'    => $request->complement,
            'num'           => $request->num,
        ];

        if (
            $request->filled('name') &&
            $request->filled('cpfcnpj') &&
            $request->filled('phone') &&
            $request->filled('email') &&
            $request->filled('birth_date') &&
            $request->filled('postal_code') &&
            $request->filled('address') &&
            $request->filled('state') &&
            $request->filled('city') &&
            $request->filled('complement') &&
            $request->filled('num')
        ) {
            $data['status'] = 3;
        }
    
        $user = User::where('id', Auth::id())->update($data);
        if ($user) {
            return redirect()->back()->with('success', 'Dados atualizados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function search(Request $request) {

        $sales      = Sale::where('id', 'like', '%' . $request->search . '%')->orWhereHas('user', function ($query) use ($request) {$query->where('name', 'like', '%' . $request->search . '%');})->get();
        $invoices   = Invoice::where('name', 'like', '%' . $request->search . '%')->orWhere('id', 'like', '%' . $request->search . '%')->where('id_user', Auth::id())->get();
        $lists      = Lists::where('name', 'like', '%' . $request->search . '%')->orWhere('id', 'like', '%' . $request->search . '%')->get();

        return view('app.User.search', [
            'search'    => $request->search,
            'sales'     => $sales,
            'invoices'  => $invoices,
            'lists'     => $lists
        ]);
    }
}
