<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleList extends Model {

    use HasFactory;

    protected $table = 'lists';
    protected $dates = ['start', 'end'];
    protected $fillable = [
        'name',
        'description',
        'start',
        'end',
        'status',
        'status_protocol'
    ];

    public function statusLabel() {
        switch ($this->status) {
            case 1:
                return 'Ativa';
            case 2:
                return 'Inativa';
            default:
                return 'Desconhecido';
        }
    }

    public function statusProtocolLabel() {
        switch ($this->status_protocol) {
            case 1:
                return 'Regularizado';
            case 2:
                return 'Protocolado';
            case 3:
                return 'Em Processamento';
            case 4:
                return 'Em Fase de Finalização';
            default:
                return 'Período de Captação';
        }
    }
}
