<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_developer_login_redirects_to_console(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->post('/login', [
            'email' => $developer->email,
            'password' => 'password',
        ])->assertRedirect(route('developer.dashboard', absolute: false));

        $this->assertAuthenticatedAs($developer);
        $this->get(route('developer.dashboard'))->assertOk()->assertSee('You are currently logged in as Developer');
    }

    public function test_developer_can_impersonate_and_restore_an_active_staff_user(): void
    {
        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');

        $designer = User::factory()->create(['account_status' => 'active']);
        $designer->assignRole('designer');

        $this->actingAs($developer)
            ->post(route('developer.impersonate', $designer), ['role' => 'designer'])
            ->assertRedirect(route('designer.dashboard', absolute: false));

        $this->assertAuthenticatedAs($designer);
        $this->assertTrue($developer->fresh()->hasRole('developer'));

        $this->post(route('developer.impersonation.stop'))
            ->assertRedirect(route('developer.dashboard', absolute: false));

        $this->assertAuthenticatedAs($developer);
        $this->assertTrue(auth()->user()->hasRole('developer'));
    }

    public function test_non_developers_cannot_access_developer_routes(): void
    {
        $owner = User::factory()->create(['account_status' => 'active']);
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get(route('developer.dashboard'))
            ->assertForbidden();
    }
}
