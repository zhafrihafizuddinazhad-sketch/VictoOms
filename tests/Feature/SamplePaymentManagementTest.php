<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\SampleOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SamplePaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private function sampleOrder(string $number = 'SO-PAYMENT-0001', string $method = 'office'): SampleOrder
    {
        return SampleOrder::create([
            'customer_name' => 'Zhafri',
            'order_number' => $number,
            'collection_method' => $method,
            'pickup_date' => '2026-09-30',
            'return_date' => '2026-10-03',
            'deposit_amount' => 50,
            'deposit_status' => 'pending',
            'status' => 'pending_payment',
            'customer_token' => $number . '-token',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create();
        $this->staff->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($this->staff);
    }

    public function test_paid_deposit_is_recorded_with_authenticated_confirmer_and_synced_to_order(): void
    {
        $order = $this->sampleOrder();

        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 50,
            'payment_method' => 'bank_transfer',
            'status' => 'paid',
            'notes' => 'Transfer confirmed in bank app',
            'confirmed_by' => 999999,
        ])->assertRedirect(route('sample-orders.show', $order));

        $payment = Payment::firstOrFail();
        $this->assertSame($this->staff->id, $payment->confirmed_by);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('paid', $order->fresh()->deposit_status);
        $this->assertSame('ready_for_collection', $order->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'sample_order_id' => $order->id,
            'amount' => 50,
            'payment_method' => 'bank_transfer',
            'status' => 'paid',
            'confirmed_by' => $this->staff->id,
        ]);

        $this->get(route('sample-orders.show', $order))
            ->assertOk()
            ->assertSee('Payment / Deposit')
            ->assertSee('Payment History')
            ->assertSee('Bank Transfer')
            ->assertSee($this->staff->name)
            ->assertSee('Transfer confirmed in bank app');
    }

    public function test_pending_payment_does_not_advance_order(): void
    {
        $order = $this->sampleOrder();

        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'pending',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertSame('pending', $order->fresh()->deposit_status);
    }

    public function test_paid_lalamove_deposit_moves_to_existing_pre_delivery_status_and_keeps_same_photo_link(): void
    {
        $order = $this->sampleOrder('SO-LALAMOVE-PAID', 'lalamove');
        $token = $order->customer_token;
        $customerUrl = route('customer-sample-photos.show', $token);

        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 50,
            'payment_method' => 'online',
            'status' => 'paid',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('paid', $order->fresh()->deposit_status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame($token, $order->fresh()->customer_token);
        $this->assertDatabaseCount('payments', 1);

        $this->get(route('sample-orders.show', $order))
            ->assertOk()
            ->assertSee('Ready for Delivery')
            ->assertSee('data-copy-url="'.e($customerUrl).'"', false)
            ->assertSee('Copy Customer Upload Link')
            ->assertSee('Customer Photo Upload Link')
            ->assertSee('✓ Link Copied')
            ->assertDontSee('Customer uploads this checkpoint');

        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_delivery',
            'photos' => [\Illuminate\Http\UploadedFile::fake()->image('before-delivery.jpg')],
        ])->assertRedirect();

        $this->patch(route('sample-orders.status.update', $order), ['status' => 'in_transit'])
            ->assertRedirect(route('sample-orders.show', $order));

        $this->get($customerUrl)->assertOk()->assertSee('received the sample');
        $this->assertSame($token, $order->fresh()->customer_token);
    }

    public function test_payment_updates_do_not_move_an_already_progressed_order_backwards(): void
    {
        $order = $this->sampleOrder();
        $order->update(['status' => 'collected']);

        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('collected', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->deposit_status);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_payment_status_updates_keep_order_deposit_status_in_sync(): void
    {
        $order = $this->sampleOrder();
        $payment = $order->payments()->create([
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $this->put(route('sample-orders.payments.update', [$order, $payment]), [
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'paid',
            'notes' => 'Paid at office',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('paid', $order->fresh()->deposit_status);
        $this->assertSame($this->staff->id, $payment->fresh()->confirmed_by);
        $this->assertNotNull($payment->fresh()->paid_at);

        $this->put(route('sample-orders.payments.update', [$order, $payment]), [
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'refunded',
            'notes' => 'Refund issued separately',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('refunded', $order->fresh()->deposit_status);
        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertSame($this->staff->id, $payment->fresh()->confirmed_by);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_paid_payment_keeps_deposit_paid_when_another_payment_is_updated(): void
    {
        $order = $this->sampleOrder();
        $firstPaid = $order->payments()->create([
            'amount' => 50,
            'payment_method' => 'online',
            'status' => 'paid',
            'paid_at' => now(),
            'confirmed_by' => $this->staff->id,
        ]);
        $other = $order->payments()->create([
            'amount' => 10,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $this->put(route('sample-orders.payments.update', [$order, $other]), [
            'amount' => 10,
            'payment_method' => 'cash',
            'status' => 'failed',
        ])->assertRedirect(route('sample-orders.show', $order));

        $this->assertSame('paid', $order->fresh()->deposit_status);
        $this->assertSame('paid', $firstPaid->fresh()->status);
    }

    public function test_payment_validation_and_sample_order_ownership_are_enforced(): void
    {
        $order = $this->sampleOrder();
        $otherOrder = $this->sampleOrder('SO-PAYMENT-0002');

        $this->from(route('sample-orders.show', $order))
            ->post(route('sample-orders.payments.store', $order), [
                'amount' => -1,
                'payment_method' => 'crypto',
                'status' => 'unknown',
            ])
            ->assertSessionHasErrors(['amount', 'payment_method', 'status']);

        $this->from(route('sample-orders.show', $order))
            ->post(route('sample-orders.payments.store', $order), [
                'amount' => '',
                'payment_method' => 'cash',
                'status' => 'pending',
            ])
            ->assertSessionHasErrors(['amount']);

        $payment = $otherOrder->payments()->create([
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $this->put(route('sample-orders.payments.update', [$order, $payment]), [
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertNotFound();

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_other_roles_cannot_manage_sample_payments(): void
    {
        $order = $this->sampleOrder();
        $designer = User::factory()->create();
        $designer->assignRole(Role::create(['name' => 'designer', 'guard_name' => 'web']));
        $this->actingAs($designer);

        $this->post(route('sample-orders.payments.store', $order), [
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }
}
