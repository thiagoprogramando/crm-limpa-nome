<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {

    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
                $table->string('token_provider')->nullable()->after('token_key');
                $table->string('token_association')->nullable()->after('token_provider');
            });

            DB::table('users')->whereNull('uuid')->get()->each(function ($user) {
                DB::table('users')->where('id', $user->id)->update(['uuid' => (string) Str::uuid()]);
            });
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        });
    }
};
