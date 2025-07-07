<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller {
    
    public function profile() {
        return view('app.User.profile');
    }

    public function created(Request $request) {

        $user                   = new User();
        $user->uuid             = Str::uuid();
        $user->sponsor_id       = Auth::user()->id;
        $user->association_id   = $request->association_id ??  Auth::user()->association_id;
        $user->name             = $request->name;
        $user->email            = $request->email;
        $user->fixed_cost       = $request->fixed_cost >= Auth::user()->fixed_cost ? $this->formatValue($request->fixed_cost) : Auth::user()->fixed_cost;
        $user->cpfcnpj          = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->password         = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->type             = $request->type;
        if ($user->save()) {
            return redirect()->back()->with('success', 'Usuário cadastrado com sucesso! Senha inicial será o CPF/CNPJ associado.'); 
        }

        return redirect()->back()->with('error', 'Não foi possível cadastrar o Usuário, verifique os dados e tente novamente!'); 
    }

    public function updateProfile(Request $request) {

        $user = User::where('id', $request->id)->first();
        if (!$user) {
            return redirect()->back()->with('info', 'Ops! Não foi possível acessar o seu perfil, tente novamente mais tarde!'); 
        }

        if (!empty($request->name)) {
            $user->name = $request->name;
        }

        if (!empty($request->cpfcnpj && $request->cpfcnpj !== $user->cpfcnpj)) {
            $user->cpfcnpj = preg_replace('/\D/', '', $request->cpfcnpj);
        }

        if (!empty($request->phone)) {
            $user->phone = $request->phone;
        }

        if ($request->email && $request->email != $user->email) {
            $user->email = $request->email;
        }

        if (!empty($request->birth_date)) {
            $user->birth_date = $request->birth_date;
        }

        if (!empty($request->postal_code)) {
            $user->postal_code = $request->postal_code;
        }

        if (!empty($request->address)) {
            $user->address = $request->address;
        }

        if (!empty($request->state)) {
            $user->state = $request->state;
        }

        if (!empty($request->city)) {
            $user->city = $request->city;
        }

        if (!empty($request->complement)) {
            $user->complement = $request->complement;
        }

        if (!empty($request->num)) {
            $user->num = $request->num;
        }

        if (!empty($request->fixed_cost)) {

            if ($this->formatValue($request->fixed_cost) < Auth::user()->fixed_cost) {
                return redirect()->back()->with('info', 'O valor Mín para custo é: R$ ' .Auth::user()->fixed_cost);
            }

            $user->fixed_cost = $this->formatValue($request->fixed_cost);
        }

        if (!empty($request->fixed_cost)) {

            if (($this->formatValue($request->fixed_cost) < Auth::user()->fixed_cost) && Auth::user()->type !== 1) {
                return redirect()->back()->with('info', 'O valor mín para custo é R$ '.Auth::user()->fixed_cost);
            }

            $user->fixed_cost = $this->formatValue($request->fixed_cost);
        }
        
        if (!empty($request->level)) {
            $user->level = $request->level;
        }

        if (!empty($request->password)) {

            if ($request->password !== $request->confirmpassword) {
                return redirect()->back()->with('info', 'Senhas não coincidem!');
            }

            $user->password = bcrypt($request->password);
        }

        if (!empty($request->type)) {
            $user->type = $request->type;
        }

        if (!empty($request->company_name)) {
            $user->company_name = $request->company_name;
        }

        if (!empty($request->company_cpfcnpj)) {
            $user->company_cpfcnpj = $request->company_cpfcnpj;
        }

        if (!empty($request->company_phone)) {
            $user->company_phone = $request->company_phone;
        }

        if (!empty($request->company_address)) {
            $user->company_address = $request->company_address;
        }

        if (!empty($request->company_email)) {
            $user->company_email = $request->company_email;
        }

        if (!empty($request->photo)) {

            if ($user->photo) {
                Storage::delete('public/' . $user->photo);
            }

            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->photo = $path;
        }

        if ($user->save()) {
            return redirect()->back()->with('success', 'Dados atualizados com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function deleteUser(Request $request) {

        $user = User::find($request->id);
        if ($user && $user->delete()) {
            return redirect()->back()->with('success', 'Usuário excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function listuser(Request $request, $type) {

        $query = User::query()->orderBy('name', 'asc')->where('type', $type);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', $request->email);
        }

        if ($request->filled('cpfcnpj')) {
            $query->where('cpfcnpj', $request->cpfcnpj);
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        return view('app.User.list-users', [
            'users'     => $query->paginate(30), 
            'type'      => $type,
        ]);
    }

    public function listNetwork(Request $request) {

        $query = User::query()->orderBy('name', 'asc')->where('sponsor_id', Auth::id())->where('type', '!=', 3);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', $request->email);
        }

        if ($request->filled('cpfcnpj')) {
            $query->where('cpfcnpj', $request->cpfcnpj);
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        return view('app.User.list-network', [
            'users' => $query->paginate(30),
        ]);
    }

    public function listClient(Request $request) {

        $query = User::orderBy('name', 'desc')->where('sponsor_id', Auth::id())->where('type', 3);

        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', $request->email);
        }

        if ($request->filled('cpfcnpj')) {
            $query->where('cpfcnpj', $request->cpfcnpj);
        }

        if (!empty($request->created_at)) {
            $query->where('created_at', $request->created_at);
        }

        return view('app.User.list-clients', [
            'users' => $query->paginate(30),
        ]);
    }

    public function listActive($status = null) {
        
        $users = User::query()->whereIn('type', [2, 99])->where('status', $status)->orderBy('name', 'asc');

        $invoices = Invoice::where('type', 1)->where('status', $status)
            ->whereYear('created_at', Carbon::now()->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = array_fill(1, 12, 0);
        foreach ($invoices as $month => $total) {
            $months[$month] = $total;
        }
        
        return view('app.User.list-actives', [
            'users'         => $users->paginate(30),
            'invoicesData'  => array_values($months)
        ]);
    }
    
    private function formatValue($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
