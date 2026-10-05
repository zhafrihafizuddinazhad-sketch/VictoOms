<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_items', function (Blueprint $table): void {
            $table->enum('item_type', ['shirt', 'short', 'others'])->change();
            $table->string('sample_name')->nullable()->after('item_type');
        });
    }

    public function down(): void
    {
        if (DB::table('sample_items')->where('item_type', 'others')->exists()) {
            throw new RuntimeException('Cannot remove Others while sample items use that type.');
        }

        Schema::table('sample_items', function (Blueprint $table): void {
            $table->dropColumn('sample_name');
            $table->enum('item_type', ['shirt', 'short'])->change();
        });
    }
};
