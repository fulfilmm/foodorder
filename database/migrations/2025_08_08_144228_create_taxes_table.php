<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();                    // e.g. "No tax", "VAT 5%"
            $table->unsignedDecimal('percent', 5, 2)->default(0);// 0.00 - 100.00
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('taxes');
    }
};
