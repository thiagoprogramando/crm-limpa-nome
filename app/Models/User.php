<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

        'type',
        'filiate',
        'api_token_zapapi'
    ];

    public function invoices() {
        return $this->hasMany(Invoice::class, 'id_user');
    }

    public function invoicesPendent() {
        return $this->invoices()->where('type', 1)->where('status', 0)->count();
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
            return $this->method;
        }
    }

    public function saleTotal() {

        $id = $this->id;
        return Sale::where('id_seller', $id)->where('status', 1)->sum('value');
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
