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
        'status_serasa',
        'status_spc',
        'status_boa_vista',
        'status_quod',
        'status_cenprot',
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

    public function statusLabelSerasa() {
        switch ($this->status_serasa) {
            case 1:
                return 'Baixada';
            case 2:
                return 'Pendente';
            default:
                return 'Pendente';
        }
    }

    public function statusLabelSpc() {
        switch ($this->status_spc) {
            case 1:
                return 'Baixada';
            case 2:
                return 'Pendente';
            default:
                return 'Pendente';
        }
    }

    public function statusLabelBoaVista() {
        switch ($this->status_boa_vista) {
            case 1:
                return 'Baixada';
            case 2:
                return 'Pendente';
            default:
                return 'Pendente';
        }
    }

    public function statusLabelQuod() {
        switch ($this->status_quod) {
            case 1:
                return 'Baixada';
            case 2:
                return 'Pendente';
            default:
                return 'Pendente';
        }
    }

    public function statusLabelCenprot() {
        switch ($this->status_cenprot) {
            case 1:
                return 'Baixada';
            case 2:
                return 'Pendente';
            default:
                return 'Pendente';
        }
    }
}
