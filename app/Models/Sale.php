<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model {

    use HasFactory;

    protected $table = 'sale';

    protected $fillable = [

        'id_product',
        'id_payment',
        'id_list',
        'id_client',
        'id_seller',

        'payment',
        'installments',

        'value',
        'commission',

        'token_payment',
        'token_contract',
        'url_contract',

        'status',
        'label',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function payment() {
        return $this->belongsTo(Payment::class, 'id_payment');
    }

    public function user() {
        return $this->belongsTo(User::class, 'id_client');
    }

    public function statusLabel() {
        switch ($this->status) {
            case 1:
                return 'Pagamento confirmado';
                break;
            case 2:
                return 'Contrato Assinado';
                break;
            case 3:
                return 'Pendente de Assinatura';
                break;
            case 4:
                return 'Pendente de Pagamento';
                break;
            default:
                return 'Pendente';
                break;
        }
    }
}
