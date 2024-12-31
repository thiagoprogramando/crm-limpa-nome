<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('white_label_contract')->default(0)->after('fixed_cost');
            $table->string('company_name')->nullable()->after('white_label_contract');
            $table->string('company_cpfcnpj')->nullable()->after('company_name');
            $table->string('company_address')->nullable()->after('company_cpfcnpj');
            $table->string('company_email')->nullable()->after('company_address');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'white_label_contract',
                'company_name',
                'company_cpfcnpj',
                'company_address',
                'company_email',
            ]);
        });
    }
};
