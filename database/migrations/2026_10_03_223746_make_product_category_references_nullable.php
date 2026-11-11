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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['game_category_id']);
            $table->dropForeign(['item_category_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('game_category_id')->nullable()->change();
            $table->unsignedBigInteger('item_category_id')->nullable()->change();
            $table->foreign('game_category_id')->references('id')->on('game_categories')->restrictOnDelete();
            $table->foreign('item_category_id')->references('id')->on('item_categories')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['game_category_id']);
            $table->dropForeign(['item_category_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('game_category_id')->nullable(false)->change();
            $table->unsignedBigInteger('item_category_id')->nullable(false)->change();
            $table->foreign('game_category_id')->references('id')->on('game_categories')->restrictOnDelete();
            $table->foreign('item_category_id')->references('id')->on('item_categories')->restrictOnDelete();
        });
    }
};
