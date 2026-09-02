<?php

namespace App\Http\Controllers\Cameraman;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index()
{
    $ready = Order::where('status', 'Ready at HQ')->count();

    $inProgress = Order::where('status', 'Photo Session')->count();

    $completedToday = Order::where('status', 'Photo Completed')
        ->whereDate('updated_at', today())
        ->count();

    $totalCompleted = Order::where('status', 'Photo Completed')->count();

    $recentTasks = Order::with('customer')
        ->whereIn('status', [
            'Ready at HQ',
            'Photo Session'
        ])
        ->latest()
        ->take(5)
        ->get();

    return view('cameraman.dashboard', compact(
        'ready',
        'inProgress',
        'completedToday',
        'totalCompleted',
        'recentTasks'
    ));
}   
}