<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        
        Schema::rename('product', 'products');

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('address', 'request_address');
            
            $table->dropColumn('active');

            $table->dropColumn(['contract', 'terms', 'terms_text', 'createuser']);
            $table->boolean('request_contract')->default(false)->after('request_address');
            $table->boolean('request_selfie')->default(false)->after('request_contract');
            $table->boolean('request_contact')->default(false)->after('request_selfie');
            $table->boolean('request_serasa')->default(false)->after('request_contact');
            $table->boolean('request_spc')->default(false)->after('request_serasa');
            $table->boolean('request_boa_vista')->default(false)->after('request_spc');
            $table->boolean('request_no_document')->default(false)->after('request_boa_vista');
            $table->boolean('status')->default(true)->after('request_no_document');
        });
    }


    public function down(): void {
        Schema::rename('products', 'product');

        Schema::table('product', function (Blueprint $table) {

            $table->renameColumn('request_address', 'address');
            
            $table->boolean('active')->default(true);
            $table->string('token_contract')->nullable();
            $table->string('terms')->nullable();
            $table->text('terms_text')->nullable();

            $table->dropColumn([
                'request_selfie',
                'request_contact',
                'request_serasa',
                'request_spc',
                'request_boa_vista',
                'status'
            ]);
        });
    }
};
