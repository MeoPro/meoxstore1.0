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
        Schema::table('item_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('item_categories')
                ->restrictOnDelete();
            $table->index('parent_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('item_subcategory_id')
                ->nullable()
                ->constrained('item_categories')
                ->nullOnDelete();
            $table->index('item_subcategory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['item_subcategory_id']);
            $table->dropIndex(['item_subcategory_id']);
            $table->dropColumn('item_subcategory_id');
        });

        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
