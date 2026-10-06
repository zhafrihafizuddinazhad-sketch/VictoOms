<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sample;
use App\Models\SampleOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CleanupCompletedSampleOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-06 12:00:00'));
        Storage::fake('local');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function order(array $overrides = []): SampleOrder
    {
        static $sequence = 0;

        return SampleOrder::create(array_replace([
            'customer_name' => 'Cleanup Test Customer',
            'customer_id' => null,
            'order_number' => 'SO-CLEANUP-' . ++$sequence,
            'collection_method' => 'office',
            'pickup_date' => '2026-10-01',
            'return_date' => '2026-10-05',
            'deposit_amount' => 0,
            'deposit_status' => 'paid',
            'status' => 'pending_payment',
            'customer_token' => 'cleanup-test-token-' . $sequence,
            'created_source' => 'staff',
        ], $overrides));
    }

    private function runCleanup(): void
    {
        $this->artisan('samples:cleanup-completed');
    }

    public function test_completed_order_31_days_old_is_deleted_with_only_its_dependent_data(): void
    {
        $customer = Customer::create([
            'customer_name' => 'Retained Customer',
            'phone' => '0123456789',
        ]);
        $sample = Sample::create([
            'sample_code' => 'KEEP-001',
            'item_type' => 'shirt',
            'status' => 'available',
        ]);
        $order = $this->order([
            'customer_id' => $customer->id,
            'status' => 'completed',
            'completed_at' => now()->subDays(31),
        ]);
        $item = $order->sampleItems()->create([
            'sample_id' => $sample->id,
            'item_type' => 'shirt',
            'quantity' => 1,
        ]);
        $payment = $order->payments()->create([
            'amount' => 50,
            'status' => 'paid',
        ]);
        $return = $order->sampleReturn()->create([
            'returned_at' => now()->subDays(31),
            'condition' => 'good',
        ]);
        $event = $order->events()->create(['event_key' => 'order_completed']);
        $photoPath = "sample-orders/{$order->id}/photos/after-return.jpg";
        Storage::disk('local')->put($photoPath, 'photo');
        $photo = $order->photos()->create([
            'photo_type' => 'after_return',
            'file_path' => $photoPath,
        ]);

        $this->artisan('samples:cleanup-completed')
            ->expectsOutput('Deleted 1 completed sample orders older than 30 days.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('sample_orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('sample_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('sample_returns', ['id' => $return->id]);
        $this->assertDatabaseMissing('sample_order_events', ['id' => $event->id]);
        $this->assertDatabaseMissing('sample_photos', ['id' => $photo->id]);
        $this->assertDatabaseHas('samples', ['id' => $sample->id, 'status' => 'available']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        Storage::disk('local')->assertMissing($photoPath);
    }

    public function test_cutoff_is_inclusive_and_recent_or_undated_completed_orders_remain(): void
    {
        $cutoff = now()->subDays(30);
        $atCutoff = $this->order(['status' => 'completed', 'completed_at' => $cutoff]);
        $beforeCutoff = $this->order(['status' => 'completed', 'completed_at' => $cutoff->copy()->subSecond()]);
        $recent = $this->order(['status' => 'completed', 'completed_at' => now()->subDays(29)]);
        $undated = $this->order(['status' => 'completed', 'completed_at' => null]);
        DB::table('sample_orders')->where('id', $undated->id)->update(['completed_at' => null]);

        $this->artisan('samples:cleanup-completed')->assertExitCode(0);

        $this->assertDatabaseMissing('sample_orders', ['id' => $atCutoff->id]);
        $this->assertDatabaseMissing('sample_orders', ['id' => $beforeCutoff->id]);
        $this->assertDatabaseHas('sample_orders', ['id' => $recent->id]);
        $this->assertDatabaseHas('sample_orders', ['id' => $undated->id]);
    }

    public function test_old_active_order_is_never_deleted(): void
    {
        $active = $this->order([
            'status' => 'return_pending',
            'created_at' => now()->subDays(60),
        ]);

        $this->artisan('samples:cleanup-completed')->assertExitCode(0);

        $this->assertDatabaseHas('sample_orders', ['id' => $active->id]);
    }

    public function test_cleanup_with_no_eligible_orders_and_repeated_runs_are_safe(): void
    {
        $this->artisan('samples:cleanup-completed')
            ->expectsOutput('No completed sample orders are eligible for deletion.')
            ->assertExitCode(0);
        $this->artisan('samples:cleanup-completed')->assertExitCode(0);
    }

    public function test_transition_to_completed_sets_timestamp_and_later_edits_do_not_reset_it(): void
    {
        $order = $this->order(['status' => 'returned']);

        $order->update(['status' => 'completed']);
        $completionTime = $order->fresh()->completed_at;

        $this->assertNotNull($completionTime);
        $this->assertTrue($completionTime->equalTo(now()));

        Carbon::setTestNow(now()->addDays(5));
        $order->update(['notes' => 'Updated after completion']);

        $this->assertTrue($order->fresh()->completed_at->equalTo($completionTime));
    }

    public function test_second_cleanup_after_deletion_succeeds_without_error(): void
    {
        $order = $this->order(['status' => 'completed', 'completed_at' => now()->subDays(31)]);

        $this->artisan('samples:cleanup-completed')->assertExitCode(0);
        $this->assertDatabaseMissing('sample_orders', ['id' => $order->id]);

        $this->artisan('samples:cleanup-completed')
            ->expectsOutput('No completed sample orders are eligible for deletion.')
            ->assertExitCode(0);
    }

    public function test_migration_backfills_completion_time_from_the_order_completed_event(): void
    {
        $completedAt = now()->subDays(31)->startOfSecond();
        $order = $this->order(['status' => 'completed', 'completed_at' => $completedAt]);
        DB::table('sample_orders')->where('id', $order->id)->update(['completed_at' => null]);
        DB::table('sample_order_events')->insert([
            'sample_order_id' => $order->id,
            'event_key' => 'order_completed',
            'user_id' => null,
            'created_at' => $completedAt,
            'updated_at' => $completedAt,
        ]);

        $migration = require database_path('migrations/2026_10_06_000002_backfill_sample_order_completed_at_from_events.php');
        $migration->up();

        $this->assertTrue($order->fresh()->completed_at->equalTo($completedAt));
    }
}
