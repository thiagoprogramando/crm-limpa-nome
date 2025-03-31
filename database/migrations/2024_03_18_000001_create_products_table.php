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
            
            $table->tinyInteger('request_photo')->default(0);
            $table->tinyInteger('request_document_photo')->default(0);
            $table->tinyInteger('request_address')->default(0);
            $table->tinyInteger('request_contract')->default(0);
            $table->longText('subject_contract')->nullable();
            $table->tinyInteger('request_terms')->default(0);
            $table->longText('subject_terms')->nullable();

            $table->tinyInteger('access_level')->nullable();
            $table->tinyInteger('status')->default(2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};
