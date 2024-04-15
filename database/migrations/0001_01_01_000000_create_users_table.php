<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpfcnpj')->unique();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            

            $table->integer('level')->nullable()->default('1'); // 1 - Start 2 - Consultor 3 - Consultor Líder 4 -Líder 5 -Regional 6 - Vendedor interno
            $table->integer('status')->nullable()->default('4'); // 1 - Ativo 2 - Documentos Pendentes 3 - Filiação Pendente 4 - Estado inicial

            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->string('complement')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('num')->nullable();

            $table->longText('token_acess')->nullable();
            $table->longText('wallet')->nullable();
            $table->longText('api_key')->nullable();
            $table->longText('customer')->nullable();

            $table->integer('type')->nullable()->default('2'); // 1 - Master 2 - Consultor 3 - Cliente 4 - Vendedor Interno 5 - Consultor Indicador
            $table->unsignedBigInteger('filiate')->nullable();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
