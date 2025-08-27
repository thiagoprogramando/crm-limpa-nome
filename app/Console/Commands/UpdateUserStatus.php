<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class UpdateUserStatus extends Command {

    protected $signature = 'users:update-status';
    protected $description = 'Atualiza usuários para status 2 caso tenham invoices atrasadas há mais de 60 dias';

    public function handle() {
        
        $this->info('Iniciando atualização de usuários para status 2...');

        $usersWithOldInvoices = User::where('type', 2)
            ->whereHas('invoices', function($q) {
                $q->where('type', 1)
                ->where('status', '!=', 1)
                ->where('created_at', '<=', now()->subDays(60));
            })
            ->get();

        $usersWithoutInvoices = User::where('type', 2)
            ->whereDoesntHave('invoices', function($q) {
                $q->where('type', 1);
            })
            ->get();

        $users = $usersWithOldInvoices->merge($usersWithoutInvoices);
        foreach ($users as $user) {
            $user->update(['status' => 2]);
            $this->info("Usuário {$user->id} atualizado para status 2");
        }

        $this->info("Processo concluído. Total: {$users->count()} usuários atualizados.");
    }
}
