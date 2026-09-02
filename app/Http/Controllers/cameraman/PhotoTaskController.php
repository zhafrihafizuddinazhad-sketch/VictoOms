<?php

namespace App\Http\Controllers\Cameraman;

use App\Http\Controllers\Controller;
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

public function complete(Order $order)
{
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

    return redirect()
        ->route('cameraman.tasks')
        ->with(
            'success',
            'Photo session completed successfully.'
        );
}
}