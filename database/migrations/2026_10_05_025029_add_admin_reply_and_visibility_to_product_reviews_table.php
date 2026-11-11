<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->text('admin_reply')->nullable();
            $table->string('admin_reply_image')->nullable();
            $table->boolean('is_hidden')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn(['admin_reply', 'admin_reply_image', 'is_hidden']);
        });
    }
};
