<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model {

    use HasFactory, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'id_user',
        'id_product',
        'id_sale',

        'name',
        'description',
        'num',

        'payment_token',
        'payment_url',

        'due_date',
        'value',
        'commission',
        'commission_filiate',
        'status',
        'type',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function sale() {
        return $this->belongsTo(Sale::class, 'id_sale');
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
