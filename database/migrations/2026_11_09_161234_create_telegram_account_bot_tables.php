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
        Schema::create('telegram_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_update_id')->unique();
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('telegram_account_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_user_id', 32);
            $table->string('chat_id', 32);
            $table->string('status')->default('active');
            $table->string('step')->default('photos');
            $table->json('data');
            $table->json('images');
            $table->json('processed_message_ids');
            $table->unsignedBigInteger('last_update_id')->nullable();
            $table->unsignedInteger('channel_media_offset')->default(0);
            $table->boolean('channel_text_sent')->default(false);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['telegram_user_id', 'status']);
        });

        Schema::create('telegram_account_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30);
            $table->string('name', 80);
            $table->string('emoji', 32)->nullable();
            $table->timestamps();
            $table->unique(['category', 'name']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_account_items');
        Schema::dropIfExists('telegram_account_drafts');
        Schema::dropIfExists('telegram_updates');
    }
};
