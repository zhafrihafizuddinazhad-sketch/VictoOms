<?php

namespace Tests\Feature;

use App\Models\SampleOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SampleOrderCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($user);
    }

    public function test_staff_can_create_sample_order_and_system_generates_order_number_and_customer_token(): void
    {
        $response = $this->post(route('sample-orders.store'), [
            'customer_name' => 'Zhafri',
            'collection_method' => 'office',
            'pickup_date' => '2026-09-30',
            'return_date' => '2026-10-03',
            'deposit_amount' => 50,
            'notes' => 'Corporate clothing samples',
            'order_number' => 'USER-SUPPLIED',
            'customer_token' => 'user-supplied-token',
            'deposit_status' => 'paid',
        ]);

        $order = SampleOrder::firstOrFail();
        $response->assertRedirect(route('sample-orders.show', $order));
        $this->assertStringStartsWith('SO-', $order->order_number);
        $this->assertNotSame('USER-SUPPLIED', $order->order_number);
        $this->assertNotSame('user-supplied-token', $order->customer_token);
        $this->assertSame('pending', $order->deposit_status);
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('Zhafri', $order->customer_name);
        $this->assertSame('office', $order->collection_method);
        $this->assertSame('50.00', $order->deposit_amount);
        $this->assertSame('Corporate clothing samples', $order->notes);

        $this->get(route('sample-orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Zhafri')
            ->assertSee('Office')
            ->assertSee('30 Sep 2026')
            ->assertSee('03 Oct 2026')
            ->assertSee('RM 50.00')
            ->assertSee('Pending');
    }

    public function test_collection_uses_existing_schema_values_and_return_date_must_not_precede_pickup(): void
    {
        $valid = [
            'customer_name' => 'Test Customer',
            'collection_method' => 'lalamove',
            'pickup_date' => '2026-10-03',
            'return_date' => '2026-10-02',
            'deposit_amount' => 0,
        ];

        $this->from(route('sample-orders.create'))
            ->post(route('sample-orders.store'), $valid)
            ->assertSessionHasErrors(['return_date']);

        $valid['collection_method'] = 'courier';
        $valid['return_date'] = '2026-10-04';
        $this->from(route('sample-orders.create'))
            ->post(route('sample-orders.store'), $valid)
            ->assertSessionHasErrors(['collection_method']);

        $this->assertDatabaseCount('sample_orders', 0);
    }

    public function test_order_list_searches_references_and_filters_upcoming_returns(): void
    {
        $due = SampleOrder::create([
            'customer_name' => 'Searchable Customer', 'order_number' => 'SO-SEARCH-100',
            'collection_method' => 'lalamove', 'pickup_date' => today()->subDays(2),
            'return_date' => today()->addDays(3), 'deposit_amount' => 20,
            'deposit_status' => 'pending', 'status' => 'in_transit', 'customer_token' => 'search-token',
        ]);
        SampleOrder::create([
            'customer_name' => 'Other Customer', 'order_number' => 'SO-OLD-200',
            'collection_method' => 'office', 'pickup_date' => today()->subDays(10),
            'return_date' => today()->subDays(2), 'deposit_amount' => 0,
            'deposit_status' => 'paid', 'status' => 'collected', 'customer_token' => 'old-token',
        ]);

        $this->get(route('sample-orders.index', ['search' => 'SO-SEARCH', 'return_due' => 'upcoming']))
            ->assertOk()->assertSee($due->order_number)->assertDontSee('SO-OLD-200')
            ->assertSee('Returns in 7 days')->assertSee('Overdue returns');
        $this->get(route('sample-orders.index', ['return_due' => 'overdue']))
            ->assertOk()->assertSee('SO-OLD-200')->assertDontSee('SO-SEARCH-100');
    }
}
