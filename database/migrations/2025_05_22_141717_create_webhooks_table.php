<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('email')->nullable();
            $table->string('enabled')->nullable();
            $table->string('interrupted')->nullable();
            $table->integer('apiVersion')->nullable();
            $table->string('sendType')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('webhooks');
    }
};
