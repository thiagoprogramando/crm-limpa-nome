<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('invoice', function (Blueprint $table) {
            $table->decimal('commission_filiate', 10, 2)->default(0)->after('commission');
        });
    }

    public function down(): void {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('commission_filiate');
        });
    }
};
