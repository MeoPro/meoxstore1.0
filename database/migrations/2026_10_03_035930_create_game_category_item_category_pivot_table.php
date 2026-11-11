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
        Schema::create('game_category_item_category', function (Blueprint $table) {
            $table->foreignId('game_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_category_id', 'item_category_id']);
        });

        DB::table('products')
            ->select(['game_category_id', 'item_category_id'])
            ->distinct()
            ->orderBy('game_category_id')
            ->orderBy('item_category_id')
            ->get()
            ->each(fn (object $pair) => DB::table('game_category_item_category')->insertOrIgnore([
                'game_category_id' => $pair->game_category_id,
                'item_category_id' => $pair->item_category_id,
            ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_category_item_category');
    }
};
