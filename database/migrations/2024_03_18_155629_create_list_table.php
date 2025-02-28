<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('list', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();

            $table->dateTime('start');
            $table->dateTime('end');

            $table->integer('status');

            $table->string('serasa_status')->default('Pendente');
            $table->string('status_spc')->default('Pendente');
            $table->string('status_boa_vista')->default('Pendente');
            $table->string('status_quod')->default('Pendente');
            $table->string('status_cenprot')->default('Pendente');
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('list');
    }
};
