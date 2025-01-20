<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->decimal('value_total', 10, 2)->after('value')->nullable();
        });

        DB::statement('UPDATE sale SET value_total = value');
    }

    public function down(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropColumn('value_total');
        });
    }
};
