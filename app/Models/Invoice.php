<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model {

    use HasFactory, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'user_id',
        'product_id',
        'sale_id',

        'name',
        'description',
        'num',

        'payment_token',
        'payment_url',

        'due_date',
        'value',
        'commission_seller',
        'commission_sponsor',
        'status',
        'type',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sale() {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function statusLabel() {
        switch ($this->status) {
            case 0:
                return 'Pendente';
                break;
            case 1:
                return 'Aprovado';
                break;      
            return $this->status;
        }
    }
}
