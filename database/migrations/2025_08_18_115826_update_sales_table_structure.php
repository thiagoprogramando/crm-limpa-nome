<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::rename('sale', 'sales');

        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('id_product', 'product_id');
            $table->renameColumn('id_list', 'list_id');
            $table->renameColumn('id_client', 'client_id');
            $table->renameColumn('id_seller', 'seller_id');

            $table->dropColumn(['url_contract', 'sign_contract', 'status_contract']);
            $table->dropColumn(['payment', 'installments', 'token_payment']);
            $table->dropColumn(['value', 'value_total', 'commission', 'commission_filiate', 'wallet_off']);

            $table->longText('contract_sign')->nullable()->after('seller_id');
            $table->longText('contract_url')->nullable()->after('contract_sign');

            $table->string('payment_token')->nullable()->after('contract_url');
            $table->string('payment_method')->nullable()->after('payment_token');
            $table->integer('payment_installments')->default(1)->after('payment_method');
            $table->tinyInteger('status_contract')->default(0)->after('payment_installments');
        });
    }

    public function down(): void {
        Schema::rename('sales', 'sale');

        Schema::table('sale', function (Blueprint $table) {

            $table->renameColumn('product_id', 'id_product');
            $table->renameColumn('list_id', 'id_list');
            $table->renameColumn('client_id', 'id_client');
            $table->renameColumn('seller_id', 'id_seller');

            $table->dropColumn([
                'contract_sign',
                'contract_url',
                'payment_token',
                'payment_method',
                'payment_installments',
                'status_contract'
            ]);

            $table->string('url_contract')->nullable();
            $table->string('sign_contract')->nullable();
            $table->string('payment')->nullable();
            $table->integer('installments')->default(1);
            $table->string('token_payment')->nullable();
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('value_total', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('commission_filiate', 10, 2)->default(0);
            $table->string('wallet_of')->nullable();
        });
    }
};
