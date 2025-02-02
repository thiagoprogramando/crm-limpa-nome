<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model {

    use HasFactory, SoftDeletes;

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
        'value_total',
        'commission',
        'commission_filiate',

        'token_payment',
        'token_contract',
        'url_contract',
        'status_contract',

        'status',
        'wallet_off',
        'label',
        'guarantee'
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function paymentMethod() {
        switch ($this->payment) {
            case 'PIX':
                return 'Pix';
                break;
            case 'BOLETO':
                return 'Boleto';
                break;
            default:
                return 'Cartão de Crédito';
                break;
        }
    }

    public function user() {
        return $this->belongsTo(User::class, 'id_client')->withTrashed();
    }

    public function seller() {
        return $this->belongsTo(User::class, 'id_seller')->withTrashed();
    }

    public function list() {
        return $this->belongsTo(Lists::class, 'id_list');
    }

    public function statusLabel() {
        switch ($this->status) {
            case 1:
                return 'Confirmado';
                break;
            case 4:
                return 'Pendente';
                break;
            default:
                return 'Pendente';
                break;
        }
    }

    public function statusContractLabel() {
        switch ($this->status_contract) {
            case 1:
                return 'Assinado';
                break;
            case 2:
                return 'Pendente';
                break;
            case 3:
                return 'Contrato não gerado';
                break;
            default:
                return 'Contrato não gerado';
                break;
        }
    }
}
