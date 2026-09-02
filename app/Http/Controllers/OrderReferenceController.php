<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderReferenceController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([

            'reference_files.*' => 'nullable|file|max:51200',

            'reference_link' => 'nullable|url',

            'description' => 'nullable|string',

            'title' => 'nullable|string|max:255',

        ]);

       /*
|--------------------------------------------------------------------------
| Save Uploaded Files
|--------------------------------------------------------------------------
*/

if ($request->hasFile('reference_files')) {

    foreach ($request->file('reference_files') as $file) {

        $path = $file->store(
            'references',
            'public'
        );

        OrderReference::create([

            'order_id' => $order->id,

            'uploaded_by' => auth()->id(),

            'title' => $file->getClientOriginalName(),

            'description' => $request->description,

            'reference_link' => null,

            'file_name' => $file->getClientOriginalName(),

            'file_path' => $path,

            'file_extension' => $file->getClientOriginalExtension(),

        ]);

    }

}

/*
|--------------------------------------------------------------------------
| Save Link
|--------------------------------------------------------------------------
*/

if ($request->reference_link) {

    OrderReference::create([

        'order_id' => $order->id,

        'uploaded_by' => auth()->id(),

        'title' => parse_url(
            $request->reference_link,
            PHP_URL_HOST
        ),

        'description' => $request->description,

        'reference_link' => $request->reference_link,

        'file_name' => null,

        'file_path' => null,

        'file_extension' => null,

    ]);

}

        return back()->with(
            'success',
            'Customer reference added successfully.'
        );
    }

    public function destroy(OrderReference $reference)
    {
        if ($reference->file_path) {

            Storage::disk('public')
                ->delete($reference->file_path);

        }

        $reference->delete();

        return back()->with(
            'success',
            'Reference deleted successfully.'
        );
    }

    public function download(OrderReference $reference)
    {
        if (!$reference->file_path) {

            return back();

        }

        return Storage::disk('public')->download(

            $reference->file_path,

            $reference->file_name

        );
    }

    public function preview(OrderReference $reference)
    {
        if (!$reference->file_path) {

            abort(404);

        }

        return response()->file(

            storage_path(
                'app/public/' . $reference->file_path
            )

        );
    }
}