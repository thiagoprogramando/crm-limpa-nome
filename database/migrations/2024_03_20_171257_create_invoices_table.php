<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->string('name');
            $table->string('description');
            $table->integer('num');
            $table->string('payment_token')->nullable();
            $table->string('payment_url')->nullable();
            $table->date('due_date');
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('commission_seller', 10, 2)->default(0);
            $table->decimal('commission_sponsor', 10, 2)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('type')->default(3);
            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
