<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',

        'value_min',
        'value_max',
        'value_cost',
        'value_rate',

        'contract_subject',
        'request_contract',
        'request_address',
        'request_selfie',
        'request_contact',
        'request_serasa',
        'request_spc',
        'request_boa_vista',
        'request_no_document',

        'level',
        'status',
    ];

    public function totalSale() {
        return $this->sales()->whereIn('status', [1, 2])->count();
    }

    public function sales() {
        return $this->hasMany(Sale::class, 'id_product');
    }
}
