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
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('translation_group_id', 36)->nullable();
            $table->string('locale', 5)->default('id');
            $table->string('source_locale', 5)->default('id');
        });

        DB::table('blog_posts')
            ->whereNull('translation_group_id')
            ->orderBy('id')
            ->chunkById(100, function ($blogPosts): void {
                foreach ($blogPosts as $blogPost) {
                    DB::table('blog_posts')
                        ->where('id', $blogPost->id)
                        ->update(['translation_group_id' => (string) Str::uuid()]);
                }
            });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('translation_group_id', 36)->nullable(false)->change();
            $table->unique(['translation_group_id', 'locale']);
            $table->index(['locale', 'status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique(['translation_group_id', 'locale']);
            $table->dropIndex(['locale', 'status', 'published_at']);
            $table->dropColumn(['translation_group_id', 'locale', 'source_locale']);
        });
    }
};
