<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('view')->default(0);
            $table->string('name');
            $table->string('description');
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('notification');
    }
};
