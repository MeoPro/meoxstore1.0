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
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('cashier_actual_revenue', 12, 2)->nullable();
            $table->timestamp('cashier_history_deleted_at')->nullable();
        });

        DB::table('orders')
            ->where('source', 'cashier')
            ->update(['cashier_actual_revenue' => DB::raw('total')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['cashier_actual_revenue', 'cashier_history_deleted_at']);
        });
    }
};
