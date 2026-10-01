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
        Schema::create('sample_items', function (Blueprint $table) {
    $table->id();

    // Link to sample order
    $table->foreignId('sample_order_id')
        ->constrained('sample_orders')
        ->cascadeOnDelete();

    // Link to physical sample inventory
    $table->foreignId('sample_id')
        ->nullable()
        ->constrained('samples')
        ->nullOnDelete();

    // Requested item
    $table->enum('item_type', [
        'shirt',
        'short'
    ]);

    $table->unsignedInteger('quantity');

    $table->string('fabric')->nullable();

    $table->text('description')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_items');
    }
};
