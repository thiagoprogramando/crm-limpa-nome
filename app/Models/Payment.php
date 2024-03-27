<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    
    use HasFactory;

    protected $table = 'product_payment';

    protected $fillable = [
        'id_product',
        'method',
        'installments',
        'value_rate',
    ];

    public function methodLabel() {
        switch ($this->method) {
            case 'CREDIT_CARD':
                return 'Cartão de Crédito';
                break;
            case 'PIX':
                return 'Pix';
                break; 
            case 'BOLETO':
                return 'Boleto';
                break;       
            return $this->method;
        }
    }
}
