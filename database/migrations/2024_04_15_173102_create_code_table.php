<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('code', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->string('code');
            $table->integer('status')->nullable();
            $table->date('data_generate');
            $table->date('data_used')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('code');
    }
};
