<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('job_order_no')
                ->unique();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status')
                ->default('Draft');

            $table->string('file_path')
                ->nullable();

            $table->string('file_name')
                ->nullable();

            $table->timestamp('generated_at')
                ->nullable();

            $table->timestamp('submitted_at')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};