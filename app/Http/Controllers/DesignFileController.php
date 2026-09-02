<?php

namespace App\Http\Controllers;

use App\Models\DesignFile;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignFileController extends Controller
{
   public function store(Request $request, Order $order)
{
    /*
    |--------------------------------------------------------------------------
    | Check Order Status
    |--------------------------------------------------------------------------
    */

    if ($order->status !== 'In Progress') {

        return back()->with(
            'error',
            'Design can only be uploaded while the task is in progress.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Get Uploaded Files
    |--------------------------------------------------------------------------
    |
    | Support both:
    | design_files[]  -> multiple files
    | design_file     -> single file
    |
    */

    $files = [];

    if ($request->hasFile('design_files')) {

        $files = $request->file('design_files');

    } elseif ($request->hasFile('design_file')) {

        $files = [
            $request->file('design_file')
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Make Sure At Least One File Exists
    |--------------------------------------------------------------------------
    */

    if (count($files) === 0) {

        return back()
            ->withInput()
            ->with(
                'error',
                'Please select at least one design file.'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Each File
    |--------------------------------------------------------------------------
    */

    $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'pdf',
        'ai',
        'eps',
        'svg',
        'psd',
        'cdr',
        'otf',
        'ttf',
    ];


    foreach ($files as $file) {

        if (!$file->isValid()) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'One of the uploaded files is invalid.'
                );

        }


        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );


        if (!in_array($extension, $allowedExtensions)) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unsupported file type: ' .
                    $file->getClientOriginalName()
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Maximum 50MB Per File
        |--------------------------------------------------------------------------
        */

        if ($file->getSize() > 50 * 1024 * 1024) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $file->getClientOriginalName() .
                    ' exceeds the 50MB file size limit.'
                );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Find Latest Version
    |--------------------------------------------------------------------------
    */

    $latestVersion =
        DesignFile::where(
            'order_id',
            $order->id
        )->max('version');


    $version =
        $latestVersion
            ? $latestVersion + 1
            : 1;


    /*
    |--------------------------------------------------------------------------
    | Save Every File
    |--------------------------------------------------------------------------
    */

    foreach ($files as $file) {

        /*
        |--------------------------------------------------------------------------
        | Store Physical File
        |--------------------------------------------------------------------------
        */

        $path =
            $file->store(
                'designs',
                'public'
            );


        /*
        |--------------------------------------------------------------------------
        | Create Database Record
        |--------------------------------------------------------------------------
        */

        DesignFile::create([

            'order_id' =>
                $order->id,

            'uploaded_by' =>
                auth()->id(),

            'file_name' =>
                $file->getClientOriginalName(),

            'file_path' =>
                $path,

            'file_extension' =>
                strtolower(
                    $file->getClientOriginalExtension()
                ),

            'version' =>
                $version,

            'remarks' =>
                $request->input('remarks'),

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    return back()->with(
        'success',
        count($files) .
        ' design file(s) uploaded successfully.'
    );
}

    public function destroy(DesignFile $designFile)
{
    $order = $designFile->order;


    /*
    |--------------------------------------------------------------------------
    | Only allow deletion while designer is working
    |--------------------------------------------------------------------------
    */

    if ($order->status !== 'In Progress') {

        return back()->with(
            'error',
            'Design can no longer be deleted after submission.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Find Latest Version
    |--------------------------------------------------------------------------
    */

    $latestVersion = DesignFile::where(
        'order_id',
        $order->id
    )->max('version');


    /*
    |--------------------------------------------------------------------------
    | Only Latest Version Can Be Deleted
    |--------------------------------------------------------------------------
    */

    if ((int) $designFile->version !== (int) $latestVersion) {

        return back()->with(
            'error',
            'Previous design versions cannot be deleted.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Delete File From Storage
    |--------------------------------------------------------------------------
    */

    if (Storage::disk('public')->exists($designFile->file_path)) {

        Storage::disk('public')->delete(
            $designFile->file_path
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Database Record
    |--------------------------------------------------------------------------
    */

    $designFile->delete();


    return back()->with(
        'success',
        'Latest design version deleted successfully.'
    );
}

    public function download(DesignFile $designFile)
{
    // Owner/Admin boleh download design selepas design mula dikerjakan
    if (!auth()->user()->hasRole('designer')) {

        if (
            $designFile->order->status != 'In Progress' &&
            $designFile->order->status != 'Pending Approval' &&
            $designFile->order->status != 'Printing' &&
            $designFile->order->status != 'Ready at HQ' &&
            $designFile->order->status != 'Out for Delivery' &&
            $designFile->order->status != 'Waiting for Pickup' &&
            $designFile->order->status != 'Completed'
        ) {

            return back()->with(
                'error',
                'Designer has not uploaded a design yet.'
            );

        }

    }

    if (!Storage::disk('public')->exists($designFile->file_path)) {

        return back()->with(
            'error',
            'File not found.'
        );

    }

    return Storage::disk('public')->download(
        $designFile->file_path,
        $designFile->file_name
    );
}

public function preview(DesignFile $designFile)
{
    // Owner/Admin boleh preview design selepas design mula dikerjakan
    if (!auth()->user()->hasRole('designer')) {

        if (
            $designFile->order->status != 'In Progress' &&
            $designFile->order->status != 'Pending Approval' &&
            $designFile->order->status != 'Printing' &&
            $designFile->order->status != 'Ready at HQ' &&
            $designFile->order->status != 'Out for Delivery' &&
            $designFile->order->status != 'Waiting for Pickup' &&
            $designFile->order->status != 'Completed'
        ) {

            return back()->with(
                'error',
                'Designer has not uploaded a design yet.'
            );

        }

    }

    if (!Storage::disk('public')->exists($designFile->file_path)) {

        abort(404);

    }

    return response()->file(
        storage_path('app/public/' . $designFile->file_path)
    );
}
}