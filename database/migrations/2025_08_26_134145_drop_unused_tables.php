<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::dropIfExists('archive');
        Schema::dropIfExists('archive_start');
        Schema::dropIfExists('photoshop');
        Schema::dropIfExists('product_item');
        Schema::dropIfExists('product_payment');
    }

    public function down(): void {
        
    }
};
