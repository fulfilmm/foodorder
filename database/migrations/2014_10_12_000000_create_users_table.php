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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('otp')->nullable();
            $table->string('password');
            $table->enum('role', ['customer', 'waiter', 'kitchen', 'manager', 'admin'])->default('customer')->index();
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent(); // Defaults to current timestamp on creation
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Defaults to current and updates on modification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
