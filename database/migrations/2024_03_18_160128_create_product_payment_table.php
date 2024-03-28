<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('product_payment', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('product')->onDelete('cascade');

            $table->string('method');
            $table->integer('installments')->nullable();
            $table->decimal('value_rate', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_payment');
    }
};
