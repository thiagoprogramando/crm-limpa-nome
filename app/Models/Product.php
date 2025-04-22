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
        'request_photo',
        'request_document_photo',
        'request_address'.
        'request_contract',
        'subject_contract',
        'request_terms',
        'subject_terms',
        'access_level',
        'status'
    ];

    public function sales() {
        return $this->hasMany(Sale::class, 'product_id');
    }

    public function totalSale() {
        return $this->sales()->where('status', 1)->count();
    }
}
