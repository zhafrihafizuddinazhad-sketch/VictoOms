<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_order_events') || ! Schema::hasColumn('sample_orders', 'completed_at')) {
            return;
        }

        DB::table('sample_orders')
            ->where('status', 'completed')
            ->whereNull('completed_at')
            ->whereExists(function ($events): void {
                $events->selectRaw('1')
                    ->from('sample_order_events')
                    ->whereColumn('sample_order_events.sample_order_id', 'sample_orders.id')
                    ->where('sample_order_events.event_key', 'order_completed');
            })
            ->update([
                'completed_at' => DB::raw(
                    '(select max(sample_order_events.created_at) from sample_order_events '
                    . 'where sample_order_events.sample_order_id = sample_orders.id '
                    . "and sample_order_events.event_key = 'order_completed')"
                ),
            ]);
    }

    public function down(): void
    {
        // Preserve the completion timestamps; they are durable business data.
    }
};
