<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the designer dashboard.
     */
    public function index(): View
    {
        $designer = Auth::user();

        // Current Tasks
        $currentTasks = Order::where('designer_id', $designer->id)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->count();

        // Pending Approval
        $pendingApproval = Order::where('designer_id', $designer->id)
            ->where('status', 'Pending Approval')
            ->count();

        // Completed
        $completedTasks = Order::where('designer_id', $designer->id)
            ->where('status', 'Completed')
            ->count();

        // Overdue
        $overdueTasks = Order::where('designer_id', $designer->id)
            ->whereDate('due_date', '<', now())
            ->whereNotIn('status', ['Completed'])
            ->count();

        // Today's Priority
        $todayPriority = Order::with('customer')    
            ->where('designer_id', $designer->id)
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->orderBy('due_date')
            ->first();

        // Recent Tasks
        $recentTasks = Order::with('customer')
            ->where('designer_id', $designer->id)
            ->latest()
            ->take(5)
            ->get();

        return view('designer.dashboard', compact(
    'currentTasks',
    'pendingApproval',
    'completedTasks',
    'overdueTasks',
    'todayPriority',
    'recentTasks'
));
    }
}