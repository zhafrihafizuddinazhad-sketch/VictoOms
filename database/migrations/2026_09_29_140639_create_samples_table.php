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
        Schema::create('samples', function (Blueprint $table) {
    $table->id();

    // Unique code for each physical sample
    $table->string('sample_code')->unique();

    // Basic sample information
    $table->enum('item_type', [
        'shirt',
        'short'
    ]);

    $table->string('fabric')->nullable();
    $table->string('size')->nullable();
    $table->string('colour')->nullable();

    // Current availability of the physical sample
    $table->enum('status', [
        'available',
        'reserved',
        'borrowed',
        'maintenance',
        'lost'
    ])->default('available');

    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
