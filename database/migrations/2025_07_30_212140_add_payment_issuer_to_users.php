<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('token_issuer', ['AMPAY', 'EXPRESS', 'ASSOCIATION', 'MY'])->nullable()->after('token_key');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token_issuer');
        });
    }
};
