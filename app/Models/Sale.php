<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model {

    use HasFactory, SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'product_id',
        'list_id',
        'client_id',
        'seller_id',
        'payment_token',
        'payment_method',
        'payment_installments',
        'contract_url',
        'contract_sign',
        'guarantee',
        'label',
        'status',
        'type',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function invoices() {
        return $this->hasMany(Invoice::class, 'sale_id', 'id');
    }

    public function invoicesByToken() {
        return $this->hasMany(Invoice::class, 'payment_token', 'payment_token');
    }

    public function list() {
        return $this->belongsTo(SaleList::class, 'list_id');
    }

    public function client() {
        return $this->belongsTo(User::class, 'client_id')->withTrashed();
    }

    public function seller() {
        return $this->belongsTo(User::class, 'seller_id')->withTrashed();
    }    

    public function totalInvoices() {
        return $this->invoices()->sum('value');
    }

    public function paymentMethod() {
        switch ($this->payment_method) {
            case 'PIX':
                return 'Pix';
                break;
            case 'BOLETO':
                return 'Boleto';
                break;
            default:
                return 'Cartão de Crédito';
                break;
        }
    }
    
    public function statusPaymentLabel() {
        switch ($this->status) {
            case 1:
                return 'Confirmado';
                break;
            case 2:
                return 'Pendente';
                break;
            default:
                return 'Desconhecido';
                break;
        }
    }

    public function statusContractLabel() {

        if (empty($this->contract_sign)) {
            return 'Pendente de Assinatura';
        }

        return 'Assinado';
    }
}
