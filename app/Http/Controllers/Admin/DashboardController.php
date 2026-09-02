<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Today's Orders
        |--------------------------------------------------------------------------
        */

        $todayOrders = Order::whereDate(
            'created_at',
            Carbon::today()
        )->count();


        /*
        |--------------------------------------------------------------------------
        | Active Orders
        |--------------------------------------------------------------------------
        |
        | Completed and Cancelled orders are not counted as active.
        |
        */

        $activeOrders = Order::whereNotIn(
            'status',
            [
                'Completed',
                'Cancelled',
            ]
        )->count();


        /*
        |--------------------------------------------------------------------------
        | Orders Due Within 3 Days
        |--------------------------------------------------------------------------
        */

        $dueSoon = Order::whereNotIn(
            'status',
            [
                'Completed',
                'Cancelled',
            ]
        )
        ->whereNotNull('due_date')
        ->whereDate(
            'due_date',
            '>=',
            Carbon::today()
        )
        ->whereDate(
            'due_date',
            '<=',
            Carbon::today()->addDays(3)
        )
        ->count();


        /*
        |--------------------------------------------------------------------------
        | Orders That Need Attention
        |--------------------------------------------------------------------------
        |
        | Includes:
        |
        | 1. Orders waiting for approval
        | 2. Overdue orders
        |
        */

        $pendingApproval = Order::where(
            'status',
            'Pending Approval'
        )->count();


        $overdueOrders = Order::whereNotIn(
            'status',
            [
                'Completed',
                'Cancelled',
            ]
        )
        ->whereNotNull('due_date')
        ->whereDate(
            'due_date',
            '<',
            Carbon::today()
        )
        ->count();


        $needsAttention =
            $pendingApproval +
            $overdueOrders;


        /*
        |--------------------------------------------------------------------------
        | Orders That Need Attention
        |--------------------------------------------------------------------------
        */

        $attentionOrders = Order::with('customer')
            ->where(function ($query) {

                /*
                | Pending Approval
                */

                $query->where(
                    'status',
                    'Pending Approval'
                );


                /*
                | Overdue
                */

                $query->orWhere(function ($query) {

                    $query
                        ->whereNotIn(
                            'status',
                            [
                                'Completed',
                                'Cancelled',
                            ]
                        )
                        ->whereNotNull('due_date')
                        ->whereDate(
                            'due_date',
                            '<',
                            Carbon::today()
                        );

                });

            })
            ->orderBy('due_date')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::with('customer')
            ->latest()
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Designer Workload
        |--------------------------------------------------------------------------
        |
        | We don't depend on a User relationship here.
        | Instead, count active orders directly from Order.
        |
        */

        $designers = User::role('designer')
            ->get();


        foreach ($designers as $designer) {

            $designer->active_orders_count = Order::where(
                'designer_id',
                $designer->id
            )
            ->whereNotIn(
                'status',
                [
                    'Completed',
                    'Cancelled',
                ]
            )
            ->count();

        }


        /*
|--------------------------------------------------------------------------
| Return Admin Dashboard
|--------------------------------------------------------------------------
*/

$stats = [

    'todayOrders' => $todayOrders,

    'activeOrders' => $activeOrders,

    'dueSoon' => $dueSoon,

    'needsAttention' => $needsAttention,

];

return view('admin.dashboard', [

    'stats' => $stats,

    'attentionOrders' => $attentionOrders,

    'recentOrders' => $recentOrders,

    'designers' => $designers,

]);

dd($stats);
}
}