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

        'id_product',
        'id_list',
        'id_client',
        'id_seller',

        'payment_token',
        'payment_method',
        'payment_installments',

        'value',
        'value_total',
        'commission',
        'commission_filiate',

        'contract_url',
        'contract_sign',

        'guarantee',
        'label',

        'status',
        'type',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function list() {
        return $this->belongsTo(Lists::class, 'id_list');
    }

    public function user() {
        return $this->belongsTo(User::class, 'id_client')->withTrashed();
    }

    public function seller() {
        return $this->belongsTo(User::class, 'id_seller')->withTrashed();
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
    
    public function statusLabel() {
        switch ($this->status) {
            case 1:
                return 'Confirmado';
                break;
            case 4:
                return 'Pendente';
                break;
            default:
                return 'Pendente';
                break;
        }
    }

    public function statusContractLabel() {

        if (empty($this->sign_contract)) {
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
            $this->list->status_quod        === 'Baixado' &&
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
