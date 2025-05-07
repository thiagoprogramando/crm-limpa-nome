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
        return view('app.User.profile');
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

            if (($this->formatarValor($request->fixed_cost) < Auth::user()->fixed_cost) && Auth::user()->type !== 1) {
                return redirect()->back()->with('info', 'O valor mín para custo é R$ '.Auth::user()->fixed_cost);
            }

            $user->fixed_cost = $this->formatarValor($request->fixed_cost);
        }
        
        if (!empty($request->level)) {
            $user->level = $request->level;
        }

        if (!empty($request->password)) {

            if ($request->password !== $request->Confirmpassword) {
                return redirect()->back()->with('info', 'Senhas não coincidem!');
            }

            $user->password = bcrypt($request->password);
            $this->alertPassword($user->id);
        }

        if (!empty($request->type)) {
            $user->type = $request->type;
        }

        if (!empty($request->white_label_network)) {
            $user->white_label_network = $request->white_label_network;
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
                $user->status  = 1;
            } else {
                return redirect()->back()->with('info', 'Tokens não válidados! Aguarde aprovação da sua carteira/ou entre em contato com o suporte.');
            }

            if (!empty($request->wallet)) {
                $user->wallet = $request->wallet;
            }
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
        if (!$user) {
            return false;
        }
        
        $message =  "Olá, {$user->name},\r\n\r\n"
                        . "Foi feito uma redefinição de senha na sua conta! \r\n\r\n"
                        . "*Caso não reconheça essa ação, entre em contato com nosso suporte imediatamente.*\r\n\r\n"
                        . "Acesse: ".env('APP_URL')."\r\n"
                        . "Faça login com seu *E-mail* e *Senha*. \r\n\r\n"
                        . "Precisa de ajuda? Estamos aqui para você!\r\n\r\n";
            $this->sendWhatsapp(
                env('APP_URL'),
                $message,
                $user->phone
            );

            return redirect()->back()->with('success', 'mensagem enviada com sucesso!');
    }

    public function listuser(Request $request, $type) {

        $query = User::query()
            ->where('type', $type)
            ->orderBy('name', 'desc');

        $query->when($request->name, fn($q, $name) => $q->where('name', 'like', "%{$name}%"));
        $query->when($request->email, fn($q, $email) => $q->where('email', 'like', "%{$email}%"));
        $query->when($request->cpfcnpj, fn($q, $cpfcnpj) => $q->where('cpfcnpj', $cpfcnpj));

        $query->when($request->created_at_start, function ($q, $start) {
            $q->where('created_at', '>=', Carbon::parse($start)->startOfDay());
        });

        $query->when($request->created_at_end, function ($q, $end) {
            $q->where('created_at', '<=', Carbon::parse($end)->endOfDay());
        });


        $users = $query->paginate(30);
        $users->setCollection(
            $users->getCollection()->map(function ($user) {
                $user->commission_total = $user->commissionTotal();
                return $user;
            })->sortByDesc('commission_total')
        );

        $users->getCollection()->transform(function ($user) {
            $user->commission_total = $user->commissionTotal();
            return $user;
        });

        $currentYear = now()->year;
        $usersByMonth = User::where('type', $type)
            ->whereYear('created_at', $currentYear)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
        $months = array_replace(array_fill(1, 12, 0), $usersByMonth);

        return view('app.User.list-users', [
            'users'     => $users, 
            'type'      => $type,
            'usersData' => array_values($months)
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

    public function deleteUser(Request $request) {

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

    public function listActive($status = null) {

        if (!in_array($status, [1, 2])) {
            return redirect()->back()->with('error', 'Dados não encontrados para a pesquisa!');
        }

        $dateLimit = Carbon::now()->subDays(30);
        if ($status == 1) {
            $query = User::where('type', 2)->whereHas('invoices', function ($query) use ($dateLimit) {
                $query->where('type', 1)
                      ->where('status', 1)
                      ->whereDate('due_date', '>=', $dateLimit);
            });
        } elseif ($status == 2) {
            $query = User::where('type', 2)->where(function ($query) use ($dateLimit) {
                $query->whereDoesntHave('invoices', function ($subQuery) {
                    $subQuery->where('type', 1);
                })
                ->orWhereHas('invoices', function ($subQuery) use ($dateLimit) {
                    $subQuery->where('type', 1)
                             ->where('status', '!=', 1)
                             ->whereDate('due_date', '>=', $dateLimit);
                });
            });
        }

        $currentYear = Carbon::now()->year;

        $invoices = Invoice::where('type', 1)->where('status', $status)
            ->whereYear('created_at', $currentYear)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = array_fill(1, 12, 0);
        foreach ($invoices as $month => $total) {
            $months[$month] = $total;
        }
        return view('app.User.list-actives', [
            'users'         => $query->orderBy('name', 'asc')->paginate(30),
            'invoicesData'  => array_values($months)
        ]);
    }

    public function sendActive($id) {

        $user = User::find($id);
        if($user) {

            $message =  "Olá, {$user->name},\r\n\r\n"
                        . "Sua conta em nossa plataforma ainda não foi ativada. Para continuar a ganhar comissões e aproveitar nossos benefícios, ative sua conta até ". Carbon::now()->addDays(30)->format('d/m/Y') ." \r\n\r\n"
                        . "*Instruções para ativação:*\r\n\r\n"
                        . "Acesse: ".env('APP_URL')."\r\n"
                        . "Faça login com seu e-mail e CPF. \r\n"
                        . "Complete a ativação \r\n"
                        . "Após a data limite, o acesso será desativado permanentemente. \r\n\r\n"
                        . "Precisa de ajuda? Estamos aqui para você!\r\n\r\n";
            $this->sendWhatsapp(
                env('APP_URL'),
                $message,
                $user->phone,
                $user->api_token_zapapi
            );

            return redirect()->back()->with('success', 'mensagem enviada com sucesso!');
        }

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
    
    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
