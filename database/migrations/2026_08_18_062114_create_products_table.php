<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            $table->string('product_name');

            $table->string('category')->nullable();

            $table->decimal('default_price', 10, 2)
                  ->default(0);

            $table->text('description')->nullable();

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};