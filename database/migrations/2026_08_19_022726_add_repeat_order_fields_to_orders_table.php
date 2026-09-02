<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->boolean('is_repeat_order')
                ->default(false)
                ->after('id');

            $table->foreignId('repeat_from_order_id')
                ->nullable()
                ->after('is_repeat_order')
                ->constrained('orders')
                ->nullOnDelete();

            $table->string('repeat_type')
                ->nullable()
                ->after('repeat_from_order_id');

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign([
                'repeat_from_order_id'
            ]);

            $table->dropColumn([
                'is_repeat_order',
                'repeat_from_order_id',
                'repeat_type',
            ]);

        });
    }
};