<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use App\Models\Apresentation;
use App\Models\Archive;
use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Notification;
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

        $user = User::where('id', $request->id)->first();

        if(!empty($request->name)) {
            $user->name = $request->name;
        }

        if(!empty($request->cpfcnpj && $request->cpfcnpj !== $user->cpfcnpj)) {
            $user->cpfcnpj = preg_replace('/\D/', '', $request->cpfcnpj);
        }

        if(!empty($request->phone)) {
            $user->phone = $request->phone;
        }

        if($request->email && $request->email != $user->email) {
            $user->email = preg_replace('/[^\w\d\.\@\-\_]/', '', $request->email);
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

        if(!empty($request->api_token_zapapi)) {
            $user->api_token_zapapi = $request->api_token_zapapi;
        }
        
        if(!empty($request->type)) {
            $user->type = $request->type;

            if($request->type == 4) {
                $user->level = 6;
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

        foreach ($users as $user) {
            $user->commission_total = $user->commissionTotal();
        }

        $users = $users->sortByDesc('commission_total');

        return view('app.User.list', ['users' => $users, 'type' => $type]);
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

    public function viewNotification($id) {

        $notification = Notification::find($id);
        if($notification) {
            
            $notification->delete();
            return redirect()->back();
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function myArchive() {

        if(Auth::check() && Auth::user()->type != 1) {
            $archives = Archive::where('id_user', Auth::id())->orderBy('id', 'asc')->get();
        } else {
            $archives = Archive::orderBy('id', 'asc')->get();
        }
        
        $users = User::orderBy('name', 'asc')->get();
        return view('app.User.archives', ['archives' => $archives, 'users' => $users]);
    }

    public function createArchive(Request $request) {

        $archive = new Archive();
        $archive->title   = $request->title;
        $archive->id_user = $request->id_user;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $archive->file = $request->file->store('public/archive');
        } else {
            return redirect()->back()->with('error', 'Não foi possível processar o arquivo, tente novamente mais tarde!');
        }

        if($archive->save()) {
            return redirect()->back()->with('success', 'Arquivo criado com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function deleteArchive(Request $request) {

        $archive = Archive::find($request->id);

        $filePath = storage_path('app/public/' . $archive->file);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if($archive && $archive->delete()) {
            return redirect()->back()->with('success', 'Arquivo excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foram localizados dados do Arquivo!');
    }

    public function apresentation() {

        if(Auth::check() && Auth::user()->type != 1) {
            $archives = Apresentation::where('level', Auth::user()->level)->orWhereNull('level')->orderBy('id', 'asc')->get();
        } else {
            $archives = Apresentation::orderBy('id', 'asc')->get();
        }
        
        $users = User::orderBy('name', 'asc')->get();
        return view('app.User.apresentation', [
                'archives' => $archives, 
                'users' => $users
            ]
        );
    }

    public function createApresentation(Request $request) {

        $apresentation         = new Apresentation();
        $apresentation->title  = $request->title;
        $apresentation->level  = $request->level;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $apresentation->file = $request->file->store('public/apresentation');
        } else {
            return redirect()->back()->with('error', 'Não foi possível processar o arquivo, tente novamente mais tarde!');
        }

        if($apresentation->save()) {
            return redirect()->back()->with('success', 'Material criado com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function deleteApresentation(Request $request) {

        $apresentation = Apresentation::find($request->id);
        $filePath = storage_path('app/public/' . $apresentation->file);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if($apresentation && $apresentation->delete()) {
            return redirect()->back()->with('success', 'Material excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foram localizados dados do Material!');
    }
}
