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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('phone')->nullable();
            $table->date('pickup_date');
            $table->string('pickup_time');
            $table->enum('order_type', ['dine_in', 'takeaway'])->default('takeaway');
            $table->foreignId('table_id')->nullable()->constrained('tables')->onDelete('set null'); // Only used for dine-in
            $table->enum('status', ['preparing', 'pending', 'confirmed', 'delivered', 'eating', 'done', 'canceled'])->default('preparing');
            $table->integer('total');
            $table->timestamp('created_at')->useCurrent(); // Defaults to current timestamp on creation
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Defaults to current and updates on modification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
