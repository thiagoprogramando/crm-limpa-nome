<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('product', function (Blueprint $table) {
            $table->longText('contract_subject')->default(3)->after('contract');
        });
    }

    public function down(): void {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn('contract_subject');
        });
    }
};
