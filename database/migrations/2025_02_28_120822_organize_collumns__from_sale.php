<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropForeign('sale_id_payment_foreign');
            $table->dropColumn(['id_payment', ]);
        });
    }

    public function down(): void {
        Schema::table('sale', function (Blueprint $table) {
            
        });
    }
};
