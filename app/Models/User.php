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

        'token_acess',
        'wallet',
        'wallet_id',
        'api_key',
        'api_token_zapapi',
        'customer',
        'wallet_off',

        'type',
        'filiate',

        'fixed_cost'
    ];

    public function salesClient() {
        return $this->hasMany(Sale::class, 'id_client');
    }

    public function salesSeller() {
        return $this->hasMany(Sale::class, 'id_seller');
    }

    public function invoices() {
        return $this->hasMany(Invoice::class, 'id_user')->orderBy('status', 'asc');
    }

    public function lastPendingInvoiceTypeOne() {

        $lastPendingInvoice = $this->invoices()
            ->where('type', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastPendingInvoice 
            ? $lastPendingInvoice->created_at->format('d/m/Y H:i:s') 
            : '---';
    }

    public function months() {
        return $this->hasMany(Invoice::class, 'id_user')
                ->where('type', 1)
                ->where('status', 1)
                ->count();
    }

    public function levelLabel() {

        switch ($this->level) {
            case 1:
                return 'INICIANTE';
                break;
            case 2:
                return 'CONSULTOR';
                break; 
            case 3:
                return 'CONSULTOR LÍDER';
                break; 
            case 4:
                return 'REGIONAL';
                break; 
            case 5:
                return 'GERENTE REGIONAL';
                break; 
            case 6:
                return 'VENDEDOR INTERNO';
                break; 
            case 7:
                return 'DIRETOR';
                break; 
            case 8:
                return 'DIRETOR REGIONAL';
                break; 
            case 9:
                return 'PRESIDENTE VIP';
                break;        
            return $this->method;
        }
    }

    public function saleTotal() {

        $id = $this->id;
        return Sale::where('id_seller', $id)->where('status', 1)->sum('value');
    }

    public function saleCount() {

        $id = $this->id;
        return Sale::where('id_seller', $id)->where('status', 1)->count();
    }

    public function commissionTotal() {

        $id = $this->id;
        return Sale::where('id_seller', $id)->where('status', 1)->sum('commission');
    }

    public function commissionTotalParent() {

        $id = $this->id;
        return Sale::where('id_seller', $id)->where('status', 1)->sum('commission_filiate');
    }

    public function statusLabel() {

        switch ($this->status) {
            case 1:
                return 'Ativo e Associado';
                break;
            case 2:
                return 'Pendente de Documentos';
                break; 
            case 3:
                return 'Pendente de Associação';
                break; 
            case 4:
                return 'Pendente de Dados';
                break;         
            return $this->method;
        }
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

    public function indicator() {
        
        if ($this->filiate !== null) {
            
            $affiliate = User::find($this->filiate);
            return $affiliate ? $affiliate->name : "---";
        }

        return "---";
    }

    public function parent() {
        return $this->belongsTo(User::class, 'filiate');
    }

    public function afiliates() {
        return $this->hasMany(User::class, 'filiate', 'id');
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
                    'access_token' => $this->api_key,
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
