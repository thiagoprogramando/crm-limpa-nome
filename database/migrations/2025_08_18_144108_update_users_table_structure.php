<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('users', function (Blueprint $table) {

            $table->renameColumn('wallet', 'token_wallet');
            $table->renameColumn('api_key', 'token_key');
            $table->renameColumn('api_token_zapapi', 'token_whatsapp');

            $table->dropColumn(['wallet_id', 'level']);

            $table->decimal('wallet', 10, 2)->default(0)->after('num');
        });
    }

    public function down(): void {
        
    }
};
