<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('sale', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('product');

            $table->unsignedBigInteger('id_payment');
            $table->foreign('id_payment')->references('id')->on('product_payment');

            $table->unsignedBigInteger('id_list');
            $table->foreign('id_list')->references('id')->on('list');

            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')->references('id')->on('users');

            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users');

            $table->string('payment');
            $table->integer('installments');

            $table->decimal('value', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();

            $table->string('token_payment')->nullable();
            $table->string('token_contract')->nullable();
            $table->longText('url_contract')->nullable();

            $table->integer('status')->nullable(); // 0 - Pendente 1 - Pagamento confirmado 2 - Contrato Assinado 3 - Pendente de Assinatura 4 - Pendente de Pagamento
            $table->integer('wallet_off')->nullable();
            $table->string('label')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sale');
    }
};
