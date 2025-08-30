<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {

    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'user_id',
        'product_id',
        'sale_id',
        'name',
        'description',
        'payment_token',
        'payment_url',
        'payment_splits',
        'due_date',
        'value',
        'commission_seller',
        'commission_afiliate',
        'status',
        'num',
        'type',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sale() {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function statusLabel() {
        switch ($this->status) {
            case 0:
                return 'Pendente';
                break;
            case 1:
                return 'Aprovado';
                break;      
            default:
                return 'Pendente';
                break;
        }
    }
}
