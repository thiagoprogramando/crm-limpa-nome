<?php

namespace App\Console\Commands;

use App\Http\Controllers\Assas\AssasController;

use App\Models\Invoice;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PurgeTypeOneInvoices extends Command {
    
    protected $signature    = 'invoices:purge-type-one';
    protected $description  = 'Remove todas as invoices do tipo 1 e cancela no Assas as que não estão com status = 1.';

    public function handle() {

        $this->info('Iniciando limpeza de invoices type=1...');

        $assas = new AssasController();

         $invoices = Invoice::where('type', 1)->get();

        if ($invoices->isEmpty()) {
            $this->info('Nenhuma fatura encontrada com type=1.');
            return Command::SUCCESS;
        }

        $deleted    = 0;
        $canceled   = 0;
        $errors     = 0;

        foreach ($invoices as $invoice) {
            DB::beginTransaction();
            try {
                if ($invoice->status != 1 && !empty($invoice->payment_token)) {
                    $result = $assas->cancelInvoice($invoice->payment_token);

                    if ($result) {
                        $this->line("✔ Cancelada no Assas: {$invoice->id}");
                        $canceled++;
                    } else {
                        $this->warn("⚠ Falha ao cancelar no Assas: {$invoice->id}");
                    }
                }

                $invoice->delete();
                $deleted++;

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $errors++;
                Log::error("Erro ao processar invoice {$invoice->id}: " . $e->getMessage());
                $this->error("Erro ao excluir invoice {$invoice->id}: {$e->getMessage()}");
            }
        }

        $this->info('-----------------------------------');
        $this->info("Total excluídas: {$deleted}");
        $this->info("Total canceladas no Assas: {$canceled}");
        $this->info("Total com erro: {$errors}");
        $this->info('-----------------------------------');

        $this->info('Limpeza concluída com sucesso!');

        return Command::SUCCESS;
    }
}
