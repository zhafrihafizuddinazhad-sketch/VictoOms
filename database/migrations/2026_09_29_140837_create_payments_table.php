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
        Schema::create('payments', function (Blueprint $table) {
    $table->id();

    // Link to sample order
    $table->foreignId('sample_order_id')
        ->constrained('sample_orders')
        ->cascadeOnDelete();

    // Payment information
    $table->decimal('amount', 10, 2);

    $table->enum('payment_method', [
        'cash',
        'bank_transfer',
        'online'
    ])->nullable();

    $table->enum('status', [
        'pending',
        'paid',
        'failed',
        'refunded'
    ])->default('pending');

    $table->timestamp('paid_at')->nullable();

    // VictoOMS user who confirmed the payment
    $table->foreignId('confirmed_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
