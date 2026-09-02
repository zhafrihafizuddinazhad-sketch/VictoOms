<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();

        $totalOrders = Order::count();

        $totalRevenue = Order::with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->sum('subtotal');
            });

        $pending = Order::where('status', 'Pending')->count();

        $assigned = Order::where('status', 'Assigned')->count();

        $inProgress = Order::where('status', 'In Progress')->count();

        $pendingApproval = Order::where(
            'status',
            'Pending Approval'
        )->count();

        $printing = Order::where(
            'status',
            'Printing'
        )->count();

        $readyHQ = Order::where(
            'status',
            'Ready at HQ'
        )->count();

        $photoSession = Order::where(
            'status',
            'Photo Session'
        )->count();

        $photoCompleted = Order::where(
            'status',
            'Photo Completed'
        )->count();

        $outForDelivery = Order::where(
            'status',
            'Out for Delivery'
        )->count();

        $waitingPickup = Order::where(
            'status',
            'Waiting for Pickup'
        )->count();

        $completed = Order::where(
            'status',
            'Completed'
        )->count();

        $attentionOrders = Order::with('customer')
            ->where(function ($query) {

                $query->where(
                    'status',
                    'Pending Approval'
                );

                $query->orWhere(function ($query) {

                    $query
                        ->whereDate('due_date', '<', now())
                        ->whereNotIn('status', [
                            'Completed',
                            'Cancelled',
                        ]);

                });

                $query->orWhere(function ($query) {

                    $query
                        ->whereDate('due_date', '>=', now())
                        ->whereDate(
                            'due_date',
                            '<=',
                            now()->addDays(2)
                        )
                        ->whereNotIn('status', [
                            'Completed',
                            'Cancelled',
                        ]);

                });

            })
            ->orderBy('due_date')
            ->take(8)
            ->get();

        $latestOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $activities = ActivityLog::with([
            'order.customer',
            'user',
        ])
        ->latest()
        ->take(5)
        ->get();

        return view('owner.dashboard', [
            'totalCustomers' => $totalCustomers,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,

            'pending' => $pending,
            'assigned' => $assigned,
            'inProgress' => $inProgress,
            'pendingApproval' => $pendingApproval,
            'printing' => $printing,

            'readyHQ' => $readyHQ,
            'photoSession' => $photoSession,
            'photoCompleted' => $photoCompleted,
            'outForDelivery' => $outForDelivery,
            'waitingPickup' => $waitingPickup,
            'completed' => $completed,

            'attentionOrders' => $attentionOrders,
            'latestOrders' => $latestOrders,
            'activities' => $activities,
        ]);
    }
}