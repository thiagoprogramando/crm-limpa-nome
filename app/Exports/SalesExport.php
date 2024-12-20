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
                'Cliente'       => $sale->user->name,
                'CPF/CNPJ'      => $sale->user->cpfcnpj,
            ];
        });
    }

    public function headings(): array {
        return [
            'Cliente',
            'CPF/CNPJ',
        ];
    }
}
