<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_returns', function (Blueprint $table): void {
            $table->enum('condition', ['good', 'damaged', 'lost', 'other'])->default('good')->change();
            $table->enum('deposit_action', ['refund', 'partial_refund', 'forfeit', 'deduct', 'hold'])->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('sample_returns')->where('condition', 'other')->exists()
            || DB::table('sample_returns')->whereIn('deposit_action', ['deduct', 'hold'])->exists()) {
            throw new RuntimeException('Cannot roll back sample return options while records use the new values.');
        }

        Schema::table('sample_returns', function (Blueprint $table): void {
            $table->enum('condition', ['good', 'damaged', 'lost'])->default('good')->change();
            $table->enum('deposit_action', ['refund', 'partial_refund', 'forfeit'])->nullable()->change();
        });
    }
};
