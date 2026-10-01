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
        Schema::table('sample_orders', function (Blueprint $table) {

            // Remove relationship with customers table
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            // Store customer name directly in sample order
            $table->string('customer_name')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_orders', function (Blueprint $table) {

            // Remove customer name
            $table->dropColumn('customer_name');

            // Restore customer relationship
            $table->foreignId('customer_id')
                ->after('id')
                ->constrained('customers')
                ->cascadeOnDelete();
        });
    }
};