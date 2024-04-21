<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('product_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product');

            $table->string('name');
            $table->longText('description')->nullable();

            $table->integer('type'); // 1 - Texto 2 - PDF ou Epub 3 - Vídeo 4 - link
            $table->string('item')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('item');
    }
};
