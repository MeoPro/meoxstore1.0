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
        Schema::create('item_subcategory_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_category_id')->constrained('item_categories')->cascadeOnDelete();
            $table->primary(['product_id', 'item_category_id']);
            $table->index(['item_category_id', 'product_id']);
        });

        DB::table('products')
            ->select(['id', 'item_subcategory_id'])
            ->whereNotNull('item_subcategory_id')
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                DB::table('item_subcategory_product')->insertOrIgnore(
                    $products->map(fn (object $product): array => [
                        'product_id' => $product->id,
                        'item_category_id' => $product->item_subcategory_id,
                    ])->all(),
                );
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['item_subcategory_id']);
            $table->dropIndex(['item_subcategory_id']);
            $table->dropColumn('item_subcategory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasMultipleSubcategories = DB::table('item_subcategory_product')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleSubcategories) {
            throw new RuntimeException('Products with multiple subcategories cannot be restored to the single-subcategory schema.');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('item_subcategory_id')
                ->nullable()
                ->constrained('item_categories')
                ->nullOnDelete();
            $table->index('item_subcategory_id');
        });

        foreach (DB::table('item_subcategory_product')->orderBy('product_id')->cursor() as $assignment) {
            DB::table('products')
                ->where('id', $assignment->product_id)
                ->update(['item_subcategory_id' => $assignment->item_category_id]);
        }

        Schema::dropIfExists('item_subcategory_product');
    }
};
