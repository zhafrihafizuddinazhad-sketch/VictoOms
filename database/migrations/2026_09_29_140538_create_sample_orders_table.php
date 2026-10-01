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
        Schema::create('sample_orders', function (Blueprint $table) {
    $table->id();

    // Link to existing VictoOMS customer
    $table->foreignId('customer_id')
        ->constrained('customers')
        ->cascadeOnDelete();

    // Order identification
    $table->string('order_number')->unique();

    // How customer will receive the samples
    $table->enum('collection_method', [
        'office',
        'lalamove'
    ]);

    // Important dates
    $table->date('pickup_date')->nullable();
    $table->date('return_date')->nullable();

    // Deposit
    $table->decimal('deposit_amount', 10, 2)->default(0);
    $table->enum('deposit_status', [
        'pending',
        'paid',
        'refunded',
        'forfeited'
    ])->default('pending');

    // Overall order status
    $table->enum('status', [
        'pending',
        'pending_payment',
        'ready_for_collection',
        'collected',
        'in_transit',
        'received',
        'return_pending',
        'returned',
        'completed',
        'cancelled'
    ])->default('pending');

    // Customer access without login
    $table->string('customer_token')->unique();

    // Optional notes
    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_orders');
    }
};
