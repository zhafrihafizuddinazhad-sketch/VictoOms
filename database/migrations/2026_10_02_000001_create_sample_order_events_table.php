<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_order_id')->constrained('sample_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_key', 50);
            $table->timestamps();
            $table->index(['sample_order_id', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_order_events');
    }
};
