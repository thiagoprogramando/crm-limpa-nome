<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('list', function (Blueprint $table) {
            $table->dateTime('start')->change();
            $table->dateTime('end')->change();
        });
    }
    
    public function down(): void
    {
        Schema::table('list', function (Blueprint $table) {
            $table->date('start')->change();
            $table->date('end')->change();
        });
    }
    
};
