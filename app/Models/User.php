<?php

namespace App\Models;

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
        'sponsor_id',
        'association_id',

        'photo',
        'name',
        'email',
        'cpfcnpj',
        'birth_date',
        'phone',
        'password',

        'postal_code',
        'address',
        'complement',
        'city',
        'state',
        'num',

        'level',
        'status',

        'wallet_off',
        'wallet',
        'wallet_id',
        'api_key',
        'customer',

        'fixed_cost',
        'type',
       
        'white_label_whatsapp',
        'white_label_contract',
        'white_label_network',

        'company_name',
        'company_cpfcnpj',
        'company_address',
        'company_email'
    ];

    public function maskedName() {
        
        if (!$this->name) {
            return '';
        }
    
        $nameParts = explode(' ', $this->name);
        return $nameParts[0];
    }

    public function cpfcnpjLabel() {

        $cpfCnpj = preg_replace('/[^0-9]/', '', $this->cpfcnpj);
        if (strlen($cpfCnpj) === 11) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpfCnpj);
        } elseif (strlen($cpfCnpj) === 14) {
            return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cpfCnpj);
        }

        return $cpfCnpj;
    }

    public function sponsor() {
        return $this->belongsTo(User::class, 'sponsor_id')->withTrashed();
    }

    public function sales() {
        return $this->hasMany(Sale::class, 'seller_id')->where('status', 1);
    }

    public function invoices() {
        return $this->hasMany(Invoice::class, 'user_id')->orderBy('status', 'asc');
    }

    // public function lastPendingInvoiceTypeOne() {

    //     $lastPendingInvoice = $this->invoices()
    //         ->where('type', 1)
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     return $lastPendingInvoice 
    //         ? $lastPendingInvoice->created_at->format('d/m/Y H:i:s') 
    //         : '---';
    // }

    // public function lastPendingInvoiceTypeOneUrl() {

    //     $lastPendingInvoice = $this->invoices()
    //         ->where('type', 1)
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     return $lastPendingInvoice 
    //         ? $lastPendingInvoice->url_payment
    //         : '---';
    // }

    // public function months() {
    //     return $this->hasMany(Invoice::class, 'user_id')
    //             ->where('type', 1)
    //             ->where('status', 1)
    //             ->count();
    // }

    // public function saleTotal() {

    //     $id = $this->id;
    //     return Sale::where('seller_id', $id)->where('status', 1)->sum('value');
    // }

    // public function saleCount() {

    //     $id = $this->id;
    //     return Sale::where('seller_id', $id)->where('status', 1)->count();
    // }

    // public function commissionTotal() {

    //     $id = $this->id;
    //     return Sale::where('seller_id', $id)->where('status', 1)->sum('commission');
    // }

    // public function commissionTotalParent() {

    //     $id = $this->id;
    //     return Sale::where('seller_id', $id)->where('status', 1)->sum('commission_filiate');
    // }

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
            default:
                return 'Não Operacional';
                break;    
            return $this->method;
        }
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

    public function getGraduation() {
        $saleTotal = $this->sales->count();
    
        $levels = [
            'CONSULTOR'         => 2,
            'CONSULTOR LÍDER'   => 10,
            'REGIONAL'          => 50,
            'GERENTE REGIONAL'  => 100,
            'DIRETOR'           => 300,
            'DIRETOR REGIONAL'  => 500,
            'PRESIDENTE VIP'    => 1000,
        ];
    
        $level = 'INICIANTE';
        $maxSalesAtual = 0;
        $proximoMax = null;
    
        foreach ($levels as $key => $maxSales) {
            if ($saleTotal >= $maxSales) {
                $level = $key;
                $maxSalesAtual = $maxSales;
            } else {
                $proximoMax = $maxSales;
                break;
            }
        }
    
        $progressAtual = $proximoMax
            ? min(100, ($saleTotal / $proximoMax) * 100)
            : 100;
    
        return (object) [
            'level'     => $level,
            'progress'  => $progressAtual,
            'maxSales'  => $proximoMax ?? $maxSalesAtual,
        ];
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
            'password'          => 'hashed',
        ];
    }
}
