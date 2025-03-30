<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->tinyInteger('status');
            $table->tinyInteger('status_serasa')->default(0);
            $table->tinyInteger('status_spc')->default(0);
            $table->tinyInteger('status_boa_vista')->default(0);
            $table->tinyInteger('status_quod')->default(0);
            $table->tinyInteger('status_cenprot')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lists');
    }
};
