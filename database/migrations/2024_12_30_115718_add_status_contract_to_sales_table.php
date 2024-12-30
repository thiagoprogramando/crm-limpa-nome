<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->integer('status_contract')->nullable()->after('url_contract'); // 1 - Assinado 2 - Pendente de Assinatura 3 - Sem Contrato
        });
    }

    public function down(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropColumn('status_contract');
        });
    }
};
