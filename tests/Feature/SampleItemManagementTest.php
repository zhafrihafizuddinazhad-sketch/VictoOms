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

    private function sampleOrder(string $number = 'SO-TEST-0001', string $method = 'office', string $status = 'pending_payment'): SampleOrder
    {
        return SampleOrder::create([
            'customer_name' => 'Zhafri',
            'order_number' => $number,
            'collection_method' => $method,
            'pickup_date' => '2026-09-30',
            'return_date' => '2026-10-03',
            'deposit_amount' => 50,
            'deposit_status' => 'pending',
            'status' => $status,
            'customer_token' => $number . '-token',
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

    public function test_office_item_remains_editable_before_collection(): void
    {
        $order = $this->sampleOrder('SO-OFFICE-READY', 'office', 'ready_for_collection');
        $item = $order->sampleItems()->create(['item_type' => 'shirt', 'quantity' => 4]);

        $this->put(route('sample-items.update', $item), [
            'item_type' => 'shirt', 'quantity' => 2, 'fabric' => 'Microfiber',
            'description' => 'Corrected before handover',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame(2, $item->fresh()->quantity);
    }

    public function test_collected_office_items_cannot_be_edited_deleted_or_added_and_ui_is_read_only(): void
    {
        $order = $this->sampleOrder('SO-OFFICE-COLLECTED', 'office', 'collected');
        $item = $order->sampleItems()->create([
            'item_type' => 'shirt', 'quantity' => 4, 'fabric' => 'Microfiber', 'description' => 'Handed over',
        ]);
        $changes = ['item_type' => 'short', 'quantity' => 2, 'fabric' => 'Cotton', 'description' => 'Changed'];

        $this->put(route('sample-items.update', $item), $changes)->assertForbidden();
        $this->patch(route('sample-items.update', $item), $changes)->assertMethodNotAllowed();
        $this->delete(route('sample-items.destroy', $item))->assertForbidden();
        $this->post(route('sample-items.store', $order), ['item_type' => 'shirt', 'quantity' => 1])->assertForbidden();
        $this->get(route('sample-items.edit', $item))->assertForbidden();

        $this->assertDatabaseHas('sample_items', [
            'id' => $item->id, 'item_type' => 'shirt', 'quantity' => 4,
            'fabric' => 'Microfiber', 'description' => 'Handed over',
        ]);
        $this->assertDatabaseCount('sample_items', 1);
        $this->get(route('sample-orders.show', $order))
            ->assertOk()
            ->assertSee('Locked after customer handover')
            ->assertDontSee(route('sample-items.edit', $item), false)
            ->assertDontSee(route('sample-items.create', $order), false);

        $owner = User::factory()->create();
        $owner->assignRole(Role::create(['name' => 'owner', 'guard_name' => 'web']));
        $this->actingAs($owner)->put(route('sample-items.update', $item), $changes)->assertForbidden();
    }

    public function test_lalamove_item_remains_editable_while_in_transit(): void
    {
        $order = $this->sampleOrder('SO-LALAMOVE-TRANSIT', 'lalamove', 'in_transit');
        $item = $order->sampleItems()->create(['item_type' => 'shirt', 'quantity' => 4]);

        $this->put(route('sample-items.update', $item), [
            'item_type' => 'shirt', 'quantity' => 2, 'fabric' => 'Microfiber',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame(2, $item->fresh()->quantity);
    }

    public function test_lalamove_items_are_immutable_after_customer_received_and_return_processing_remains_available(): void
    {
        $order = $this->sampleOrder('SO-LALAMOVE-RECEIVED', 'lalamove', 'received');
        $item = $order->sampleItems()->create([
            'item_type' => 'shirt', 'quantity' => 4, 'fabric' => 'Microfiber', 'description' => 'Handed over',
        ]);
        $sample = Sample::create(['sample_code' => 'SMP-LOCKED', 'item_type' => 'shirt', 'status' => 'available']);
        $changes = [
            'item_type' => 'others', 'sample_name' => 'Changed item', 'quantity' => 2,
            'fabric' => 'Cotton', 'description' => 'Changed description', 'sample_id' => $sample->id,
        ];

        $this->put(route('sample-items.update', $item), $changes)->assertForbidden();
        $this->delete(route('sample-items.destroy', $item))->assertForbidden();
        $this->post(route('sample-items.store', $order), $changes)->assertForbidden();
        $this->assertDatabaseHas('sample_items', [
            'id' => $item->id, 'item_type' => 'shirt', 'sample_name' => null,
            'quantity' => 4, 'fabric' => 'Microfiber', 'description' => 'Handed over', 'sample_id' => null,
        ]);

        $order->photos()->create([
            'photo_type' => 'customer_received', 'uploaded_by_customer' => true,
            'file_path' => 'sample-orders/'.$order->id.'/photos/received.jpg',
        ]);
        $this->post(route('customer-sample-photos.store', $order->customer_token), [
            'photo_type' => 'before_return',
            'photos' => [\Illuminate\Http\UploadedFile::fake()->image('before-return.jpg')],
        ])->assertRedirect(route('customer-sample-photos.show', $order->customer_token));
        $this->post(route('sample-orders.return.store', $order), [
            'condition' => 'good', 'deposit_action' => 'refund', 'returned_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Returned in good condition',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertDatabaseHas('sample_returns', [
            'sample_order_id' => $order->id, 'condition' => 'good', 'deposit_action' => 'refund',
            'notes' => 'Returned in good condition',
        ]);
        $this->assertSame(4, $item->fresh()->quantity);
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
