<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications for current user
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
   public function read(Notification $notification)
{
    abort_if($notification->user_id != auth()->id(), 403);

    $notification->update([
        'is_read' => true
    ]);

    if ($notification->order_id) {

        $user = auth()->user();

        if ($user->hasRole('designer')) {

            return redirect()->route('designer.task.show', $notification->order_id);

        }

        if ($user->hasRole('cameraman')) {

            return redirect()->route('cameraman.tasks.show', $notification->order_id);

        }

        return redirect()->route('orders.show', $notification->order_id);

    }

    return back();
}

/**
 * Mark all notifications as read for current user
 */
public function markAllAsRead()
{
    Notification::where('user_id', auth()->id())
        ->where('is_read', false)
        ->update([
            'is_read' => true,
        ]);

    return back()->with(
        'success',
        'All notifications marked as read.'
    );
}
}