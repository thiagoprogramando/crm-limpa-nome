<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->unsignedBigInteger('id_payment')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->unsignedBigInteger('id_payment')->nullable(false)->change();
        });
    }
};
