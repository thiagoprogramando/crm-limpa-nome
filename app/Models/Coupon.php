<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model {
    
    protected $table = 'coupons';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'percentage',
        'qtd',
        'expiry_date',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
