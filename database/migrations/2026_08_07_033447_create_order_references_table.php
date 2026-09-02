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
        Schema::create('order_references', function (Blueprint $table) {

    $table->id();

    $table->foreignId('order_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('uploaded_by')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->string('title')->nullable();

    $table->text('description')->nullable();

    $table->text('reference_link')->nullable();

    $table->string('file_name')->nullable();

    $table->string('file_path')->nullable();

    $table->string('file_extension')->nullable();

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_references');
    }
};
