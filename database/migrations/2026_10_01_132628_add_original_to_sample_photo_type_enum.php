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
        Schema::table('sample_photos', function (Blueprint $table) {
            $table->enum('photo_type', [
                'original',
                'before_handover',
                'before_delivery',
                'customer_received',
                'before_return',
                'before_customer_return',
                'after_return',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_photos', function (Blueprint $table) {
            $table->enum('photo_type', [
                'before_handover',
                'before_delivery',
                'customer_received',
                'before_return',
                'before_customer_return',
                'after_return',
            ])->change();
        });
    }
};