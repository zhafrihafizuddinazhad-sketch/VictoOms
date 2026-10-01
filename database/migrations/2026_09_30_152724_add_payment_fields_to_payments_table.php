<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 10, 2)->after('sample_order_id');
            }
            if (! Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->after('amount');
            }
            if (! Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('pending')->after('payment_method');
            }
            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('payments', 'confirmed_by')) {
                $table->foreignId('confirmed_by')
                    ->nullable()
                    ->after('paid_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('confirmed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);

            $table->dropColumn([
                'amount',
                'payment_method',
                'status',
                'paid_at',
                'confirmed_by',
                'notes',
            ]);
        });
    }
};
