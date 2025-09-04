<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Client;
use Carbon\Carbon;


class User extends Authenticatable {
    
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'photo',
        'name',
        'email',
        'cpfcnpj',
        'birth_date',
        'phone',
        'password',

        'level',
        'status',

        'postal_code',
        'address',
        'complement',
        'city',
        'state',
        'num',

        'wallet',
        'token_wallet',
        'token_key',

        'token_whatsapp',
        'customer',

        'type',
        'filiate',
        'fixed_cost'
    ];

    public function sales() {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    public function saleTotal() {
        return Invoice::whereIn('sale_id', $this->sales()->pluck('id'))->sum('value');
    }

    public function salesClient() {
        return $this->hasMany(Sale::class, 'client_id');
    }

    public function invoices() {
        return $this->hasMany(Invoice::class, 'user_id')->orderBy('status', 'asc');
    }

    public function saleCount() {
        return Sale::where('seller_id', $this->id)->where('status', 1)->count();
    }

    public function parent() {
        return $this->belongsTo(User::class, 'filiate');
    }

    public function afiliates() {
        return $this->hasMany(User::class, 'filiate', 'id');
    }

    public function activeFiliatesCount() {

        if ($this->type == 1) {
            return User::where('type', 2)->where('status', 1)->count();
        }

        return $this->hasMany(User::class, 'filiate', 'id')->where('status', 1)->count();
    }

    public function inactiveFiliatesCount() {

        if ($this->type == 1) {
            return User::where('type', 2)->where('status', 2)->count();
        }

        return $this->hasMany(User::class, 'filiate', 'id')->where('status', 2)->count();
    }

    public function lastPendingInvoiceTypeOne() {

        return $lastPendingInvoice = $this->invoices()
            ->where('type', 1)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function months() {
        return $this->hasMany(Invoice::class, 'user_id')
                ->where('type', 1)
                ->where('status', 1)
                ->count();
    }

    public function levelLabel() {

        switch ($this->level) {
            case 1:
                return 'Iniciante';
                break;
            case 2:
                return 'Agente Profissional';
                break; 
            case 3:
                return 'Consultor Avançado';
                break; 
            case 4:
                return 'Especialista Executivo';
                break; 
            case 5:
                return 'Gestor Regional';
                break; 
            case 6:
                return 'Diretor Nacional';
                break; 
            case 7:
                return 'Embaixador Master Brasil';
                break; 
        }

        return '---';
    }

    public function statusLabel() {

        switch ($this->status) {
            case 1:
                return 'Ativo e Associado';
                break;
            case 2:
                return 'Pendente de Associação/Inativo';
                break; 
            case 3:
                return 'Pendente de Dados';
                break;  
            default:
                return 'Sem Dados';
                break;     
        }
    }

    public function getGraduation() {
        
        $saleTotal = $this->saleCount();

        $levels = [
            'Iniciante'                 => 1,
            'Agente Profissional'       => 100,
            'Consultor Avançado'        => 250,
            'Especialista Executivo'    => 500,
            'Gestor Regional'           => 1000,
            'Diretor Nacional'          => 2500,
            'Embaixador Master Brasil'  => 5000,
        ];

        $nivel = 'Iniciante';
        $maxSalesAtual = end($levels);
        $progressAtual = 100;

        foreach ($levels as $key => $maxSales) {
            if ($saleTotal < $maxSales) {
                $nivel = $key;
                $maxSalesAtual = $maxSales;
                $progressAtual = min(100, ($saleTotal / $maxSales) * 100);
                break;
            }
        }

        return (object) [
            'nivel' => $nivel,
            'progress' => $progressAtual,
            'maxSales' => $maxSalesAtual,
        ];
    }

    public function cpfcnpjLabel() {
        $cpfCnpj = $this->cpfcnpj;

        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);

        if (strlen($cpfCnpj) === 11) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpfCnpj);
        } elseif (strlen($cpfCnpj) === 14) {
            return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cpfCnpj);
        }

        return $cpfCnpj;
    }

    public function timeMonthly() {
       
        $lastInvoice = $this->invoices()->where('type', 1)->orderBy('due_date', 'desc')->first();
        if ($lastInvoice) {
            
            $nextInvoiceDate = Carbon::parse($lastInvoice->due_date)->addDays(30);
            $daysRemaining = Carbon::now()->diffInDays($nextInvoiceDate, false);

            return intval($daysRemaining > 0 ? $daysRemaining : 0);
        }

        return 0;
    }

    public function maskedName() {
        
        if (!$this->name) {
            return '';
        }
    
        $nameParts = explode(' ', $this->name);
        return $nameParts[0];
    }  
    
    public function address() {
        $this->address.', '.$this->num.' '.$this->city.'/'.$this->state.' - '.$this->postal_code;
    }

    public function balance() {
        try {
            $client = new Client();

            $response = $client->request('GET', env('API_URL_ASSAS') . 'v3/finance/balance', [
                'headers' => [
                    'accept'       => 'application/json',
                    'access_token' => $this->token_key,
                    'User-Agent'   => env('APP_NAME'),
                ],
                'verify' => false,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                return $data['balance'] ?? 0;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar saldo de '.$this->name.': ' . $e->getMessage());
            return false;
        }
    }

    public function getTokenWhatsapp() {

        if (!empty($this->token_whatsapp)) {
            return $this->token_whatsapp;
        } elseif (!empty($this->parent->token_whatsapp)) {
            return $this->parent->token_whatsapp;
        }

        return env('APP_TOKEN_WHATSAPP');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot() {

        parent::boot();

        static::deleting(function ($user) {
            $user->salesClient()->delete();
            $user->salesSeller()->delete();
            $user->invoices()->delete();
        });
    }
}
