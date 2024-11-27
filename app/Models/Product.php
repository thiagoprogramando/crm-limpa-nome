<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    use HasFactory;

    protected $table = 'product';

    protected $fillable = [

        'name',
        'description',

        'level',
        'contract',
        'address',
        'createuser',

        'value_min',
        'value_max',
        'value_cost',
        'value_rate',
    ];

    public function totalSale() {
        return $this->sales()->whereIn('status', [1, 2])->count();
    }

    public function sales() {
        return $this->hasMany(Sale::class, 'id_product');
    }
}
