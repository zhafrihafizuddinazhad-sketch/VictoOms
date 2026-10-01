<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DeveloperImpersonationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
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
                'inactiveUsers' => User::whereIn('account_status', ['inactive', 'archived'])->count(),
                'roles' => Role::count(),
                'orders' => Order::count(),
                'customers' => Customer::count(),
                'products' => Product::count(),
                'jobOrders' => JobOrder::count(),
            ],
        ]);
    }

    public function impersonate(Request $request, User $user, DeveloperImpersonationService $impersonation): RedirectResponse
    {
        $role = $request->validate(['role' => ['required', 'in:owner,admin,designer,cameraman']])['role'];
        $impersonation->start($user, $role);

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
