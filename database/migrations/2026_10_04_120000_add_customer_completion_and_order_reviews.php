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
            $table->timestamp('customer_completed_at')->nullable();
        });

        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('product_reviews')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Guest reviews must be migrated before this migration can be rolled back.');
        }

        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->dropUnique(['order_id', 'product_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('customer_completed_at');
        });
    }
};
