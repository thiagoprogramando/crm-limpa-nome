<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('id_product')->nullable();
            $table->unsignedBigInteger('id_sale')->nullable();

            $table->string('name');
            $table->string('description');

            $table->string('token_payment')->nullable();
            $table->string('url_payment')->nullable();

            $table->date('due_date');

            $table->decimal('value', 10, 2);
            $table->decimal('commission', 10, 2);
            
            $table->integer('status');
            $table->integer('num');
            $table->integer('type'); // 1 - Mensalidade 2 - Faturas 3 - Extras 4 - Saldo em Carteira

            $table->timestamps();
        });
    }


    public function down(): void {
        Schema::dropIfExists('invoice');
    }
};
