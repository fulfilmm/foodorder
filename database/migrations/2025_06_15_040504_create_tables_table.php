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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('available');
            $table->string('qr_path')->nullable();
            $table->timestamp('created_at')->useCurrent(); // Defaults to current timestamp on creation
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Defaults to current and updates on modification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
