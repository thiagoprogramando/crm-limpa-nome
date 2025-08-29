<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Notification;
use App\Models\Sale;
use App\Models\User;

use Carbon\Carbon;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller {
    
    public function profile() {

        if (Auth::user()->status == 1 ) {
            return view('app.User.profile');
        }

        $documents = new AssasController();
        $mydocuments = $documents->myDocuments();
        return view('app.User.profile', [
            'mydocuments' => $mydocuments
        ]);
    }

    public function update(Request $request) {

        $user = User::where('id', $request->id)->first();

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
            $user->email = preg_replace('/[^\w\d\.\@\-\_]/', '', $request->email);
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

        if (!empty($request->token_whatsapp)) {
            $user->token_whatsapp = $request->token_whatsapp;
        }

        if (!empty($request->white_label_network)) {
            $user->white_label_network = $request->white_label_network;
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

        if (!empty($request->num)) {
            $user->num = $request->num;
        }

        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);
            $this->alertPassword($request->id, true);
        }

        if (!empty($request->type)) {
            $user->type = $request->type;
        }

        if (!empty($request->white_label_contract)) {
            $user->white_label_contract = $request->white_label_contract;
        }

        if (!empty($request->company_name)) {
            $user->company_name = $request->company_name;
        }

        if (!empty($request->company_cpfcnpj)) {
            $user->company_cpfcnpj = $request->company_cpfcnpj;
        }

        if (!empty($request->company_address)) {
            $user->company_address = $request->company_address;
        }

        if (!empty($request->company_email)) {
            $user->company_email = $request->company_email;
        }

        if (!empty($request->api_key)) {
            $status = $this->accountStatus($request->api_key);
            if (is_array($status) && (isset($status['general']) && ($status['general'] == 'APPROVED' || $status['general'] == 'AWAITING_APPROVA'))) {
                $user->api_key = $request->api_key;
            } else {
                return redirect()->back()->with('info', 'Tokens não válidados! Aguarde aprovação da sua carteira/ou entre em contato com o suporte.');
            }
        }

        if (!empty($request->wallet)) {
            $user->wallet = $request->wallet;
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

    private function accountStatus($api_key) {

        $assas = new AssasController();
        $accountStatus = $assas->accountStatus($api_key);

        return $accountStatus;
    }

    private function alertPassword($id) {

        $user = User::find($id);
        
        $message =  "Olá, {$user->name},\r\n\r\n"
                        . "Foi feito uma redefinição de senha na sua conta! \r\n\r\n"
                        . "*Caso não reconheça essa ação, entre em contato com nosso suporte imediatamente.*\r\n\r\n"
                        . "Acesse: ".env('APP_URL')."\r\n"
                        . "Faça login com seu *E-mail* e *Senha* (Caso tenha solicitado via suporte sua senha será CPF/CNPJ informado no cadastro). \r\n\r\n"
                        . "Precisa de ajuda? Estamos aqui para você!\r\n\r\n";
            $this->sendWhatsapp(
                env('APP_URL'),
                $message,
                $user->phone,
                $user->token_whatsapp
            );

            return redirect()->back()->with('success', 'mensagem enviada com sucesso!');
    }

    public function search(Request $request) {

        $sales = Sale::where('id_seller', Auth::user()->id)
            ->where(function ($query) use ($request) {
                $query->where('id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    });
            })
            ->get();

        $invoices = Invoice::where('id_user', Auth::id())
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('id', 'like', '%' . $request->search . '%');
            })
            ->get();
        
        return view('app.User.search', [
            'search'    => $request->search,
            'sales'     => $sales,
            'invoices'  => $invoices
        ]);
    }

    public function listuser(Request $request, $type) {

        $query = User::orderBy('name', 'desc');

        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if (!empty($request->email)) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if (!empty($request->cpfcnpj)) {
            $query->where('cpfcnpj', $request->cpfcnpj);
        }

        if (!empty($request->created_at_start)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->created_at_start)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }
        
        if (!empty($request->created_at_end)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->created_at_end)->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        return view('app.User.list-users', [
            'users'     => $query->where('type', $type)->paginate(30),
            'type'      => $type,
        ]);
    }

    public function listNetwork(Request $request) {

        $query = User::query()
            ->orderBy('name', 'asc')
            ->where('filiate', Auth::id())
            ->where('type', '!=', 3);

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

        $usersForRanking = User::where('filiate', Auth::user()->id)->whereIn('type', [2, 5, 6, 7])->get();
    
        $sortedUsers = $usersForRanking->sortByDesc(function($user) {
            return $user->saleTotal();
        });
    
        $usersForRanking = $sortedUsers->take(10);

        return view('app.User.list-network', [
            'users'             => $query->paginate(30),
            'usersForRanking'   => $usersForRanking
        ]);
    }

    public function listClient(Request $request) {

        $query = User::orderBy('name', 'desc')->where('filiate', Auth::id())->where('type', '3');

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

        return view('app.User.list-client', [
            'users' => $query->paginate(30)
        ]);
    }

    public function destroy(Request $request) {

        $user = User::find($request->id);
        if ($user && $user->delete()) {
            return redirect()->back()->with('success', 'Usuário excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function viewNotification($id) {

        $notification = Notification::find($id);
        if ($notification && $notification->delete()) {
            return redirect()->back();
        }

        return redirect()->back()->with('error', 'Ops! Notificação não encontrada.');
    }

    private function sendWhatsapp($link, $message, $phone, $token = null) {

        $client = new Client();
        $url = $token ?: 'https://api.z-api.io/instances/3C71DE8B199F70020C478ECF03C1E469/token/DC7D43456F83CCBA2701B78B/send-link';
    
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Client-Token'  => 'Fabe25dbd69e54f34931e1c5f0dda8c5bS',
                ],
                'json' => [
                    'phone'           => '55' . $phone,
                    'message'         => $message,
                    'image'           => env('APP_URL_LOGO'),
                    'linkUrl'         => $link,
                    'title'           => 'Assinatura de Documento',
                    'linkDescription' => 'Link para Assinatura Digital',
                ],
                'verify' => false
            ]);
    
            if ($response->getStatusCode() == 200) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createWallet() {
        
        if (!Auth::check()) {
            return redirect()->back()->with('info', 'Verifique seus dados e tente novamente!');
        }
    
        return view('app.User.create-wallet');
    }

    private function formatValue($value) {
        
        $value = trim((string)$value);
        if ($value === '') {
            return number_format(0, 2, '.', '');
        }

        $value = preg_replace('/[^0-9.,-]/', '', $value);

        $hasComma = strpos($value, ',') !== false;
        $hasDot   = strpos($value, '.') !== false;

        if ($hasComma && $hasDot) {
            
            $lastComma = strrpos($value, ',');
            $lastDot   = strrpos($value, '.');

            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            $parts = explode(',', $value);
            $last  = end($parts);
            if (strlen($last) === 3 && count($parts) > 1) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif ($hasDot) {
            
            $parts = explode('.', $value);
            $last  = end($parts);
            if (strlen($last) === 3 && count($parts) > 1) {
                $value = str_replace('.', '', $value);
            }
        }

        $valueFloat = floatval($value);
        return number_format($valueFloat, 2, '.', '');
    }

}
