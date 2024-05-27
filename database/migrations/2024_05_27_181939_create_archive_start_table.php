<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('archive_start', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->nullable();

            $table->string('title');
            $table->string('file');
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('archive_start');
    }
};
