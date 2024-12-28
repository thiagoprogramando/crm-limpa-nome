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

use Carbon\Carbon;

use GuzzleHttp\Client;
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

        if(!empty($request->fixed_cost)) {

            if ($this->formatarValor($request->fixed_cost) < Auth::user()->fixed_cost) {
                return redirect()->back()->with('info', 'O valor mín para custo é R$ '.Auth::user()->fixed_cost);
            }

            $user->fixed_cost = $this->formatarValor($request->fixed_cost);
        }
        
        if(!empty($request->level)) {
            $user->level = $request->level;
        }

        if(!empty($request->num)) {
            $user->num = $request->num;
        }

        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);
        }

        if(!empty($request->type)) {
            $user->type = $request->type;
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
            $request->filled('num') &&
            $user->status !== 3
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
            $query->where('cpfcnpj', $request->cpfcnpj);
        }

        $query->where('type', $type);

        $users = $query->paginate(100);

        $users->getCollection()->transform(function ($user) {
            $user->commission_total = $user->commissionTotal();
            return $user;
        });

        $sortedUsers = $users->sortByDesc('commission_total');
        $users->setCollection($sortedUsers);

        return view('app.User.list', ['users' => $users, 'type' => $type]);
    }

    public function listNetwork(Request $request) {

        $query = User::query()
            ->orderBy('name', 'asc')
            ->where('filiate', Auth::id())
            ->where('type', '!=', 3);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        return view('app.User.list-network', [
            'users' => $query->get()
        ]);
    }

    public function listClient(Request $request) {

        $query = User::orderBy('name', 'desc')->where('filiate', Auth::id())->where('type', '=', '3');

        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if (!empty($request->created_at)) {
            $query->where('created_at', $request->created_at);
        }

        $users = $query->get();

        return view('app.User.list-client', ['users' => $users]);
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

        if ($request->hasFile('file')) {
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

    public function listActive($status = null) {

        $dateLimit = Carbon::now()->subDays(90);

        if ($status == 1) {
            $users = User::whereNotIn('type', [1, 3])
            ->whereHas('invoices', function($query) use ($dateLimit) {
                $query->where('status', 1)
                      ->where('due_date', '>=', $dateLimit);
            })->orderBy('name', 'asc')->get();
        } elseif ($status == 2) {
            $users = User::whereNotIn('type', [1, 3])
                ->where(function($query) use ($dateLimit) {
                    $query->whereDoesntHave('invoices', function($subQuery) use ($dateLimit) {
                        $subQuery->where('status', 1)
                                 ->where('due_date', '>=', $dateLimit);
                    })
                    ->orWhereHas('invoices', function($subQuery) use ($dateLimit) {
                        $subQuery->where('status', 0)
                                 ->where('due_date', '>=', $dateLimit);
                    });
                })
                ->orderBy('name', 'asc')->get();
        } else {
            return redirect()->back()->with('error', 'Dados não encontrados para a pesquisa!');
        }

        return view('app.User.active', ['users' => $users]);
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

    public function createWallet() {
        
        if (!Auth::check()) {
            return redirect()->back()->with('info', 'Verifique seus dados e tente novamente!');
        }
    
        $invoice = Invoice::where('id_user', Auth::user()->id)
            ->where('type', 1)
            ->where('status', 1)
            ->first();
    
        if (!$invoice) {
            return redirect()->back()->with('info', 'Afilie-se conosco para Criar Sua Carteira Digital!');
        }
    
        $assas = new AssasController();
        $apikey = $assas->createKey($invoice->id);
    
        if ($apikey['status'] === true) {
            return redirect()->route('profile')->with('success', 'Vamos para a próxima etapa!');
        }
    
        return redirect()->back()->with('info', $apikey['error']);
    }    
}
