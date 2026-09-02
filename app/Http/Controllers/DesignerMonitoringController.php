<?php

namespace App\Http\Controllers;

use App\Models\User;

class DesignerMonitoringController extends Controller
{
    public function index()
    {
        $designers = User::role('designer')
            ->withCount([

                'assignedOrders as active_orders' => function ($query) {
                    $query->whereIn('status', [
                        'Assigned',
                        'In Progress',
                        'Pending Approval',
                        'Printing',
                        'Ready at HQ',
                        'Out for Delivery',
                        'Waiting for Pickup',
                    ]);
                },

                'assignedOrders as pending_approval' => function ($query) {
                    $query->where('status', 'Pending Approval');
                },

                'assignedOrders as completed_orders' => function ($query) {
                    $query->where('status', 'Completed');
                }

            ])
            ->orderBy('name')
            ->get();

        return view('designer-monitoring.index', compact('designers'));
    }

    public function show(User $designer)
{
    $orders = $designer->assignedOrders()
        ->with('customer')
        ->latest()
        ->get();

    $totalOrders = $orders->count();

    $activeOrders = $orders->whereIn('status', [
        'Assigned',
        'In Progress'
    ])->count();

    $pendingApproval = $orders->where('status', 'Pending Approval')->count();

    $completedOrders = $orders->where('status', 'Completed')->count();

    return view('designer-monitoring.show', compact(
        'designer',
        'orders',
        'totalOrders',
        'activeOrders',
        'pendingApproval',
        'completedOrders'
    ));
}
}