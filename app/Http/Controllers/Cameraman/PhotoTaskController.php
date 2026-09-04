<?php

namespace App\Http\Controllers\Cameraman;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;

class PhotoTaskController extends Controller
{
   public function index()
{
    $orders = Order::with('customer')

        ->where('cameraman_id', auth()->id())

        ->whereIn('status', [
            'Ready at HQ',
            'Photo Session',
            'Photo Completed',
        ])

        ->orderBy('due_date')

        ->paginate(10);

    return view('cameraman.tasks.index', compact('orders'));
}

public function show(Order $order)
{
    abort_unless($order->cameraman_id === auth()->id(), 403);

    // Kalau order masih Ready at HQ,
    // automatik tukar kepada Photo Session

    if ($order->status == 'Ready at HQ') {

        $order->update([
            'status' => 'Photo Session',
        ]);

    }
    $productPhotos = $order->productPhotos()->latest()->get();
    return view(
    'cameraman.tasks.show',
    compact('order', 'productPhotos')
);
}

public function start(Order $order)
{
    abort_unless($order->cameraman_id === auth()->id(), 403);

    if ($order->status !== 'Ready at HQ') {
        return back()->with('error', 'This photo session cannot be started yet.');
    }

    $order->update(['status' => 'Photo Session']);

    ActivityLog::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'action' => 'Photo Session Started',
        'description' => auth()->user()->name . ' started the product photo session.',
    ]);

    return redirect()->route('cameraman.tasks.show', $order)
        ->with('success', 'Photo session started successfully.');
}

public function complete(Order $order)
{
    abort_unless($order->cameraman_id === auth()->id(), 403);

    if ($order->status !== 'Photo Session') {
        return back()->with('error', 'This photo session is not ready to be completed.');
    }

    // Pastikan ada sekurang-kurangnya satu gambar
    if ($order->productPhotos()->count() == 0) {

        return back()->with(
            'error',
            'Please upload at least one photo.'
        );

    }

    $order->update([
        'status' => 'Photo Completed',
    ]);

    ActivityLog::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'action' => 'Photo Session Completed',
        'description' => auth()->user()->name . ' completed the product photo session.',
    ]);

    return redirect()
        ->route('cameraman.tasks')
        ->with(
            'success',
            'Photo session completed successfully.'
        );
}
}
