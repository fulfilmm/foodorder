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
        Schema::table('orders', function (Blueprint $table) {
            // Add parent_order_id
            $table->foreignId('parent_order_id')->nullable()
                ->after('table_id')
                ->constrained('orders')
                ->onDelete('cascade');

            // Add has_add_on
            $table->boolean('has_add_on')->default(false)->after('parent_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['parent_order_id']);
            $table->dropColumn(['parent_order_id', 'has_add_on']);
        });
    }
};
