<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            // FK to taxes (nullable)
            $table->foreignId('tax_id')->nullable()
                  ->constrained('taxes')->nullOnDelete()->after('status');

            // snapshot the tax used (name + percent) to keep history correct
            $table->string('tax_name_snapshot')->nullable()->after('tax_id');
            $table->unsignedDecimal('tax_percent_snapshot', 5, 2)->nullable()->after('tax_name_snapshot');

            // pricing breakdown (add if missing in your schema)
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->unsignedInteger('subtotal')->default(0)->after('tax_percent_snapshot');
            }
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->unsignedInteger('tax_amount')->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'total')) {
                $table->unsignedInteger('total')->default(0)->after('tax_amount');
            }
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn(['tax_id','tax_name_snapshot','tax_percent_snapshot']);
            // Leave subtotal/tax_amount/total if you already used them
        });
    }
};
