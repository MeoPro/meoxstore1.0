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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('source', 20)->default('storefront')->after('status');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->dropColumn('is_anonymous');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('source');
        });
    }
};
