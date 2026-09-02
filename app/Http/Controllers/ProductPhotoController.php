<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductPhotoController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'photos.*' => 'required|image|max:5120',
            'remarks' => 'nullable|string'
        ]);

        foreach ($request->file('photos') as $photo) {

            $path = $photo->store('product-photos', 'public');

            ProductPhoto::create([
                'order_id' => $order->id,
                'uploaded_by' => auth()->id(),
                'photo_name' => $photo->getClientOriginalName(),
                'photo_path' => $path,
                'remarks' => $request->remarks,
            ]);
        }

        return back()->with(
            'success',
            'Photos uploaded successfully.'
        );
    }

    public function destroy(ProductPhoto $photo)
{
    // Jangan bagi delete kalau photo session dah complete
    if ($photo->order->status != 'Photo Session') {

        return back()->with(
            'error',
            'Photo can no longer be deleted.'
        );
    }

    Storage::disk('public')->delete($photo->photo_path);

    $photo->delete();

    return back()->with(
        'success',
        'Photo deleted successfully.'
    );
}
}