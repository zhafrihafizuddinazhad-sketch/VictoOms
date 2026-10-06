<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\User;
use App\Services\DeveloperImpersonationService;
use App\Services\SystemAudit;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        $roles = ['owner', 'admin', 'designer', 'cameraman'];
        $usersByRole = collect($roles)->mapWithKeys(fn (string $role) => [
            $role => User::role($role)
                ->where('account_status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'account_status']),
        ]);

        return view('developer.dashboard', [
            'usersByRole' => $usersByRole,
            'stats' => [
                'users' => User::count(),
                'activeUsers' => User::where('account_status', 'active')->count(),
                'inactiveUsers' => User::where('account_status', 'inactive')->count(),
                'archivedUsers' => User::where('account_status', 'archived')->count(),
                'roleCounts' => collect(['owner', 'admin', 'designer', 'cameraman', 'developer'])
                    ->mapWithKeys(fn (string $role) => [$role => User::role($role)->count()]),
                'orders' => Order::count(),
                'ordersToday' => Order::whereDate('created_at', today())->count(),
                'activeOrders' => Order::whereNotIn('status', ['Completed', 'Cancelled'])->count(),
            ],
            'health' => $this->healthStatuses(),
            'recentActivity' => ActivityLog::with('user')->whereNull('order_id')->latest()->take(8)->get(),
            'maintenanceEnabled' => app()->isDownForMaintenance(),
        ]);
    }

    public function activity(): View
    {
        return view('developer.activity', [
            'activities' => ActivityLog::with('user')->whereNull('order_id')->latest()->paginate(30),
        ]);
    }

    public function health(): View
    {
        return view('developer.health', ['health' => $this->healthStatuses()]);
    }

    public function maintenancePage(): View
    {
        return view('developer.maintenance', ['maintenanceEnabled' => app()->isDownForMaintenance()]);
    }

    public function maintenance(SystemAudit $audit): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('developer'), 403);

        if (app()->isDownForMaintenance()) {
            $result = Artisan::call('up');
            abort_if($result !== 0, 500, 'Maintenance mode could not be disabled.');
            $audit->record('maintenance_disabled', 'Developer disabled application maintenance mode.', auth()->user());

            return redirect()->route('developer.dashboard')->with('success', 'Maintenance mode is disabled.');
        }

        $secret = Str::random(40);
        $result = Artisan::call('down', ['--secret' => $secret, '--render' => 'errors.503']);
        abort_if($result !== 0, 500, 'Maintenance mode could not be enabled.');
        $audit->record('maintenance_enabled', 'Developer enabled application maintenance mode.', auth()->user());

        // Visit Laravel's one-time secret URL so its signed bypass cookie keeps this
        // developer session operational while all ordinary visitors see the 503 page.
        return redirect('/'.$secret);
    }

    private function healthStatuses(): array
    {
        $health = [
            'application' => ['label' => 'Application', 'ok' => true, 'detail' => 'Online'],
            'database' => ['label' => 'Database', 'ok' => false, 'detail' => 'Unavailable'],
            'storage' => ['label' => 'Storage', 'ok' => false, 'detail' => 'Unavailable'],
            'cache' => ['label' => 'Cache', 'ok' => false, 'detail' => 'Unavailable'],
        ];

        try {
            DB::select('SELECT 1');
            $health['database']['ok'] = true;
            $health['database']['detail'] = 'Connected';
        } catch (\Throwable) {
            // Health output deliberately excludes connection details and exception text.
        }

        try {
            $storagePath = storage_path('app');
            $health['storage']['ok'] = is_dir($storagePath) && is_writable($storagePath);
            $health['storage']['detail'] = $health['storage']['ok'] ? 'Available' : 'Unavailable';
        } catch (\Throwable) {
            // Do not expose local paths in the dashboard.
        }

        $cacheKey = 'developer-health-'.Str::random(24);
        try {
            Cache::put($cacheKey, 'ok', 10);
            $health['cache']['ok'] = Cache::get($cacheKey) === 'ok';
            $health['cache']['detail'] = $health['cache']['ok'] ? 'Working' : 'Unavailable';
        } catch (\Throwable) {
            // Do not expose cache credentials or connection details.
        } finally {
            try { Cache::forget($cacheKey); } catch (\Throwable) {}
        }

        return $health;
    }

    public function impersonate(Request $request, User $user, DeveloperImpersonationService $impersonation): RedirectResponse
    {
        $role = $request->validate(['role' => ['required', 'in:owner,admin,designer,cameraman']])['role'];
        $impersonation->start($user, $role, app(SystemAudit::class));

        $route = match ($role) {
            'owner' => 'owner.dashboard',
            'admin' => 'admin.dashboard',
            'designer' => 'designer.dashboard',
            'cameraman' => 'cameraman.dashboard',
            default => throw new AccessDeniedHttpException(),
        };

        return redirect()->route($route);
    }

    public function stopImpersonation(DeveloperImpersonationService $impersonation): RedirectResponse
    {
        if (! $impersonation->stop()) {
            return redirect()->route('login')->with('error', 'Your Developer session could not be restored. Please sign in again.');
        }

        return redirect()->route('developer.dashboard')->with('success', 'Returned to Developer Console.');
    }
}
