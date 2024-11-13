<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->decimal('value_min', 10, 2)->nullable();
            $table->decimal('value_max', 10, 2)->nullable();
            $table->decimal('value_cost', 10, 2);
            $table->decimal('value_rate', 10, 2)->nullable();
            $table->boolean('address')->nullable();
            $table->boolean('createuser')->nullable();
            $table->integer('level')->nullable();
            $table->longText('contract')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product');
    }
};
