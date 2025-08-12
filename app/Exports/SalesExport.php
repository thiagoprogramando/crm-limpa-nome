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
                'Cliente'       => $sale->client->name,
                'CPF/CNPJ'      => $sale->client->cpfcnpj,
                'Consultor'     =>  $sale->seller->name,
                'Observação'    =>  $sale->label,
                
            ];
        });
    }

    public function headings(): array {
        return [
            'Cliente',
            'CPF/CNPJ',
            'Consultor',
            'Observação',
        ];
    }
}
