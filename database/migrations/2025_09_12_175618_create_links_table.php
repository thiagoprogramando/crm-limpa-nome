<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('value', 10, 2)->default(0); 
            $table->string('type')->nullable();
            $table->string('payment_method')->default('PIX');
            $table->string('payment_installments')->default('1');
            $table->json('payment_json_installments');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('links');
    }
};
