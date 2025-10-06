<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::create('wallet_extracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('sale_id')->nullable();
            $table->string('description');
            $table->decimal('value', 10, 2);
            $table->integer('type')->default(2);
            $table->integer('status')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('wallet_extracts');
    }
};
