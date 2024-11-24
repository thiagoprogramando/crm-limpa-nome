<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('list', function (Blueprint $table) {
            $table->string('status_quod')->default('Pendente');
            $table->string('status_cenprot')->default('Pendente');
        });
    }

    public function down(): void {
        Schema::table('lists', function (Blueprint $table) {
            $table->dropColumn('status_quod');
            $table->dropColumn('status_cenprot');
        });
    }
};
