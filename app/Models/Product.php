<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {

    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',

        'value_min',
        'value_max',
        'value_cost',
        'value_rate',

        'address',
        'level',
        'active'.
        'terms',

        'subject_contract',
        'subject_terms'
    ];

    public function sales() {
        return $this->hasMany(Sale::class, 'id_product');
    }

    public function totalSale() {
        return $this->sales()->whereIn('status', 1)->count();
    }
}
