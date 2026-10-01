<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payments', 'sample_order_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('sample_order_id')
                ->nullable()
                ->after('id')
                ->constrained('sample_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('payments', 'sample_order_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['sample_order_id']);
            $table->dropColumn('sample_order_id');
        });
    }
};
