<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->unique();
            $table->longText('description')->nullable();
            $table->decimal('percentage', 5, 2);
            $table->tinyInteger('qtd')->default(1);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::table('coupons', function (Blueprint $table) {
            
        });
    }
};
