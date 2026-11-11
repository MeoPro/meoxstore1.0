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
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasDuplicateUserProductReviews = DB::table('product_reviews')
            ->select('product_id', 'user_id')
            ->whereNotNull('user_id')
            ->groupBy('product_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateUserProductReviews) {
            throw new RuntimeException('Multiple reviews per user and product must be resolved before this migration can be rolled back.');
        }

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->unique(['product_id', 'user_id']);
        });
    }
};
