<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('fixed_cost', 10, 2)->nullable()->after('api_token_zapapi');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            
        });
    }
};
