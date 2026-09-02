<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;

class DesignerTaskController extends Controller
{
    public function index()
{
    $query = Order::with('customer')
        ->where('designer_id', auth()->id());

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    if (request('search')) {

        $search = request('search');

        $query->where(function ($q) use ($search) {

            $q->where('order_no', 'like', "%{$search}%")
              ->orWhereHas('customer', function ($c) use ($search) {

                    $c->where('customer_name', 'like', "%{$search}%");

              });

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */

    if (request('status')) {

        $query->where('status', request('status'));

    } else {

        $query->whereIn('status', [

            'Assigned',
            'In Progress',
            'Pending Approval',
            'Printing',
            'Completed'

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    $assigned = Order::where('designer_id', auth()->id())
        ->where('status', 'Assigned')
        ->count();

    $inProgress = Order::where('designer_id', auth()->id())
        ->where('status', 'In Progress')
        ->count();

    $pendingApproval = Order::where('designer_id', auth()->id())
        ->where('status', 'Pending Approval')
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    $orders = $query
        ->orderBy('due_date')
        ->get();

    return view('designer.task.index', compact(

        'orders',

        'assigned',

        'inProgress',

        'pendingApproval'

    ));
}
    public function startTask(Order $order)
    {
        if ($order->designer_id != auth()->id()) {
            abort(403);
        }

    if ($order->status == 'Assigned') {

        $order->update([
            'status' => 'In Progress'
        ]);

        ActivityLog::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'Task Started',
            'description' => auth()->user()->name . ' started working on this order.'
        ]);

    }

    return redirect()->route('designer.task')
        ->with('success', 'Task started successfully.');
}

public function show(Order $order)
{
    // Pastikan designer hanya boleh buka order dia sendiri
    if ($order->designer_id != auth()->id()) {
        abort(403);
    }

    $order->load([
    'customer',
    'items',
    'references',
    'activityLogs.user',
    'designFiles.uploader',

    // Repeat Order
    'originalOrder',
    'originalOrder.designFiles.uploader',
    'originalOrder.designer',

    // Job Orders
    'jobOrders.items',
    'jobOrders.creator',
]);

    return view(
        'designer.task.show',
        compact('order')
    );
}

public function submitForApproval(Order $order)
{
    /*
    |--------------------------------------------------------------------------
    | Make sure this designer owns the order
    |--------------------------------------------------------------------------
    */

    if ($order->designer_id != auth()->id()) {

        abort(403);

    }


    /*
    |--------------------------------------------------------------------------
    | Make sure a Job Order exists
    |--------------------------------------------------------------------------
    */

    if (!$order->jobOrders()->exists()) {

        return back()->with(
            'error',
            'Please create a Job Order before submitting for approval.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Design Requirement
    |--------------------------------------------------------------------------
    |
    | Same Design:
    | No new design file is required because the original design
    | is being reused.
    |
    | Minor Changes / Normal Order:
    | At least one design file must exist.
    |
    */

    if (
    !$order->is_repeat_order ||
    $order->repeat_type === 'minor_changes'
) {

    if ($order->designFiles()->count() == 0) {

        if (
            $order->is_repeat_order &&
            $order->repeat_type === 'minor_changes'
        ) {

            return back()->with(
                'error',
                'Please upload the revised design before submitting this Minor Changes order for approval.'
            );

        }


        return back()->with(
            'error',
            'Please upload at least one design before submitting.'
        );

    }

}


    /*
    |--------------------------------------------------------------------------
    | Submit For Owner Approval
    |--------------------------------------------------------------------------
    */

    $order->update([

        'status' =>
            'Pending Approval',

        'owner_review_comment' =>
            null,

        'reviewed_by' =>
            null,

        'reviewed_at' =>
            null,

    ]);


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    ActivityLog::create([

        'order_id' =>
            $order->id,

        'action' =>
            'Submitted For Approval',

        'description' =>
            'Designer submitted the order and Job Order for owner review.',

        'user_id' =>
            auth()->id(),

    ]);


    /*
    |--------------------------------------------------------------------------
    | Notify Owners
    |--------------------------------------------------------------------------
    */

    $owners =
        User::role('owner')->get();


    foreach ($owners as $owner) {

        Notification::create([

            'user_id' =>
                $owner->id,

            'order_id' =>
                $order->id,

            'title' =>
                'Order Waiting Approval',

            'message' =>
                "Order {$order->order_no} is waiting for your approval.",

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    return back()->with(
        'success',
        'Order and Job Order submitted for approval.'
    );
}
}