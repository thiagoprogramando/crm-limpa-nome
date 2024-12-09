<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesExport implements FromCollection, WithHeadings, ShouldAutoSize {

    protected $sales;

    public function __construct($sales) {
        $this->sales = $sales;
    }

    public function collection() {
        return $this->sales->map(function ($sale) {
            return [
                'ID'            => $sale->id,
                'Produto'       => $sale->product->name,
                'Cliente'       => $sale->user->name,
                'CPF/CNPJ'      => $sale->user->cpfcnpj,
                'Vendedor'      => $sale->seller->name,
                'Valor'         => $sale->value,
                'Comissão'      => $sale->commission,
                'Data da Venda' => $sale->created_at->format('d/m/Y H:i:s')
            ];
        });
    }

    public function headings(): array {
        return [
            'ID',
            'Produto',
            'Cliente',
            'CPF/CNPJ',
            'Vendedor',
            'Valor',
            'Comissão',
            'Data da Venda'
        ];
    }
}
