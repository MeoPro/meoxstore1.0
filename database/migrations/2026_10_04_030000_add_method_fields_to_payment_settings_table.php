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
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->string('type')->default('bank_transfer');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
        });

        DB::table('payment_settings')
            ->whereNull('name')
            ->whereNotNull('bank_name')
            ->update(['name' => DB::raw('bank_name')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->dropColumn(['type', 'name', 'is_active']);
        });
    }
};
