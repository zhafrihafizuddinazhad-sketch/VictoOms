<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('job_order_id')
                ->constrained('job_orders')
                ->cascadeOnDelete();

            $table->string('item_name');

            $table->string('size');

            $table->unsignedInteger('quantity')
                ->default(0);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_items');
    }
};