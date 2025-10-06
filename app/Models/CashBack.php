<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBack extends Model {
    
    protected $table = 'wallet_extracts';

    protected $fillable = [
        'uuid',
        'user_id',
        'sale_id',
        'description',
        'value',
        'type',
        'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function labelType($value) {
        $types = [
            1 => 'Entrada',
            2 => 'Saída'
        ];

        return $types[$value] ?? 'Nulo';
    }

    public function labelStatus($value) {
        $status = [
            1 => 'Aprovado',
            2 => 'Pendente',
            3 => 'Estornado'
        ];

        return $status[$value] ?? 'Nulo';
    }
}
