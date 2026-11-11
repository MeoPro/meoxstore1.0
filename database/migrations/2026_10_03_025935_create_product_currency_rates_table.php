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
        Schema::create('product_currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('idr_per_myr', 14, 4)->default(4000);
            $table->decimal('idr_per_usd', 14, 4)->default(15000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_currency_rates');
    }
};
