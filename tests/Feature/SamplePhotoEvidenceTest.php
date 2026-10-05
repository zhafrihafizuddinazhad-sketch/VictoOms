<?php

namespace Tests\Feature;

use App\Models\SampleOrder;
use App\Models\SamplePhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SamplePhotoEvidenceTest extends TestCase
{
    use RefreshDatabase;

    private function sampleOrder(): SampleOrder
    {
        return SampleOrder::create([
            'customer_name' => 'Zhafri',
            'order_number' => 'SO-PHOTO-0001',
            'collection_method' => 'office',
            'pickup_date' => '2026-09-30',
            'return_date' => '2026-10-03',
            'deposit_amount' => 50,
            'deposit_status' => 'pending',
            'status' => 'pending_payment',
            'customer_token' => 'sample-photo-test-token',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $this->actingAs($user);
    }

    public function test_office_order_exposes_only_its_two_staff_checkpoints_and_rejects_lalamove_types(): void
    {
        $order = $this->sampleOrder();
        $order->update(['deposit_status' => 'paid']);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'ready_for_collection']);
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_handover',
            'notes' => 'Condition evidence',
            'photos' => [UploadedFile::fake()->image('handover-one.jpg'), UploadedFile::fake()->image('handover-two.png')],
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'customer_received',
            'photos' => [UploadedFile::fake()->image('invalid.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'after_return',
            'photos' => [UploadedFile::fake()->image('too-early.jpg')],
        ])->assertSessionHasErrors('photo_type');

        $this->assertDatabaseCount('sample_photos', 2);
        foreach ($order->photos()->get() as $photo) {
            Storage::disk('local')->assertExists($photo->file_path);
            $this->assertSame('Condition evidence', $photo->notes);
            $this->assertNotNull($photo->uploaded_by_user_id);
            $this->assertFalse($photo->uploaded_by_customer);
        }

        $this->get(route('sample-orders.show', $order))
            ->assertOk()
            ->assertSee('Before Handover')
            ->assertSee('After Return')
            ->assertDontSee('Before Delivery')
            ->assertDontSee('Customer Received')
            ->assertDontSee('Before Customer Return')
            ->assertSee('Condition evidence');
    }

    public function test_lalamove_customer_token_uploads_only_customer_checkpoints_without_login(): void
    {
        $order = $this->sampleOrder();
        $order->update(['collection_method' => 'lalamove', 'deposit_status' => 'paid']);
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_delivery',
            'photos' => [UploadedFile::fake()->image('delivery.jpg')],
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'in_transit']);

        $staff = auth()->user();
        auth()->logout();
        $this->get(route('customer-sample-photos.show', $order->customer_token))
            ->assertOk()->assertSee('Please upload a photo showing that you have received the sample.')
            ->assertSee('name="photo_type" value="customer_received"', false);
        $this->post(route('customer-sample-photos.store', $order->customer_token), [
            'photo_type' => 'before_delivery', 'photos' => [UploadedFile::fake()->image('forbidden.jpg')],
        ])->assertSessionHasErrors('photo_type');
        $this->post(route('customer-sample-photos.store', $order->customer_token), [
            'photo_type' => 'customer_received', 'photos' => [UploadedFile::fake()->image('customer_received.jpg')],
        ])->assertRedirect(route('customer-sample-photos.show', $order->customer_token));
        $this->get(route('customer-sample-photos.show', $order->customer_token))
            ->assertOk()->assertSee('Please upload a photo of the sample before returning it.')
            ->assertSee('name="photo_type" value="before_return"', false);
        $this->post(route('customer-sample-photos.store', $order->customer_token), [
            'photo_type' => 'before_return', 'photos' => [UploadedFile::fake()->image('before_return.jpg')],
        ])->assertRedirect(route('customer-sample-photos.show', $order->customer_token));
        $this->assertDatabaseCount('sample_photos', 3);
        $this->assertDatabaseHas('sample_orders', ['id' => $order->id, 'status' => 'return_pending']);
        $this->actingAs($staff)->get(route('sample-orders.show', $order))->assertOk()->assertSee('Record Return');
        $this->get(route('customer-sample-photos.show', 'not-a-real-token'))->assertNotFound();

        $this->actingAs($staff);
        $this->post(route('sample-orders.return.store', $order), [
            'condition' => 'damaged', 'damage_description' => 'Small tear on the sleeve',
            'deposit_action' => 'deduct',
            'returned_at' => now()->format('Y-m-d H:i:s'),
        ]);
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'after_return', 'photos' => [UploadedFile::fake()->image('lalamove-return.jpg')],
        ]);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'completed']);
        $this->assertDatabaseHas('sample_orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('sample_returns', ['sample_order_id' => $order->id, 'condition' => 'damaged', 'deposit_action' => 'deduct']);
    }

    public function test_office_return_and_inspection_can_be_completed_after_both_photos(): void
    {
        $order = $this->sampleOrder();
        $order->update(['return_date' => '2020-01-01']);
        $order->update(['deposit_status' => 'paid']);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'ready_for_collection']);
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_handover', 'photos' => [UploadedFile::fake()->image('before.jpg')],
        ]);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'collected']);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'return_pending']);
        $this->post(route('sample-orders.return.store', $order), [
            'condition' => 'good', 'deposit_action' => 'refund', 'returned_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('sample-orders.show', $order));
        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'after_return', 'photos' => [UploadedFile::fake()->image('after.jpg')],
        ]);
        $this->patch(route('sample-orders.status.update', $order), ['status' => 'completed'])
            ->assertRedirect(route('sample-orders.show', $order));

        $this->assertDatabaseHas('sample_orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('sample_returns', ['sample_order_id' => $order->id, 'condition' => 'good', 'deposit_action' => 'refund']);
        $this->get(route('sample-orders.show', $order))->assertOk()->assertSee('Order Completed');
    }

    public function test_staff_can_delete_photo_record_and_file(): void
    {
        $order = $this->sampleOrder();
        $path = UploadedFile::fake()->image('evidence.jpg')->store('sample-orders/test/photos', 'local');
        $photo = $order->photos()->create([
            'photo_type' => 'before_handover',
            'uploaded_by_user_id' => auth()->id(),
            'uploaded_by_customer' => false,
            'file_path' => $path,
        ]);

        $this->delete(route('sample-photos.destroy', [$order, $photo]))
            ->assertRedirect(route('sample-orders.show', $order));

        $this->assertDatabaseMissing('sample_photos', ['id' => $photo->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_photo_upload_rejects_non_images_and_wrong_order_photo_deletion(): void
    {
        $order = $this->sampleOrder();
        $otherOrder = SampleOrder::create([
            'customer_name' => 'Another Customer',
            'order_number' => 'SO-PHOTO-0002',
            'collection_method' => 'office',
            'deposit_amount' => 0,
            'customer_token' => 'another-sample-photo-test-token',
        ]);

        $this->from(route('sample-orders.show', $order))
            ->post(route('sample-photos.store', $order), [
                'photo_type' => 'before_handover',
                'photos' => [
                    UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->image('too-large.jpg')->size(6000),
                ],
            ])
            ->assertSessionHasErrors(['photos.0', 'photos.1']);

        $photo = $otherOrder->photos()->create([
            'photo_type' => 'before_return',
            'file_path' => 'sample-orders/other/photo.jpg',
        ]);
        $this->delete(route('sample-photos.destroy', [$order, $photo]))->assertNotFound();
        $this->assertDatabaseHas('sample_photos', ['id' => $photo->id]);
    }

    public function test_unauthenticated_users_cannot_upload_photo_evidence(): void
    {
        auth()->logout();
        $order = $this->sampleOrder();

        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_handover',
            'photos' => [UploadedFile::fake()->image('evidence.jpg')],
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('sample_photos', 0);
    }

    public function test_internal_photo_view_requires_authorized_staff_and_serves_private_storage(): void
    {
        $order = $this->sampleOrder();
        $path = UploadedFile::fake()->image('private-evidence.jpg')->store('sample-orders/test/photos', 'local');
        $photo = $order->photos()->create([
            'photo_type' => 'before_handover',
            'uploaded_by_user_id' => auth()->id(),
            'uploaded_by_customer' => false,
            'file_path' => $path,
        ]);

        $this->get(route('sample-photos.show', [$order, $photo]))
            ->assertOk()
            ->assertHeader('cache-control', 'no-store, private')
            ->assertHeader('content-disposition', 'inline; filename=' . basename($path));

        auth()->logout();
        $this->get(route('sample-photos.show', [$order, $photo]))->assertRedirect(route('login'));
    }

    public function test_non_sample_staff_roles_cannot_access_sample_evidence_actions(): void
    {
        $order = $this->sampleOrder();
        $designer = User::factory()->create();
        $designer->assignRole(Role::create(['name' => 'designer', 'guard_name' => 'web']));
        $this->actingAs($designer);

        $this->post(route('sample-photos.store', $order), [
            'photo_type' => 'before_handover',
            'photos' => [UploadedFile::fake()->image('evidence.jpg')],
        ])->assertForbidden();

        $photo = $order->photos()->create([
            'photo_type' => 'before_handover',
            'file_path' => 'sample-orders/other/private.jpg',
        ]);
        $this->get(route('sample-photos.show', [$order, $photo]))->assertForbidden();

        $this->assertDatabaseCount('sample_photos', 1);
    }
}
