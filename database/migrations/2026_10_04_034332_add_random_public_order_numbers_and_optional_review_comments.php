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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('public_number', 17)->nullable()->unique();
        });

        DB::table('orders')->select('id')->orderBy('id')->chunkById(100, function ($orders): void {
            foreach ($orders as $order) {
                do {
                    $publicNumber = 'MEOX-'.Str::upper(Str::random(12));
                } while (DB::table('orders')->where('public_number', $publicNumber)->exists());

                DB::table('orders')->where('id', $order->id)->update([
                    'public_number' => $publicNumber,
                ]);
            }
        });

        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->text('comment')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->text('comment')->nullable(false)->change();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['public_number']);
            $table->dropColumn('public_number');
        });
    }
};
