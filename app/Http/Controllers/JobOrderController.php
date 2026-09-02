<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use App\Models\JobOrderItem;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;
use App\Models\JobOrderImage;

class JobOrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Create Job Order
    |--------------------------------------------------------------------------
    */

    public function create(Order $order)
    {
        $order->load([
            'customer',
            'items',
        ]);

        return view(
            'job_orders.create',
            compact('order')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Job Order
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Order $order
    ) {

        $validated = $request->validate([

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_name' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.rows' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.rows.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.rows.*.number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'items.*.rows.*.size' => [
                'required',
                'string',
                'max:20',
            ],

            'items.*.rows.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'job_order_images' => [
            'nullable',
            'array',
            ],

            'job_order_images.*' => [
            'image',
            'mimes:jpg,jpeg,png',
            'max:5120',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Create Job Order
        |--------------------------------------------------------------------------
        */

        $jobOrder = DB::transaction(function () use (
            $validated,
            $order
        ) {

            $nextId =
                (JobOrder::max('id') ?? 0) + 1;


            $jobOrderNo =
                'JO-' .
                str_pad(
                    $nextId,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            $jobOrder =
                JobOrder::create([

                    'order_id' =>
                        $order->id,

                    'job_order_no' =>
                        $jobOrderNo,

                    'created_by' =>
                        auth()->id(),

                    'status' =>
                        'Draft',

                    'remarks' =>
                        $validated['remarks']
                        ?? null,

                ]);


            /*
            |--------------------------------------------------------------------------
            | Create Job Order Items
            |--------------------------------------------------------------------------
            */

            foreach (
                $validated['items']
                as $item
            ) {

                foreach (
                    $item['rows']
                    as $row
                ) {

                    $name =
                        isset($row['name'])
                            ? trim($row['name'])
                            : null;


                    if ($name === '') {
                        $name = null;
                    }


                    $number =
                        isset($row['number'])
                            ? trim($row['number'])
                            : null;


                    if ($number === '') {
                        $number = null;
                    }


                    JobOrderItem::create([

                        'job_order_id' =>
                            $jobOrder->id,

                        'item_name' =>
                            $item['item_name'],

                        'size' =>
                            $row['size'],

                        'quantity' =>
                            (int) $row['quantity'],

                        'name' =>
                            $name,

                        'number' =>
                            $number,

                    ]);

                }

            }


            return $jobOrder;

        });


       /*
|--------------------------------------------------------------------------
| Store Job Order Images
|--------------------------------------------------------------------------
*/

if ($request->hasFile('job_order_images')) {

    foreach (
        $request->file('job_order_images')
        as $image
    ) {

        $imageName =
            $jobOrder->job_order_no .
            '_' .
            time() .
            '_' .
            uniqid() .
            '.' .
            $image->getClientOriginalExtension();


        $imagePath =
            $image->storeAs(
                'job-orders/images',
                $imageName,
                'public'
            );


        JobOrderImage::create([

            'job_order_id' =>
                $jobOrder->id,

            'image_name' =>
                $image->getClientOriginalName(),

            'image_path' =>
                $imagePath,

        ]);

    }

}


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'designer.task.show',
                $order
            )
            ->with(
                'success',
                'Job Order created successfully.'
            );
    }

    /*
|--------------------------------------------------------------------------
| Delete Job Order
|--------------------------------------------------------------------------
*/

public function destroy(JobOrder $jobOrder)
{
    /*
    |--------------------------------------------------------------------------
    | Only Designer Can Delete
    |--------------------------------------------------------------------------
    */

    if (!auth()->user()->hasRole('designer')) {
        abort(403);
    }


    /*
    |--------------------------------------------------------------------------
    | Only The Creator Can Delete
    |--------------------------------------------------------------------------
    */

    if ($jobOrder->created_by != auth()->id()) {
        abort(403);
    }


    /*
    |--------------------------------------------------------------------------
    | Only Draft Job Orders Can Be Deleted
    |--------------------------------------------------------------------------
    */

    if ($jobOrder->status !== 'Draft') {

        return back()->with(
            'error',
            'Only Draft Job Orders can be deleted.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Related Data
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($jobOrder) {

        /*
        |--------------------------------------------------------------------------
        | Delete Job Order Items
        |--------------------------------------------------------------------------
        */

        $jobOrder->items()->delete();


        /*
        |--------------------------------------------------------------------------
        | Delete Job Order Image
        |--------------------------------------------------------------------------
        */

        if ($jobOrder->image_path) {

            Storage::disk('public')->delete(
                $jobOrder->image_path
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Delete Job Order
        |--------------------------------------------------------------------------
        */

        $jobOrder->delete();

    });


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    return back()->with(
        'success',
        'Job Order deleted successfully.'
    );
}

    /*
    |--------------------------------------------------------------------------
    | Generate Word
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| Generate Word
|--------------------------------------------------------------------------
*/

public function generateWord(
    JobOrder $jobOrder
) {

    $jobOrder->load([
        'order.customer',
        'items',
        'images',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Create Word Document
    |--------------------------------------------------------------------------
    */

    $phpWord = new PhpWord();


    /*
    |--------------------------------------------------------------------------
    | Default Font
    |--------------------------------------------------------------------------
    */

    $phpWord->setDefaultFontName('Arial');
    $phpWord->setDefaultFontSize(10);


    /*
    |--------------------------------------------------------------------------
    | Section
    |--------------------------------------------------------------------------
    */

    $section = $phpWord->addSection([

        'marginTop' =>
            700,

        'marginBottom' =>
            700,

        'marginLeft' =>
            900,

        'marginRight' =>
            900,

    ]);


    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    */

    $titleStyle = [

        'bold' => true,

        'size' => 22,

    ];


    $jobOrderNoStyle = [

        'bold' => true,

        'size' => 13,

    ];


    $sectionTitleStyle = [

        'bold' => true,

        'size' => 13,

    ];


    $normalStyle = [

        'size' => 10,

    ];


    /*
    |--------------------------------------------------------------------------
    | TITLE
    |--------------------------------------------------------------------------
    */

    $section->addText(

        'JOB ORDER',

        $titleStyle,

        [

            'alignment' =>
                'center',

            'spaceAfter' =>
                80,

        ]

    );


    /*
    |--------------------------------------------------------------------------
    | Job Order Number
    |--------------------------------------------------------------------------
    */

    $section->addText(

        $jobOrder->job_order_no,

        $jobOrderNoStyle,

        [

            'alignment' =>
                'center',

            'spaceAfter' =>
                250,

        ]

    );


    /*
    |--------------------------------------------------------------------------
    | ORDER INFORMATION
    |--------------------------------------------------------------------------
    */

    $infoTable =
        $section->addTable([

            'borderSize' =>
                6,

            'borderColor' =>
                'D9D9D9',

            'cellMargin' =>
                100,

            'alignment' =>
                'center',

        ]);


    $infoRows = [

        [
            'Job Order No',
            $jobOrder->job_order_no,
        ],

        [
            'Order No',
            $jobOrder->order->order_no,
        ],

        [
            'Customer',
            $jobOrder
                ->order
                ->customer
                ->customer_name,
        ],

        [
            'Due Date',
            $jobOrder->order->due_date,
        ],

    ];


    foreach ($infoRows as $row) {

        $infoTable->addRow();


        $infoTable
            ->addCell(
                2500,
                [
                    'bgColor' => 'F2F2F2',
                    'valign' => 'center',
                ]
            )
            ->addText(
                $row[0],
                [
                    'bold' => true,
                    'size' => 10,
                ],
                [
                    'alignment' => 'center',
                ]
            );


        $infoTable
            ->addCell(
                4500,
                [
                    'valign' => 'center',
                ]
            )
            ->addText(
                $row[1] ?? '-',
                $normalStyle,
                [
                    'alignment' => 'center',
                ]
            );

    }


    $section->addTextBreak(1);


    /*
    |--------------------------------------------------------------------------
    | JOB ORDER IMAGES
    |--------------------------------------------------------------------------
    */

    if ($jobOrder->images->count() > 0) {

        $section->addText(

            'JOB ORDER IMAGES',

            $sectionTitleStyle,

            [

                'alignment' =>
                    'center',

                'spaceAfter' =>
                    150,

            ]

        );


        foreach (
            $jobOrder->images
            as $jobOrderImage
        ) {

            if (
                empty(
                    $jobOrderImage->image_path
                )
            ) {
                continue;
            }


            $imagePath =
                Storage::disk('public')
                    ->path(
                        $jobOrderImage->image_path
                    );


            if (
                !file_exists($imagePath)
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Image File
            |--------------------------------------------------------------------------
            */

            $section->addImage(

                $imagePath,

                [

                    'width' =>
                        420,

                    'alignment' =>
                        'center',

                    'wrappingStyle' =>
                        'inline',

                    'marginTop' =>
                        100,

                    'marginBottom' =>
                        100,

                ]

            );


            /*
            |--------------------------------------------------------------------------
            | Image Name
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $jobOrderImage->image_name
                )
            ) {

                $section->addText(

                    $jobOrderImage->image_name,

                    [

                        'size' =>
                            8,

                        'color' =>
                            '808080',

                    ],

                    [

                        'alignment' =>
                            'center',

                        'spaceAfter' =>
                            150,

                    ]

                );

            }

        }


        $section->addTextBreak(1);

    }


    /*
    |--------------------------------------------------------------------------
    | GROUP ITEMS
    |--------------------------------------------------------------------------
    */

    $groupedItems =
        $jobOrder
            ->items
            ->groupBy('item_name');


    foreach (
        $groupedItems as
        $itemName => $items
    ) {


        /*
        |--------------------------------------------------------------------------
        | Item Name
        |--------------------------------------------------------------------------
        */

        $section->addText(

            strtoupper($itemName),

            [

                'bold' =>
                    true,

                'size' =>
                    14,

            ],

            [

                'alignment' =>
                    'center',

                'spaceBefore' =>
                    150,

                'spaceAfter' =>
                    150,

            ]

        );


        /*
        |--------------------------------------------------------------------------
        | Determine Name / Number Columns
        |--------------------------------------------------------------------------
        */

        $showName =
            $items->contains(
                function ($item) {

                    return !empty(
                        $item->name
                    );

                }
            );


        $showNumber =
            $items->contains(
                function ($item) {

                    return !empty(
                        $item->number
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        $table =
            $section->addTable([

                'borderSize' =>
                    8,

                'borderColor' =>
                    'BFBFBF',

                'cellMargin' =>
                    100,

                'alignment' =>
                    'center',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $table->addRow();


        if ($showName) {

            $table
                ->addCell(
                    2500,
                    [
                        'bgColor' => 'E9ECEF',
                        'valign' => 'center',
                    ]
                )
                ->addText(
                    'NAME',
                    [
                        'bold' => true,
                        'size' => 10,
                    ],
                    [
                        'alignment' => 'center',
                    ]
                );

        }


        if ($showNumber) {

            $table
                ->addCell(
                    1800,
                    [
                        'bgColor' => 'E9ECEF',
                        'valign' => 'center',
                    ]
                )
                ->addText(
                    'NUMBER',
                    [
                        'bold' => true,
                        'size' => 10,
                    ],
                    [
                        'alignment' => 'center',
                    ]
                );

        }


        $table
            ->addCell(
                1500,
                [
                    'bgColor' => 'E9ECEF',
                    'valign' => 'center',
                ]
            )
            ->addText(
                'SIZE',
                [
                    'bold' => true,
                    'size' => 10,
                ],
                [
                    'alignment' => 'center',
                ]
            );


        $table
            ->addCell(
                1800,
                [
                    'bgColor' => 'E9ECEF',
                    'valign' => 'center',
                ]
            )
            ->addText(
                'QUANTITY',
                [
                    'bold' => true,
                    'size' => 10,
                ],
                [
                    'alignment' => 'center',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Item Rows
        |--------------------------------------------------------------------------
        */

        $itemTotal = 0;


        foreach (
            $items as $item
        ) {

            $table->addRow();


            if ($showName) {

                $table
                    ->addCell(
                        2500,
                        [
                            'valign' => 'center',
                        ]
                    )
                    ->addText(
                        $item->name ?: '-',
                        $normalStyle,
                        [
                            'alignment' => 'center',
                        ]
                    );

            }


            if ($showNumber) {

                $table
                    ->addCell(
                        1800,
                        [
                            'valign' => 'center',
                        ]
                    )
                    ->addText(
                        $item->number ?: '-',
                        $normalStyle,
                        [
                            'alignment' => 'center',
                        ]
                    );

            }


            $table
                ->addCell(
                    1500,
                    [
                        'valign' => 'center',
                    ]
                )
                ->addText(
                    $item->size,
                    $normalStyle,
                    [
                        'alignment' => 'center',
                    ]
                );


            $table
                ->addCell(
                    1800,
                    [
                        'valign' => 'center',
                    ]
                )
                ->addText(
                    (string) $item->quantity,
                    $normalStyle,
                    [
                        'alignment' => 'center',
                    ]
                );


            $itemTotal +=
                $item->quantity;

        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $totalLabelWidth =
            1500 +
            1800;


        if ($showName) {

            $totalLabelWidth +=
                2500;

        }


        if ($showNumber) {

            $totalLabelWidth +=
                1800;

        }


        $table->addRow();


        $table
            ->addCell(
                $totalLabelWidth,
                [
                    'bgColor' => 'F2F2F2',
                    'valign' => 'center',
                ]
            )
            ->addText(
                'TOTAL',
                [
                    'bold' => true,
                    'size' => 10,
                ],
                [
                    'alignment' => 'center',
                ]
            );


        $table
            ->addCell(
                1800,
                [
                    'bgColor' => 'F2F2F2',
                    'valign' => 'center',
                ]
            )
            ->addText(
                $itemTotal . ' PCS',
                [
                    'bold' => true,
                    'size' => 10,
                ],
                [
                    'alignment' => 'center',
                ]
            );


        $section->addTextBreak(1);

    }


    /*
    |--------------------------------------------------------------------------
    | GRAND TOTAL
    |--------------------------------------------------------------------------
    */

    $grandTotal =
        $jobOrder
            ->items
            ->sum('quantity');


    $section->addText(

        'GRAND TOTAL: ' .
        $grandTotal .
        ' PCS',

        [
            'bold' => true,
            'size' => 15,
        ],

        [
            'alignment' => 'center',
            'spaceBefore' => 100,
            'spaceAfter' => 200,
        ]

    );


    /*
    |--------------------------------------------------------------------------
    | REMARKS
    |--------------------------------------------------------------------------
    */

    if ($jobOrder->remarks) {

        $section->addText(

            'REMARKS',

            [
                'bold' => true,
                'size' => 12,
            ],

            [
                'alignment' => 'center',
                'spaceAfter' => 80,
            ]

        );


        $section->addText(

            $jobOrder->remarks,

            [
                'size' => 10,
            ],

            [
                'alignment' => 'center',
                'spaceAfter' => 100,
            ]

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */

    $footer =
        $section->addFooter();


    $footer->addText(

        'Victo OMS',

        [
            'size' => 8,
            'color' => '808080',
        ],

        [
            'alignment' => 'center',
        ]

    );


    /*
    |--------------------------------------------------------------------------
    | Generate DOCX
    |--------------------------------------------------------------------------
    */

    $fileName =
        $jobOrder->job_order_no .
        '.docx';


    $tempPath =
        storage_path(
            'app/' .
            $fileName
        );


    $writer =
        IOFactory::createWriter(
            $phpWord,
            'Word2007'
        );


    $writer->save(
        $tempPath
    );


    return response()
        ->download(
            $tempPath,
            $fileName
        )
        ->deleteFileAfterSend(true);
}
}