<?php

namespace Tests\Feature;

use App\Models\Sample;
use App\Models\SampleItem;
use App\Models\SampleOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SampleItemManagementTest extends TestCase
{
    use RefreshDatabase;

    private function sampleOrder(): SampleOrder
    {
        return SampleOrder::create([
            'customer_name' => 'Zhafri',
            'order_number' => 'SO-TEST-0001',
            'collection_method' => 'office',
            'pickup_date' => '2026-09-30',
            'return_date' => '2026-10-03',
            'deposit_amount' => 50,
            'deposit_status' => 'pending',
            'status' => 'pending_payment',
            'customer_token' => 'sample-order-test-token',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($user);
    }

    public function test_staff_can_add_and_view_multiple_sample_items(): void
    {
        $order = $this->sampleOrder();
        $sample = Sample::create(['sample_code' => 'SMP-001', 'item_type' => 'shirt', 'status' => 'available']);

        $this->post(route('sample-items.store', $order), [
            'item_type' => 'shirt', 'quantity' => 2, 'fabric' => 'Cotton',
            'description' => 'Black corporate shirt', 'sample_id' => $sample->id,
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->post(route('sample-items.store', $order), [
            'item_type' => 'short', 'quantity' => 1, 'fabric' => 'Microfiber',
            'description' => 'Navy sample short',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertDatabaseCount('sample_items', 2);
        $this->assertDatabaseHas('sample_items', [
            'sample_order_id' => $order->id,
            'sample_id' => $sample->id,
            'item_type' => 'shirt',
            'quantity' => 2,
        ]);
        $this->get(route('sample-orders.show', $order))
            ->assertOk()
            ->assertSee('Black corporate shirt')
            ->assertSee('Navy sample short');
    }

    public function test_staff_can_edit_and_delete_an_item_but_cannot_change_its_order(): void
    {
        $order = $this->sampleOrder();
        $item = $order->sampleItems()->create(['item_type' => 'shirt', 'quantity' => 1]);

        $this->put(route('sample-items.update', $item), [
            'item_type' => 'short', 'quantity' => 3, 'fabric' => 'Cotton',
            'description' => 'Updated item', 'sample_order_id' => 999999,
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertDatabaseHas('sample_items', [
            'id' => $item->id, 'sample_order_id' => $order->id,
            'item_type' => 'short', 'quantity' => 3,
        ]);

        $this->delete(route('sample-items.destroy', $item))
            ->assertRedirect(route('sample-orders.show', $order));
        $this->assertDatabaseMissing('sample_items', ['id' => $item->id]);
    }

    public function test_item_validation_requires_supported_type_and_positive_quantity(): void
    {
        $order = $this->sampleOrder();

        $this->from(route('sample-items.create', $order))
            ->post(route('sample-items.store', $order), ['item_type' => 'jacket', 'quantity' => 0])
            ->assertSessionHasErrors(['item_type', 'quantity']);

        $this->assertDatabaseCount('sample_items', 0);
    }

    public function test_other_item_requires_and_saves_sample_name(): void
    {
        $order = $this->sampleOrder();
        $this->from(route('sample-items.create', $order))
            ->post(route('sample-items.store', $order), ['item_type' => 'others', 'quantity' => 1])
            ->assertSessionHasErrors(['sample_name']);

        $this->post(route('sample-items.store', $order), [
            'item_type' => 'others', 'sample_name' => 'Display Stand', 'quantity' => 1,
            'description' => 'Counter display stand',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertDatabaseHas('sample_items', [
            'sample_order_id' => $order->id, 'item_type' => 'others',
            'sample_name' => 'Display Stand', 'quantity' => 1,
        ]);
        $this->get(route('sample-orders.show', $order))->assertOk()->assertSee('Display Stand');
    }
}
