<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['game_categories', 'item_categories', 'products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('slug')->nullable();
            });

            $usedSlugs = [];
            DB::table($tableName)->select(['id', 'name'])->orderBy('id')->get()->each(
                function (object $record) use ($tableName, &$usedSlugs): void {
                    $baseSlug = Str::slug($record->name) ?: match ($tableName) {
                        'game_categories' => 'game',
                        'item_categories' => 'category',
                        default => 'product',
                    };
                    if (ctype_digit($baseSlug)) {
                        $baseSlug = match ($tableName) {
                            'game_categories' => 'game-'.$baseSlug,
                            'item_categories' => 'category-'.$baseSlug,
                            default => 'product-'.$baseSlug,
                        };
                    }

                    $slug = $baseSlug;
                    $suffix = 2;
                    while (isset($usedSlugs[$slug])) {
                        $slug = $baseSlug.'-'.$suffix;
                        $suffix++;
                    }
                    $usedSlugs[$slug] = true;

                    DB::table($tableName)->where('id', $record->id)->update(['slug' => $slug]);
                },
            );

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unique('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['products', 'item_categories', 'game_categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};
