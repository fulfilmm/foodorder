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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('actual_price')->default(0);
            $table->integer('qty')->default(0);
            $table->integer('remain_qty')->default(0);
            $table->integer('sell_qty')->default(0);
            $table->string('image')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent(); // Defaults to current timestamp on creation
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Defaults to current and updates on modification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
