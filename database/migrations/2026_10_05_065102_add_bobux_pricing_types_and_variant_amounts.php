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
        Schema::table('item_categories', function (Blueprint $table): void {
            $table->string('bobux_type', 32)->nullable();
        });

        DB::table('item_categories')
            ->where('uses_robux', true)
            ->update(['bobux_type' => 'gift_ingame']);

        Schema::table('product_currency_rates', function (Blueprint $table): void {
            $table->decimal('idr_per_gamepass_bobux', 14, 4)->default(150);
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->decimal('bobux_amount', 14, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn('bobux_amount');
        });

        Schema::table('product_currency_rates', function (Blueprint $table): void {
            $table->dropColumn('idr_per_gamepass_bobux');
        });

        Schema::table('item_categories', function (Blueprint $table): void {
            $table->dropColumn('bobux_type');
        });
    }
};
