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

        'payment',
        'installments',

        'value',
        'value_total',
        'commission',
        'commission_filiate',

        'token_payment',
        'token_contract',
        'url_contract',
        'status_contract',

        'status',
        'wallet_off',
        'label',
        'guarantee'
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

    public function client() {
        return $this->belongsTo(User::class, 'client_id')->withTrashed();
    }

    public function seller() {
        return $this->belongsTo(User::class, 'seller_id')->withTrashed();
    }

    public function list() {
        return $this->belongsTo(Lists::class, 'list_id');
    }

    public function totalInvoices() {

        $total = Invoice::where('sale_id', $this->id)->orWhere('payment_token', $this->payment_token)->sum('value');
        if ($this->payment_token == null) {
            return $total;
        }

        $sales = Sale::where('payment_token', $this->payment_token)->count();
        if ($sales > 1) {
            return $total / $sales;
        }

        return $total;
    }

    public function paymentMethod() {
        switch ($this->payment) {
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

    public function protocolLabel() {
        
        if ($this->status == 1) {
            $data = [
                'label' => 'Protocolado',
                'color' => 'warning',
                'title' => 'O pedido foi oficialmente registrado e está aguardando processamento.'
            ];
        }

        if ($this->status == 1 && $this->list()->exists() && $this->list->status == 2) {
            $data = [
                'label' => 'Em Processamento',
                'color' => 'info',
                'title' => 'A remoção está sendo tratada pelos órgãos responsáveis.'
            ];
        }

        if ($this->status == 1 && $this->allStatus() && $this->list->status == 2) {
            $data = [
                'label' => 'Em Fase de Finalização',
                'color' => 'info',
                'title' => 'Últimos ajustes antes da remoção ser concluída.'
            ];
        }

        if ($this->status == 1 && $this->list->status == 2 && $this->listEnd() && $this->allStatus()) {
            $data = [
                'label' => 'Regularizado',
                'color' => 'success',
                'title' => 'O cliente está oficialmente com o nome limpo e pode consultar nos órgãos de proteção ao crédito.'
            ];
        }

        return $data ?? [
            'label' => '',
            'color' => '',
            'title' => ''
        ];
    }

    public function allStatus() {
        return $this->list()->exists() &&
            $this->list->serasa_status      === 'Baixado' &&
            $this->list->status_spc         === 'Baixado' &&
            $this->list->status_boa_vista   === 'Baixado' &&
            $this->list->status_cenprot     === 'Baixado';
    }

    public function listEnd() {
        
        if (!$this->list()->exists() || !$this->list->end) {
            return false;
        }

        $date_due = Carbon::parse($this->list->end);
        return $date_due->diffInWeekdays(now()) >= 7;
    }
}
