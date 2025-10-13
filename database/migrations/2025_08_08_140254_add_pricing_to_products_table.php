<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Place after actual_price so it stays tidy in DESCRIBE (MySQL)
            $table->boolean('has_discount')->default(false)->index()->after('actual_price');
            $table->enum('discount_type', ['percent', 'fixed'])->nullable()->after('has_discount');
            $table->unsignedInteger('discount_value')->nullable()->after('discount_type');
                        $table->integer('price')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_discount', 'discount_type', 'discount_value','price']);
        });
    }
};
