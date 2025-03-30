<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->decimal('value_min', 10, 2)->nullable();
            $table->decimal('value_max', 10, 2)->nullable();
            $table->decimal('value_cost', 10, 2)->default(0);
            $table->decimal('value_rate', 10, 2)->default(0);
            
            $table->tinyInteger('address')->default(0);
            $table->tinyInteger('level')->nullable();
            $table->tinyInteger('active')->default(2);
            $table->tinyInteger('terms')->default(2);
            
            $table->longText('subject_contract')->nullable();
            $table->longText('subject_terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};
