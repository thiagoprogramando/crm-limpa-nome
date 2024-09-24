<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('list', function (Blueprint $table) {
            $table->string('serasa_status')->default('Pendente');
            $table->string('status_spc')->default('Pendente');
            $table->string('status_boa_vista')->default('Pendente');
        });
    }

    public function down(): void {
        Schema::table('list', function (Blueprint $table) {
            $table->dropColumn(['serasa_status', 'status_spc', 'status_boa_vista']);
        });
    }
};
