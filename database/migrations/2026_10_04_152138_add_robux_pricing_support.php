<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_currency_rates', function (Blueprint $table): void {
            $table->decimal('idr_per_robux', 14, 4)->default(120)->after('idr_per_usd');
        });

        Schema::table('item_categories', function (Blueprint $table): void {
            $table->boolean('uses_robux')->default(false)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('item_categories', function (Blueprint $table): void {
            $table->dropColumn('uses_robux');
        });

        Schema::table('product_currency_rates', function (Blueprint $table): void {
            $table->dropColumn('idr_per_robux');
        });
    }
};
