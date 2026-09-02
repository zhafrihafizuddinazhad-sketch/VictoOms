<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements
    FromCollection,
    WithStyles,
    WithEvents,
    ShouldAutoSize
{
    /**
     * Generate report data.
     */
    public function collection()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | ORDER SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalOrders = Order::count();

        $completedOrders = Order::where(
            'status',
            'Completed'
        )->count();

        $pendingOrders = Order::where(
            'status',
            'Pending'
        )->count();

        $inProgressOrders = Order::where(
            'status',
            'In Progress'
        )->count();

        $pendingApprovalOrders = Order::where(
            'status',
            'Pending Approval'
        )->count();

        $printingOrders = Order::where(
            'status',
            'Printing'
        )->count();

        $overdueOrders = Order::whereDate(
            'due_date',
            '<',
            today()
        )
        ->where(
            'status',
            '!=',
            'Completed'
        )
        ->count();

        $completionRate = $totalOrders > 0
            ? round(
                ($completedOrders / $totalOrders) * 100,
                1
            )
            : 0;


        /*
        |--------------------------------------------------------------------------
        | SALES SUMMARY
        |--------------------------------------------------------------------------
        */

        $monthlyOrders = Order::whereBetween(
            'created_at',
            [
                $startOfMonth,
                $endOfMonth
            ]
        )->count();

        $monthlySales = OrderItem::whereHas(
            'order',
            function ($query) use (
                $startOfMonth,
                $endOfMonth
            ) {
                $query->whereBetween(
                    'created_at',
                    [
                        $startOfMonth,
                        $endOfMonth
                    ]
                );
            }
        )->sum('subtotal');

        $totalSales = OrderItem::sum('subtotal');


        /*
        |--------------------------------------------------------------------------
        | ORDER TYPE
        |--------------------------------------------------------------------------
        */

        $newOrders = Order::where(
            'is_repeat_order',
            false
        )->count();

        $repeatOrders = Order::where(
            'is_repeat_order',
            true
        )->count();


        /*
        |--------------------------------------------------------------------------
        | DESIGNER PERFORMANCE
        |--------------------------------------------------------------------------
        */

        $designers = User::role('designer')->get();

        $designerPerformance = [];

        foreach ($designers as $designer) {

            $assigned = Order::where(
                'designer_id',
                $designer->id
            )->count();

            $completed = Order::where(
                'designer_id',
                $designer->id
            )
            ->where(
                'status',
                'Completed'
            )
            ->count();

            $pendingApproval = Order::where(
                'designer_id',
                $designer->id
            )
            ->where(
                'status',
                'Pending Approval'
            )
            ->count();

            $designerPerformance[] = [
                $designer->name,
                $assigned,
                $completed,
                $pendingApproval,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | TOP CUSTOMERS
        |--------------------------------------------------------------------------
        */

        $topCustomers = Order::with('customer')
            ->selectRaw(
                'customer_id, COUNT(*) as orders_count'
            )
            ->groupBy('customer_id')
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | BUILD EXCEL ROWS
        |--------------------------------------------------------------------------
        */

        $rows = new Collection();


        /*
        |--------------------------------------------------------------------------
        | REPORT HEADER
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'VictoOMS',
            '',
        ]);

        $rows->push([
            'BUSINESS PERFORMANCE REPORT',
            '',
        ]);

        $rows->push([
            'Report Type',
            'Current Month',
        ]);

        $rows->push([
            'Report Month',
            now()->format('F Y'),
        ]);

        $rows->push([
            '',
            '',
        ]);


        /*
        |--------------------------------------------------------------------------
        | ORDER SUMMARY
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'ORDER SUMMARY',
            '',
        ]);

        $rows->push([
            'Total Orders',
            $totalOrders,
        ]);

        $rows->push([
            'Completed Orders',
            $completedOrders,
        ]);

        $rows->push([
            'Pending Orders',
            $pendingOrders,
        ]);

        $rows->push([
            'In Progress Orders',
            $inProgressOrders,
        ]);

        $rows->push([
            'Pending Approval Orders',
            $pendingApprovalOrders,
        ]);

        $rows->push([
            'Printing Orders',
            $printingOrders,
        ]);

        $rows->push([
            'Overdue Orders',
            $overdueOrders,
        ]);

        $rows->push([
            'Completion Rate',
            $completionRate / 100,
        ]);

        $rows->push([
            '',
            '',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SALES SUMMARY
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'SALES SUMMARY',
            '',
        ]);

        $rows->push([
            'Current Month Orders',
            $monthlyOrders,
        ]);

        $rows->push([
            'Current Month Sales',
            $monthlySales,
        ]);

        $rows->push([
            'Total Sales',
            $totalSales,
        ]);

        $rows->push([
            '',
            '',
        ]);


        /*
        |--------------------------------------------------------------------------
        | ORDER TYPE
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'ORDER TYPE',
            '',
        ]);

        $rows->push([
            'New Orders',
            $newOrders,
        ]);

        $rows->push([
            'Repeat Orders',
            $repeatOrders,
        ]);

        $rows->push([
            '',
            '',
        ]);


        /*
        |--------------------------------------------------------------------------
        | DESIGNER PERFORMANCE
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'DESIGNER PERFORMANCE',
            '',
            '',
            '',
        ]);

        $rows->push([
            'Designer',
            'Assigned Orders',
            'Completed Orders',
            'Pending Approval',
        ]);

        foreach ($designerPerformance as $designer) {

            $rows->push([
                $designer[0],
                $designer[1],
                $designer[2],
                $designer[3],
            ]);
        }

        $rows->push([
            '',
            '',
            '',
            '',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TOP CUSTOMERS
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'TOP CUSTOMERS',
            '',
        ]);

        $rows->push([
            'Customer',
            'Orders',
        ]);

        foreach ($topCustomers as $customer) {

            $customerName = $customer->customer
                ? $customer->customer->name
                : 'Unknown Customer';

            $rows->push([
                $customerName,
                $customer->orders_count,
            ]);
        }


        return $rows;
    }


    /**
     * Basic worksheet styling.
     */
    public function styles(Worksheet $sheet)
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Main title
            |--------------------------------------------------------------------------
            */

            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 18,
                ],
            ],

            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 13,
                ],
            ],

        ];
    }


    /**
     * Advanced Excel styling.
     */
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();


                /*
                |--------------------------------------------------------------------------
                | Merge Header
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');


                /*
                |--------------------------------------------------------------------------
                | Column Width
                |--------------------------------------------------------------------------
                */

                $sheet->getColumnDimension('A')
                    ->setWidth(32);

                $sheet->getColumnDimension('B')
                    ->setWidth(22);

                $sheet->getColumnDimension('C')
                    ->setWidth(22);

                $sheet->getColumnDimension('D')
                    ->setWidth(22);


                /*
                |--------------------------------------------------------------------------
                | General Font
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:D100')
                    ->getFont()
                    ->setName('Arial')
                    ->setSize(10);


                /*
                |--------------------------------------------------------------------------
                | Main Header
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:D1')->applyFromArray([

                    'font' => [
                        'bold' => true,
                        'size' => 18,
                    ],

                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],

                ]);

                $sheet->getRowDimension(1)
                    ->setRowHeight(30);


                /*
                |--------------------------------------------------------------------------
                | Report Title
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A2:D2')->applyFromArray([

                    'font' => [
                        'bold' => true,
                        'size' => 13,
                    ],

                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],

                ]);

                $sheet->getRowDimension(2)
                    ->setRowHeight(24);


                /*
                |--------------------------------------------------------------------------
                | Report Information
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A3:B4')->applyFromArray([

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],

                ]);

                $sheet->getStyle('A3:A4')->getFont()
                    ->setBold(true);


                /*
                |--------------------------------------------------------------------------
                | Section Headers
                |--------------------------------------------------------------------------
                */

                $sectionRows = [
                    6,   // ORDER SUMMARY
                    16,  // SALES SUMMARY
                    21,  // ORDER TYPE
                    25,  // DESIGNER PERFORMANCE
                    29,  // TOP CUSTOMERS
                ];


                foreach ($sectionRows as $row) {

                    $sheet->mergeCells(
                        "A{$row}:D{$row}"
                    );

                    $sheet->getStyle(
                        "A{$row}:D{$row}"
                    )->applyFromArray([

                        'font' => [
                            'bold' => true,
                            'size' => 11,
                        ],

                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_LEFT,
                        ],

                        'borders' => [
                            'bottom' => [
                                'borderStyle' =>
                                    Border::BORDER_THIN,
                            ],
                        ],

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Order Summary Borders
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A6:D14')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );


                /*
                |--------------------------------------------------------------------------
                | Sales Summary Borders
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A16:D19')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );


                /*
                |--------------------------------------------------------------------------
                | Order Type Borders
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A21:D23')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );


                /*
                |--------------------------------------------------------------------------
                | Designer Performance
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A26:D26')->applyFromArray([

                    'font' => [
                        'bold' => true,
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,
                        ],
                    ],

                ]);


                /*
                |--------------------------------------------------------------------------
                | Top Customers
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A30:B34')->applyFromArray([

                    'font' => [
                        'bold' => true,
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,
                        ],
                    ],

                ]);


                /*
                |--------------------------------------------------------------------------
                | Currency Formatting
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('B18:B19')
                    ->getNumberFormat()
                    ->setFormatCode(
                        '"RM" #,##0.00'
                    );


                /*
                |--------------------------------------------------------------------------
                | Percentage Formatting
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('B14')
                    ->getNumberFormat()
                    ->setFormatCode(
                        '0.0%'
                    );


                /*
                |--------------------------------------------------------------------------
                | Align Numbers
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('B6:D100')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );


                /*
                |--------------------------------------------------------------------------
                | Freeze Header
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A5');


                /*
                |--------------------------------------------------------------------------
                | Print Settings
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet->getPageSetup()
                    ->setPaperSize(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                    );

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->getPageSetup()
                    ->setFitToHeight(0);

                $sheet->setShowGridlines(false);
            },

        ];
    }
}