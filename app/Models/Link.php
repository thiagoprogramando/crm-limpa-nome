<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model {
    
    protected $table = 'links';

    protected $fillable = [
        'user_id',
        'product_id',
        'title',
        'description',
        'value',
        'type',
        'payment_method',
        'payment_installments',
        'payment_json_installments',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    protected $casts = [
        'payment_json_installments' => 'array',
    ];

}
