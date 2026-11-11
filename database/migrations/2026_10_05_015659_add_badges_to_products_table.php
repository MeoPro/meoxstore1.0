<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('badges')->nullable();
        });

        DB::table('products')
            ->select(['id', 'is_featured', 'discount_percent'])
            ->orderBy('id')
            ->chunkById(100, function (Collection $products): void {
                foreach ($products as $product) {
                    $badges = [];

                    if ((bool) $product->is_featured) {
                        $badges[] = 'trending';
                    }

                    if ((float) ($product->discount_percent ?? 0) > 0) {
                        $badges[] = 'discount';
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['badges' => json_encode($badges, JSON_THROW_ON_ERROR)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('badges');
        });
    }
};
