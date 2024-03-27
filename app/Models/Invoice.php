<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {

    use HasFactory;

    protected $table = 'invoice';

    protected $fillable = [
        'id_user',
        'id_product',
        'id_sale',

        'name',
        'description',

        'token_payment',
        'url_payment',

        'due_date',
        'value',
        'commission',
        'status',
        'num',
        'type',
    ];

    public function sale() {
        return $this->belongsTo(Sale::class, 'id_sale');
    }

    public function statusLabel() {
        switch ($this->status) {
            case 0:
                return 'Pendente';
                break;
            case 1:
                return 'Aprovado';
                break;      
            return $this->status;
        }
    }
}
