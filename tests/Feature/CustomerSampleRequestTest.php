<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SampleOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerSampleRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['sample.owner_whatsapp' => '60123456789']);
        Storage::fake('local');
    }

    private function officeRequest(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Aina Customer',
            'phone' => '+60 12-345 6789',
            'company' => 'Aina Apparel',
            'email' => 'aina@example.test',
            'items' => [
                ['item_type' => 'shirt', 'quantity' => 2, 'fabric' => 'Microfiber', 'description' => 'Black collar'],
                ['item_type' => 'short', 'quantity' => 1, 'fabric' => 'Cotton', 'description' => 'White sample'],
            ],
            'collection_method' => 'office',
            'pickup_date' => '2026-10-05',
            'return_date' => '2026-10-10',
            'notes' => 'Please advise availability.',
        ], $overrides);
    }

    public function test_public_form_loads_without_login(): void
    {
        $this->get(route('customer.sample-request.create'))
            ->assertOk()
            ->assertSee('id="sample-request-form" method="POST"', false)
            ->assertSee('data-loading="off"', false)
            ->assertSee('id="confirm-submit"', false)
            ->assertSee('type="submit" id="confirm-submit"', false)
            ->assertSee('Request a sample')
            ->assertSee('Add another sample')
            ->assertSee('Lalamove delivery');
    }

    public function test_office_request_creates_customer_order_items_and_whatsapp_success_without_payment(): void
    {
        $response = $this->post(route('customer.sample-request.store'), $this->officeRequest());
        $response->assertRedirect(route('customer.sample-request.success'));
        $response->assertSessionHas('sample_request_id');

        $order = SampleOrder::with('customer', 'sampleItems')->firstOrFail();
        $this->assertSame('Aina Customer', $order->customer_name);
        $this->assertSame('0123456789', $order->customer->phone);
        $this->assertSame('Aina Apparel', $order->customer->company);
        $this->assertSame('office', $order->collection_method);
        $this->assertSame('2026-10-05', $order->pickup_date->format('Y-m-d'));
        $this->assertNull($order->delivery_date);
        $this->assertNull($order->delivery_address);
        $this->assertSame('2026-10-10', $order->return_date->format('Y-m-d'));
        $this->assertSame('customer', $order->created_source);
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('pending', $order->deposit_status);
        $this->assertSame('0.00', $order->deposit_amount);
        $this->assertTrue(Str::isUuid($order->customer_token));
        $this->assertSame(2, $order->sampleItems->count());
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('payments', 0);

        $this->get(route('customer.sample-request.success'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Continue to WhatsApp')
            ->assertSee('wa.me/60123456789?text=', false)
            ->assertDontSee('Phone%3A%200123456789', false)
            ->assertDontSee('Jalan Contoh')
            ->assertDontSee($order->customer_token);
        $this->get(route('customer.sample-request.success'))->assertOk()->assertSee($order->order_number);

        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($admin)->get(route('sample-orders.index'))
            ->assertOk()->assertSee($order->order_number)->assertSee('Customer submitted')->assertSee('Pending');
        $this->actingAs($admin)->get(route('sample-orders.show', $order))
            ->assertOk()->assertSee('0123456789')->assertSee('Aina Apparel')->assertSee('Black collar');
    }

    public function test_lalamove_request_saves_delivery_fields_and_uses_delivery_date_for_return_validation(): void
    {
        $data = $this->officeRequest([
            'collection_method' => 'lalamove',
            'pickup_date' => '',
            'delivery_date' => '2026-10-06',
            'delivery_address' => '12 Jalan Contoh, 50000 Kuala Lumpur',
            'return_date' => '2026-10-11',
        ]);
        $response = $this->post(route('customer.sample-request.store'), $data);
        $response->assertRedirect(route('customer.sample-request.success'));
        $order = SampleOrder::firstOrFail();
        $this->assertSame('lalamove', $order->collection_method);
        $this->assertNull($order->pickup_date);
        $this->assertSame('2026-10-06', $order->delivery_date->format('Y-m-d'));
        $this->assertSame('12 Jalan Contoh, 50000 Kuala Lumpur', $order->delivery_address);
        $this->get(route('customer.sample-request.success'))->assertOk()
            ->assertDontSee('Delivery%20address%3A', false)
            ->assertDontSee('12%20Jalan%20Contoh', false);
    }

    public function test_customer_can_request_other_sample_with_a_required_sample_name(): void
    {
        $data = $this->officeRequest([
            'items' => [
                ['item_type' => 'others', 'sample_name' => 'Banner', 'quantity' => 2, 'description' => 'Promotional banner'],
                ['item_type' => 'shirt', 'quantity' => 1, 'fabric' => 'Cotton'],
            ],
        ]);

        $this->post(route('customer.sample-request.store'), $data)->assertRedirect(route('customer.sample-request.success'));
        $order = SampleOrder::with('sampleItems')->firstOrFail();
        $this->assertCount(2, $order->sampleItems);
        $this->assertSame('others', $order->sampleItems[0]->item_type);
        $this->assertSame('Banner', $order->sampleItems[0]->sample_name);
        $this->assertSame('shirt', $order->sampleItems[1]->item_type);
        $this->assertNull($order->sampleItems[1]->sample_name);
    }

    public function test_customer_other_item_requires_a_sample_name(): void
    {
        $this->from(route('customer.sample-request.create'))
            ->post(route('customer.sample-request.store'), $this->officeRequest([
                'items' => [['item_type' => 'others', 'sample_name' => '', 'quantity' => 1]],
            ]))
            ->assertSessionHasErrors(['items.0.sample_name']);

        $this->assertDatabaseCount('sample_orders', 0);
    }

    public function test_existing_customer_is_reused_and_contact_details_are_updated_without_duplicate(): void
    {
        $customer = Customer::create(['customer_name' => 'Old Name', 'phone' => '0123456789', 'company' => 'Old Company']);
        $this->post(route('customer.sample-request.store'), $this->officeRequest(['full_name' => 'New Name', 'company' => null, 'email' => null]))
            ->assertRedirect(route('customer.sample-request.success'));
        $order = SampleOrder::firstOrFail();
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertDatabaseCount('customers', 1);
        $this->assertSame('New Name', $customer->fresh()->customer_name);
        $this->assertSame('Old Company', $customer->fresh()->company);
    }

    public function test_public_request_ignores_forged_status_payment_and_token_values(): void
    {
        $this->post(route('customer.sample-request.store'), $this->officeRequest([
            'status' => 'completed', 'deposit_status' => 'paid', 'deposit_amount' => 9999,
            'customer_token' => 'attacker-token', 'order_number' => 'ATTACKER-ORDER',
        ]))->assertRedirect(route('customer.sample-request.success'));

        $order = SampleOrder::firstOrFail();
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('pending', $order->deposit_status);
        $this->assertSame('0.00', $order->deposit_amount);
        $this->assertNotSame('attacker-token', $order->customer_token);
        $this->assertStringStartsWith('SO-', $order->order_number);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_validation_rejects_missing_fields_invalid_item_and_earlier_return_without_partial_data(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $invalidCases = [
            [$this->officeRequest(['full_name' => '']), 'full_name'],
            [$this->officeRequest(['phone' => 'not-a-Malaysian-number']), 'phone'],
            [$this->officeRequest(['items' => []]), 'items'],
            [$this->officeRequest(['items' => [['item_type' => 'shirt', 'quantity' => 0]]]), 'items.0.quantity'],
            [$this->officeRequest(['collection_method' => 'courier']), 'collection_method'],
            [$this->officeRequest(['pickup_date' => '']), 'pickup_date'],
            [$this->officeRequest(['email' => 'bad-email']), 'email'],
            [$this->officeRequest(['return_date' => '2026-10-04']), 'return_date'],
            [$this->officeRequest(['collection_method' => 'lalamove', 'pickup_date' => '', 'delivery_date' => '', 'delivery_address' => '']), 'delivery_address'],
        ];
        foreach ($invalidCases as [$payload, $errorKey]) {
            $this->from(route('customer.sample-request.create'))
                ->post(route('customer.sample-request.store'), $payload)
                ->assertSessionHasErrors([$errorKey]);
        }
        $this->assertDatabaseCount('sample_orders', 0);
        $this->assertDatabaseCount('sample_items', 0);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_guest_cannot_open_staff_sample_order_list(): void
    {
        $this->get(route('sample-orders.index'))->assertRedirect(route('login'));
    }

    public function test_office_request_completes_the_manual_payment_pickup_return_and_after_return_workflow(): void
    {
        $this->post(route('customer.sample-request.store'), $this->officeRequest())
            ->assertRedirect(route('customer.sample-request.success'));
        $order = SampleOrder::firstOrFail();
        $this->assertSame('pending_payment', $order->status);

        $staff = User::factory()->create();
        $staff->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($staff);

        $this->patch(route('sample-orders.status.update', $order), ['status' => 'ready_for_collection'])
            ->assertSessionHasErrors('status');
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_handover', 'photos' => [UploadedFile::fake()->image('too-early.jpg')],
        ])->assertSessionHasErrors('photo_type');

        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 25, 'payment_method' => 'bank_transfer', 'status' => 'paid',
            'paid_at' => '2026-10-02 10:00:00', 'notes' => 'Transfer verified',
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->assertSame('paid', $order->fresh()->deposit_status);
        $this->assertSame('ready_for_collection', $order->fresh()->status);

        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_handover', 'photos' => [UploadedFile::fake()->image('handover.jpg')],
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'collected'])
            ->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'return_pending'])
            ->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->post(route('sample-orders.return.store', $order), [
            'condition' => 'good', 'deposit_action' => 'refund', 'returned_at' => '2026-10-08 10:00:00',
            'notes' => 'Returned early in good condition.',
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'after_return', 'photos' => [UploadedFile::fake()->image('after-return.jpg')],
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'completed'])
            ->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame('2026-10-08 10:00:00', $order->fresh()->sampleReturn->returned_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('sample_order_events', ['sample_order_id' => $order->id, 'event_key' => 'prepared_for_pickup']);
        $this->assertDatabaseHas('sample_order_events', ['sample_order_id' => $order->id, 'event_key' => 'customer_collected']);
        $this->assertDatabaseHas('sample_order_events', ['sample_order_id' => $order->id, 'event_key' => 'return_expected']);
        $this->assertDatabaseHas('sample_order_events', ['sample_order_id' => $order->id, 'event_key' => 'order_completed']);
        $this->get(route('sample-orders.show', $order))->assertOk()
            ->assertSee('Customer Collection')->assertSee('Return Expected')
            ->assertSee('Return Recorded')->assertSee('After Return Photo')->assertSee('Order Completed');
    }

    public function test_lalamove_request_completes_the_same_token_delivery_return_and_inspection_workflow(): void
    {
        $data = $this->officeRequest([
            'collection_method' => 'lalamove', 'pickup_date' => '', 'delivery_date' => '2026-10-06',
            'delivery_address' => '12 Jalan Contoh, Kuala Lumpur', 'return_date' => '2026-10-12',
        ]);
        $this->post(route('customer.sample-request.store'), $data)
            ->assertRedirect(route('customer.sample-request.success'));
        $order = SampleOrder::firstOrFail();
        $token = $order->customer_token;

        $staff = User::factory()->create();
        $staff->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($staff);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'in_transit'])
            ->assertSessionHasErrors('status');
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_delivery', 'photos' => [UploadedFile::fake()->image('too-early.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 25, 'payment_method' => 'cash', 'status' => 'paid',
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->assertSame('pending', $order->fresh()->status);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'in_transit'])
            ->assertSessionHasErrors('status');
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_delivery', 'photos' => [UploadedFile::fake()->image('before-delivery.jpg')],
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'in_transit'])
            ->assertRedirect(route('sample-orders.show', $order));

        auth()->logout();
        $customerPage = route('customer-sample-photos.show', $token);
        $this->get($customerPage)->assertOk()
            ->assertSee('Please upload a photo showing that you have received the sample.')
            ->assertSee('name="photo_type" value="customer_received"', false);
        $this->post(route('customer-sample-photos.store', $token), [
            'photo_type' => 'after_return', 'photos' => [UploadedFile::fake()->image('staff-only.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->post(route('customer-sample-photos.store', $token), [
            'photo_type' => 'customer_received', 'photos' => [UploadedFile::fake()->image('received.jpg')],
        ])->assertRedirect($customerPage);
        $this->get($customerPage)->assertOk()
            ->assertSee('Please upload a photo of the sample before returning it.')
            ->assertSee('name="photo_type" value="before_return"', false)
            ->assertDontSee('name="photo_type" value="customer_received"', false);
        $this->post(route('customer-sample-photos.store', $token), [
            'photo_type' => 'customer_received', 'photos' => [UploadedFile::fake()->image('duplicate-received.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->post(route('customer-sample-photos.store', $token), [
            'photo_type' => 'before_delivery', 'photos' => [UploadedFile::fake()->image('staff-checkpoint.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->post(route('customer-sample-photos.store', $token), [
            'photo_type' => 'before_return', 'photos' => [UploadedFile::fake()->image('before-return.jpg')],
        ])->assertRedirect($customerPage);
        $this->get($customerPage)->assertOk()
            ->assertSee('No customer upload is required right now. Please contact Victo if you need assistance.')
            ->assertDontSee('type="file"', false);
        $this->post(route('customer-sample-photos.store', $token), [
            'photo_type' => 'before_return', 'photos' => [UploadedFile::fake()->image('duplicate-before-return.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->get(route('customer-sample-photos.show', 'not-a-real-token'))->assertNotFound();
        $this->assertSame(3, $order->photos()->count());

        $this->actingAs($staff);
        $this->get(route('sample-orders.show', $order))->assertOk()->assertSee('Record Return');
        $this->post(route('sample-orders.return.store', $order), [
            'condition' => 'good', 'deposit_action' => 'hold', 'returned_at' => '2026-10-10 09:00:00',
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'after_return', 'photos' => [UploadedFile::fake()->image('after-return.jpg')],
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'completed'])
            ->assertRedirect(route('sample-orders.show', $order));
        $this->assertSame('completed', $order->fresh()->status);
        $this->get(route('sample-orders.show', $order))->assertOk()
            ->assertSee('Sent with Lalamove')->assertSee('Customer Received Photo')
            ->assertSee('Before Return Photo')->assertSee('Return Recorded')
            ->assertSee('After Return Photo')->assertSee('Order Completed');
    }
}
