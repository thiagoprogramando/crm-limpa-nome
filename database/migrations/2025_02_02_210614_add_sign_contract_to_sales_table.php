<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->longText('sign_contract')->nullable()->after('url_contract');
        });
    }

    public function down(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropColumn('sign_contract');
        });
    }
};
