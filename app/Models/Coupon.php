<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model {
    
    protected $table = 'coupons';

    protected $fillable = [
        'id_user',
        'name',
        'description',
        'percentage',
        'qtd',
        'expiry_date',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }
}
