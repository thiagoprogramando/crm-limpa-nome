<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSalesStatusContract extends Command {

    protected $signature = 'update:sales-status-contract';
    protected $description = 'Atualiza os campos status_contract na tabela sales';

    public function handle() {
        DB::table('sale')->where('status', 1)->update(['status_contract' => 1]);
        DB::table('sale')->where('status', 2)->update(['status_contract' => 1]);
        DB::table('sale')->where('status', 3)->update(['status_contract' => 2]);
        DB::table('sale')->where('status', 4)->update(['status_contract' => 1]);
        DB::table('sale')->where('status', 0)->update(['status_contract' => 3]);

        $this->info('Campos status_contract atualizados com sucesso.');
    }
}
