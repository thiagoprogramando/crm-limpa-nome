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

        $user = User::where('id', Auth::id())->first();

        if(!empty($request->name)) {
            $user->name = $request->name;
        }

        if(!empty($request->cpfcnpj && $request->cpfcnpj !== $user->cpfcnpj)) {
            $user->cpfcnpj = $request->cpfcnpj;
        }

        if(!empty($request->phone)) {
            $user->phone = $request->phone;
        }

        if(!empty($request->email) && $request->email !== $user->email) {
            $user->email = $request->email;
        }

        if(!empty($request->birth_date)) {
            $user->birth_date = $request->birth_date;
        }

        if(!empty($request->postal_code)) {
            $user->postal_code = $request->postal_code;
        }

        if(!empty($request->address)) {
            $user->address = $request->address;
        }

        if(!empty($request->state)) {
            $user->state = $request->state;
        }

        if(!empty($request->city)) {
            $user->city = $request->city;
        }

        if(!empty($request->complement)) {
            $user->complement = $request->complement;
        }

        if(!empty($request->num)) {
            $user->num = $request->num;
        }

        if(!empty($request->type)) {
            $user->type = $request->type;

            if($request->type == 4) {
                $user->level = 5;
            }
        }

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
            $user->status = 3;
        }

        if ($user->save()) {
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

    public function listuser(Request $request, $type) {

        $query = User::orderBy('name', 'desc');

        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if (!empty($request->city)) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if (!empty($request->email)) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if (!empty($request->cpfcnpj)) {
            $query->where('cpfcnpj', 'like', '%' . $request->cpfcnpj . '%');
        }

        $query->where('type', $type);

        $users = $query->get();

        return view('app.User.list', ['users' => $users]);
    }

    public function deleteUser(Request $request) {

        $user = User::find($request->id);
        if($user) {
            if($user->delete()) {
                return redirect()->back()->with('success', 'Usuário excluído com sucesso!');
            }

            return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }

        return redirect()->back()->with('error', 'Não foram localizados dados do usuário!');
    }
}
