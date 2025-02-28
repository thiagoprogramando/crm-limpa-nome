<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();

            $table->integer('id_user')->nullable();
            $table->integer('id_event')->nullable();
            $table->integer('type')->nullable();
            $table->integer('view')->nullable();

            $table->string('name');
            $table->string('description');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('notification');
    }
};
