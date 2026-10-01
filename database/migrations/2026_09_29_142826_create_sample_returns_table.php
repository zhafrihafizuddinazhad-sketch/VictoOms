<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sample_order_id')
                ->constrained('sample_orders')
                ->cascadeOnDelete();

            $table->timestamp('returned_at')->nullable();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('condition', [
                'good',
                'damaged',
                'lost'
            ])->default('good');

            $table->text('damage_description')->nullable();

            $table->enum('deposit_action', [
                'refund',
                'partial_refund',
                'forfeit'
            ])->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_returns');
    }
};