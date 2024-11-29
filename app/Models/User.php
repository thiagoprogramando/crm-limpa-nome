<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Carbon\Carbon;

class User extends Authenticatable {
    
    use HasFactory, Notifiable;

    protected $fillable = [
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
        'api_key',
        'api_token_zapapi',
        'customer',
        'wallet_off',

        'type',
        'filiate',

        'fixed_cost'
    ];

    public function invoices() {
        return $this->hasMany(Invoice::class, 'id_user');
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
                return 'CONSULTOR MASTER';
                break;
            case 8:
                return 'VENDEDOR MASTER';
                break; 
            case 9:
                return 'CONSULTOR VIP';
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

    public function timeMonthly() {
       
        $lastInvoice = $this->invoices()->where('type', 1)->orderBy('due_date', 'desc')->first();
        if ($lastInvoice) {
            
            $nextInvoiceDate = Carbon::parse($lastInvoice->due_date)->addDays(30);
            $daysRemaining = Carbon::now()->diffInDays($nextInvoiceDate, false);

            return intval($daysRemaining > 0 ? $daysRemaining : 0);
        }

        return 0;
    }

    public function promoCruzeiro() {
        
        $saleCount = $this->saleCount();
        if ($saleCount < 100) {
            return $saleCount;
        } elseif ($saleCount >= 100 && $saleCount <= 200) {
            return $saleCount - 100;
        } else {
            return 100;
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
}
