<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('product');

            $table->unsignedBigInteger('id_list');
            $table->foreign('id_list')->references('id')->on('list');

            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')->references('id')->on('users');

            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users');

            $table->string('payment_token')->nullable();
            $table->string('payment_method');
            $table->tinyInteger('payment_installments')->default(1);

            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('value_total', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('commission_filiate', 10, 2)->default(0);
            
            $table->longText('contract_url')->nullable();
            $table->longText('contract_sign')->nullable();

            $table->date('guarantee')->nullable();
            $table->string('label')->nullable();

            $table->tinyInteger('status')->default(2);
            $table->tinyInteger('type')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sales');
    }
};
