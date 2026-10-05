<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('customer_name')->constrained('customers')->nullOnDelete();
            $table->date('delivery_date')->nullable()->after('pickup_date');
            $table->text('delivery_address')->nullable()->after('delivery_date');
            $table->string('created_source', 32)->default('staff')->after('customer_token');
        });
    }

    public function down(): void
    {
        Schema::table('sample_orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'delivery_date', 'delivery_address', 'created_source']);
        });
    }
};
