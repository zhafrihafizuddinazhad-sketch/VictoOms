<?php

namespace Tests\Feature;

use App\Models\SampleOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SampleOrderCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($user);
    }

    public function test_order_creation_saves_items_and_original_sample_photos_together(): void
    {
        $this->post(route('sample-orders.store'), [
            'customer_name' => 'Sample Photo Customer',
            'collection_method' => 'office',
            'pickup_date' => '2026-10-01',
            'return_date' => '2026-10-05',
            'deposit_amount' => 25,
            'items' => [
                ['item_type' => 'shirt', 'quantity' => 2, 'fabric' => 'Cotton', 'description' => 'Navy sample'],
                ['item_type' => 'short', 'quantity' => 1],
            ],
            'sample_photos' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('back.png')],
        ])->assertSessionHasNoErrors();

        $order = SampleOrder::where('customer_name', 'Sample Photo Customer')->firstOrFail();
        $this->assertDatabaseCount('sample_items', 2);
        $this->assertDatabaseCount('sample_photos', 2);
        $this->assertSame(['original', 'original'], $order->photos()->orderBy('id')->pluck('photo_type')->all());
        foreach ($order->photos as $photo) {
            Storage::disk('local')->assertExists($photo->file_path);
        }
        $this->get(route('sample-orders.show', $order))->assertOk()->assertSee('Sample Photos')->assertSee('Navy sample');
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

    public function test_staff_create_form_uses_customer_request_fields_and_item_controls(): void
    {
        $this->get(route('sample-orders.create'))
            ->assertOk()
            ->assertSee('Full name')
            ->assertSee('Phone number')
            ->assertSee('Add another sample')
            ->assertSee('Others')
            ->assertSee('Please specify the sample you are borrowing')
            ->assertSee('Delivery address')
            ->assertSee('Pickup date')
            ->assertSee('Deposit amount')
            ->assertSee('Take Photo')
            ->assertSee('Choose from Gallery')
            ->assertSee("setAttribute('capture', 'environment')", false)
            ->assertSee('SAMPLE MANAGEMENT')
            ->assertSee('Sample Orders');
    }

    public function test_staff_can_create_lalamove_order_with_other_and_standard_items(): void
    {
        $this->post(route('sample-orders.store'), [
            'full_name' => 'Hana Customer', 'phone' => '012 345 6789',
            'company' => 'Hana Studio', 'email' => 'hana@example.test',
            'collection_method' => 'lalamove', 'delivery_date' => '2026-10-07',
            'delivery_address' => '12 Jalan Contoh, Kuala Lumpur', 'return_date' => '2026-10-12',
            'deposit_amount' => 35, 'notes' => 'Please confirm before dispatch.',
            'items' => [
                ['item_type' => 'others', 'sample_name' => 'Table Cloth', 'quantity' => 2, 'description' => 'Round display table'],
                ['item_type' => 'shirt', 'quantity' => 1, 'fabric' => 'Microfiber'],
            ],
        ])->assertRedirectContains('/sample-orders/');

        $order = SampleOrder::with('customer', 'sampleItems')->firstOrFail();
        $this->assertSame('Hana Customer', $order->customer_name);
        $this->assertSame('0123456789', $order->customer->phone);
        $this->assertSame('Hana Studio', $order->customer->company);
        $this->assertSame('lalamove', $order->collection_method);
        $this->assertSame('2026-10-07', $order->delivery_date->format('Y-m-d'));
        $this->assertNull($order->pickup_date);
        $this->assertSame('Table Cloth', $order->sampleItems[0]->sample_name);
        $this->assertNull($order->sampleItems[1]->sample_name);
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('pending', $order->deposit_status);
        $this->get(route('sample-orders.show', $order))->assertOk()->assertSee('Table Cloth');
    }

    public function test_staff_can_create_office_order_using_the_shared_customer_fields(): void
    {
        $this->post(route('sample-orders.store'), [
            'full_name' => 'Aina Office', 'phone' => '0123456789', 'company' => 'Aina Apparel',
            'email' => 'aina@example.test', 'collection_method' => 'office',
            'pickup_date' => '2026-10-07', 'return_date' => '2026-10-12',
            'deposit_amount' => 20, 'items' => [['item_type' => 'shirt', 'quantity' => 2]],
        ])->assertRedirectContains('/sample-orders/');

        $order = SampleOrder::with('customer', 'sampleItems')->firstOrFail();
        $this->assertSame('office', $order->collection_method);
        $this->assertSame('2026-10-07', $order->pickup_date->format('Y-m-d'));
        $this->assertNull($order->delivery_date);
        $this->assertNull($order->delivery_address);
        $this->assertSame('aina@example.test', $order->customer->email);
        $this->assertSame('shirt', $order->sampleItems[0]->item_type);
        $this->assertNull($order->sampleItems[0]->sample_name);
    }

    public function test_staff_cannot_create_other_item_without_sample_name(): void
    {
        $this->from(route('sample-orders.create'))
            ->post(route('sample-orders.store'), [
                'full_name' => 'Hana Customer', 'phone' => '0123456789',
                'collection_method' => 'office', 'pickup_date' => '2026-10-07',
                'return_date' => '2026-10-12', 'deposit_amount' => 35,
                'items' => [['item_type' => 'others', 'sample_name' => '', 'quantity' => 1]],
            ])
            ->assertSessionHasErrors(['items.0.sample_name']);

        $this->assertDatabaseCount('sample_orders', 0);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_sample_management_sidebar_is_visible_to_owner_and_hidden_from_other_roles(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::create(['name' => 'owner', 'guard_name' => 'web']));
        $this->actingAs($owner)->get(route('sample-orders.index'))
            ->assertOk()->assertSee('SAMPLE MANAGEMENT')->assertSee('Sample Orders');

        $designer = User::factory()->create();
        $designer->assignRole(Role::create(['name' => 'designer', 'guard_name' => 'web']));
        $this->actingAs($designer)->get(route('sample-orders.index'))->assertForbidden();
        $this->get(route('designer.dashboard'))->assertOk()->assertDontSee('SAMPLE MANAGEMENT')->assertDontSee('Sample Orders');
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
