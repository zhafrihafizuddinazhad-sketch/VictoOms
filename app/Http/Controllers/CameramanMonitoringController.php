<?php

namespace App\Http\Controllers;

use App\Models\User;

class CameramanMonitoringController extends Controller
{
    public function index()
    {
        $cameramen = User::role('cameraman')
            ->withCount([

                'photoOrders as active_tasks' => function ($query) {
                    $query->whereIn('status', [
                        'Ready at HQ',
                        'Photo Session',
                    ]);
                },

                'photoOrders as ready_hq' => function ($query) {
                    $query->where('status', 'Ready at HQ');
                },

                'photoOrders as photo_session' => function ($query) {
                    $query->where('status', 'Photo Session');
                },

                'photoOrders as completed_tasks' => function ($query) {
                    $query->where('status', 'Photo Completed');
                },

            ])
            ->orderBy('name')
            ->get();

        return view('cameraman-monitoring.index', compact('cameramen'));
    }

    public function show(User $cameraman)
    {
        $orders = $cameraman->photoOrders()
            ->with('customer')
            ->latest()
            ->get();

        $totalOrders = $orders->count();

        $activeTasks = $orders->whereIn('status', [
            'Ready at HQ',
            'Photo Session',
        ])->count();

        $completedToday = $orders->where('status', 'Photo Completed')
            ->filter(function ($order) {
                return $order->updated_at->isToday();
            })
            ->count();

        $completedTasks = $orders->where('status', 'Photo Completed')->count();

        return view('cameraman-monitoring.show', compact(
            'cameraman',
            'orders',
            'totalOrders',
            'activeTasks',
            'completedToday',
            'completedTasks'
        ));
    }
}