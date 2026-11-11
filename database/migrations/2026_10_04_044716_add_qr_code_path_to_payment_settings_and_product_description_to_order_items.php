<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->string('qr_code_path')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->text('product_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('product_description');
        });

        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->dropColumn('qr_code_path');
        });

    }
};
