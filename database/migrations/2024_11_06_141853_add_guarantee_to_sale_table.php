<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->date('guarantee')->nullable();
        });
    }

    public function down(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropColumn('guarantee');
        });
    }
};