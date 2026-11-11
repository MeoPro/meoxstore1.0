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
        Schema::table('product_currency_rates', function (Blueprint $table): void {
            $table->boolean('bobux_gift_ingame_enabled')->default(true);
            $table->boolean('bobux_gamepass_5_days_enabled')->default(true);
            $table->boolean('bobux_login_enabled')->default(true);
        });

        DB::table('product_currency_rates')->update([
            'bobux_gift_ingame_enabled' => DB::raw('robux_enabled'),
            'bobux_gamepass_5_days_enabled' => DB::raw('robux_enabled'),
            'bobux_login_enabled' => DB::raw('robux_enabled'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_currency_rates', function (Blueprint $table): void {
            $table->dropColumn([
                'bobux_gift_ingame_enabled',
                'bobux_gamepass_5_days_enabled',
                'bobux_login_enabled',
            ]);
        });
    }
};
