<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 40)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('orders')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Guest orders must be migrated before this migration can be rolled back.');
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['contact_email', 'contact_phone']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
