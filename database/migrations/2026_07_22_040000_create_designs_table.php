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
    Schema::create('designs', function (Blueprint $table) {

        $table->id();

        // Order yang sedang direka
        $table->foreignId('order_id')
              ->constrained()
              ->cascadeOnDelete();

        // Designer yang buat design
        $table->foreignId('designer_id')
              ->constrained('users')
              ->cascadeOnDelete();
        $table->string('design_name');
        
        // Nama fail design
        $table->string('design_file')->nullable();

        // Catatan designer
        $table->text('remarks')->nullable();

        // Tarikh design siap
        $table->timestamp('completed_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
