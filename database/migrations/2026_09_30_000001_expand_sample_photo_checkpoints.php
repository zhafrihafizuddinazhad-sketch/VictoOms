<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $checkpoints = [
        'before_handover',
        'before_delivery',
        'customer_received',
        'before_return',
        'before_customer_return',
        'after_return',
    ];

    public function up(): void
    {
        Schema::table('sample_photos', function (Blueprint $table): void {
            $table->enum('photo_type', $this->checkpoints)->change();
        });
    }

    public function down(): void
    {
        DB::table('sample_photos')
            ->where('photo_type', 'before_delivery')
            ->update(['photo_type' => 'before_handover']);
        DB::table('sample_photos')
            ->where('photo_type', 'before_customer_return')
            ->update(['photo_type' => 'before_return']);

        Schema::table('sample_photos', function (Blueprint $table): void {
            $table->enum('photo_type', [
                'before_handover',
                'customer_received',
                'before_return',
                'after_return',
            ])->change();
        });
    }
};
