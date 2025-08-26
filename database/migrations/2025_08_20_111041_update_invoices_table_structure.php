<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {

        Schema::rename('invoice', 'invoices');

       Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('id_user', 'user_id');
            $table->renameColumn('id_product', 'product_id');
            $table->renameColumn('id_sale', 'sale_id');
            $table->renameColumn('commission', 'commission_seller');
            $table->renameColumn('token_payment', 'payment_token');
            $table->renameColumn('url_payment', 'payment_url');

            $table->dropColumn(['notification_number']);

            $table->json('payment_splits')->nullable()->after('payment_url');
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
