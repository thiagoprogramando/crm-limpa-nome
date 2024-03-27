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
    ];

    public function levelLabel() {

        switch ($this->level) {
            case 1:
                return 'START';
                break;
            case 2:
                return 'CONSULTOR';
                break; 
            case 3:
                return 'LÍDER';
                break; 
            case 4:
                return 'REGIONAL';
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
