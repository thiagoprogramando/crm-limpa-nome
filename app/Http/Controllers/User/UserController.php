<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

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
    
        $user = User::where('id', Auth::id())->update($data);
        if ($user) {
            return redirect()->back()->with('success', 'Dados atualizados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

}
