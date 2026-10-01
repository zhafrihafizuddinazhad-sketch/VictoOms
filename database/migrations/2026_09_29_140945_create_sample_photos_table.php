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
        Schema::create('sample_photos', function (Blueprint $table) {
    $table->id();

    // Link to sample order
    $table->foreignId('sample_order_id')
        ->constrained('sample_orders')
        ->cascadeOnDelete();

    // Type of evidence photo
    $table->enum('photo_type', [
        'before_handover',
        'customer_received',
        'before_return',
        'after_return'
    ]);

    // Who uploaded the photo
    $table->foreignId('uploaded_by_user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->boolean('uploaded_by_customer')
        ->default(false);

    // Actual stored file path
    $table->string('file_path');

    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_photos');
    }
};
