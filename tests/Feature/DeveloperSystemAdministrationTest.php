<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Tests\TestCase;

class DeveloperSystemAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_developer_can_manage_accounts_and_account_status_lifecycle(): void
    {
        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSee('Account Management');

        $this->actingAs($developer)->post(route('accounts.store'), [
            'name' => 'System Admin',
            'email' => 'system-admin@example.test',
            'phone' => '',
            'role' => 'admin',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertRedirect(route('accounts.index', absolute: false));

        $managed = User::where('email', 'system-admin@example.test')->firstOrFail();
        $this->assertTrue($managed->hasRole('admin'));
        $this->assertTrue(Hash::check('secure-pass-123', $managed->password));

        $this->actingAs($developer)->patch(route('accounts.deactivate', $managed))->assertRedirect();
        $this->assertSame('inactive', $managed->fresh()->account_status);
        $this->actingAs($developer)->patch(route('accounts.archive', $managed))->assertRedirect();
        $this->assertSame('archived', $managed->fresh()->account_status);
        $this->actingAs($developer)->patch(route('accounts.restore', $managed))->assertRedirect();
        $this->assertSame('inactive', $managed->fresh()->account_status);
        $this->actingAs($developer)->patch(route('accounts.reactivate', $managed))->assertRedirect();
        $this->assertSame('active', $managed->fresh()->account_status);

        $this->assertGreaterThanOrEqual(5, ActivityLog::whereNull('order_id')->count());
    }

    public function test_developer_cannot_disable_or_demote_their_own_account(): void
    {
        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->patch(route('accounts.deactivate', $developer))
            ->assertSessionHas('error');
        $this->patch(route('accounts.archive', $developer))->assertSessionHas('error');
        $this->patch(route('accounts.role', $developer))->assertForbidden();

        $this->assertSame('active', $developer->fresh()->account_status);
        $this->assertTrue($developer->fresh()->hasRole('developer'));
    }

    public function test_developer_pages_are_protected_and_health_page_is_available(): void
    {
        $owner = User::factory()->create(['account_status' => 'active']);
        $owner->assignRole('owner');

        foreach ([route('developer.dashboard'), route('developer.accounts'), route('developer.health'), route('developer.activity'), route('developer.maintenance')] as $uri) {
            $this->actingAs($owner)->get($uri)->assertForbidden();
        }

        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');
        $this->actingAs($developer)->get(route('developer.health'))
            ->assertOk()->assertSee('Database')->assertSee('Storage')->assertSee('Cache');
        $this->get(route('developer.activity'))->assertOk()->assertSee('System Activity');
        $this->get(route('developer.dashboard'))->assertOk()->assertSee('System Overview')->assertSee('Role Distribution');
    }

    public function test_owner_cannot_create_a_protected_developer_account(): void
    {
        $owner = User::factory()->create(['account_status' => 'active']);
        $owner->assignRole('owner');

        $this->actingAs($owner)->from(route('accounts.create'))->post(route('accounts.store'), [
            'name' => 'Attempted Developer',
            'email' => 'attempt@example.test',
            'role' => 'developer',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'attempt@example.test']);
    }

    public function test_developer_can_change_other_accounts_roles_but_cannot_remove_the_last_active_developer(): void
    {
        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');
        $staff = User::factory()->create(['account_status' => 'active']);
        $staff->assignRole('designer');

        $this->actingAs($developer)->patch(route('accounts.role', $staff), ['role' => 'cameraman'])
            ->assertRedirect();
        $this->assertTrue($staff->fresh()->hasRole('cameraman'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'role_changed']);

        $this->patch(route('accounts.role', $developer), ['role' => 'admin'])->assertForbidden();
        $this->assertTrue($developer->fresh()->hasRole('developer'));

        $this->patch(route('accounts.deactivate', $developer))->assertSessionHas('error');
        $this->assertSame('active', $developer->fresh()->account_status);
    }

    public function test_developer_can_impersonate_each_active_business_role_but_not_developer_accounts(): void
    {
        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');
        $this->actingAs($developer);

        foreach (['owner', 'admin', 'designer', 'cameraman'] as $role) {
            $target = User::factory()->create(['account_status' => 'active']);
            $target->assignRole($role);

            $this->post(route('developer.impersonate', $target), ['role' => $role])
                ->assertRedirect(route($role.'.dashboard', absolute: false));
            $this->assertAuthenticatedAs($target);
            $this->post(route('developer.impersonation.stop'))
                ->assertRedirect(route('developer.dashboard', absolute: false));
            $this->assertAuthenticatedAs($developer);
        }

        $inactive = User::factory()->create(['account_status' => 'inactive']);
        $inactive->assignRole('admin');
        $this->post(route('developer.impersonate', $inactive), ['role' => 'admin'])->assertForbidden();

        $otherDeveloper = User::factory()->create(['account_status' => 'active']);
        $otherDeveloper->assignRole('developer');
        $this->post(route('developer.impersonate', $otherDeveloper), ['role' => 'developer'])->assertInvalid('role');
    }

    public function test_maintenance_mode_is_enabled_with_a_developer_bypass_and_can_be_disabled(): void
    {
        $developer = User::factory()->create(['account_status' => 'active']);
        $developer->assignRole('developer');

        try {
            $response = $this->actingAs($developer)->post(route('developer.maintenance.toggle'));
            $response->assertRedirect();
            $this->assertTrue(app()->isDownForMaintenance());
            $this->assertDatabaseHas('activity_logs', ['action' => 'maintenance_enabled']);

            $secretPath = parse_url($response->headers->get('Location'), PHP_URL_PATH);
            $secret = ltrim($secretPath, '/');
            $bypassCookie = MaintenanceModeBypassCookie::create($secret)->getValue();
            $this->disableCookieEncryption()->withCookie('laravel_maintenance', $bypassCookie)
                ->post(route('developer.maintenance.toggle'))
                ->assertRedirect(route('developer.dashboard', absolute: false));
            $this->assertFalse(app()->isDownForMaintenance());
        } finally {
            Artisan::call('up');
        }
    }
}
