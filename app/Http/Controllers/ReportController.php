<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Report Filter
        |--------------------------------------------------------------------------
        */

        $type = $request->get('type', 'monthly');

        $month = (int) $request->get(
            'month',
            now()->month
        );

        $year = (int) $request->get(
            'year',
            now()->year
        );


        /*
        |--------------------------------------------------------------------------
        | Determine Report Period
        |--------------------------------------------------------------------------
        */

        if ($type === 'annual') {

            $startDate = Carbon::create(
                $year,
                1,
                1
            )->startOfDay();

            $endDate = Carbon::create(
                $year,
                12,
                31
            )->endOfDay();

            $periodLabel = (string) $year;

        } else {

            $startDate = Carbon::create(
                $year,
                $month,
                1
            )->startOfMonth();

            $endDate = Carbon::create(
                $year,
                $month,
                1
            )->endOfMonth();

            $periodLabel = $startDate->format('F Y');
        }


        /*
        |--------------------------------------------------------------------------
        | Period Orders
        |--------------------------------------------------------------------------
        */

        $periodOrders = Order::whereBetween(
            'created_at',
            [
                $startDate,
                $endDate
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Order Summary
        |--------------------------------------------------------------------------
        */

        $totalOrders = (clone $periodOrders)->count();

        $completedOrders = (clone $periodOrders)
            ->where('status', 'Completed')
            ->count();

        $pendingOrders = (clone $periodOrders)
            ->where('status', 'Pending')
            ->count();

        $inProgressOrders = (clone $periodOrders)
            ->where('status', 'In Progress')
            ->count();

        $pendingApprovalOrders = (clone $periodOrders)
            ->where('status', 'Pending Approval')
            ->count();

        $printingOrders = (clone $periodOrders)
            ->where('status', 'Printing')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Overdue Orders
        |--------------------------------------------------------------------------
        */

        $overdueOrders = (clone $periodOrders)
            ->whereDate(
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


        /*
        |--------------------------------------------------------------------------
        | Completion Rate
        |--------------------------------------------------------------------------
        */

        $completionRate = $totalOrders > 0
            ? round(
                ($completedOrders / $totalOrders) * 100,
                1
            )
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Period Sales
        |--------------------------------------------------------------------------
        */

        $periodSales = OrderItem::whereHas(
            'order',
            function ($query) use (
                $startDate,
                $endDate
            ) {

                $query->whereBetween(
                    'created_at',
                    [
                        $startDate,
                        $endDate
                    ]
                );

            }
        )->sum('subtotal');


        /*
        |--------------------------------------------------------------------------
        | Overall Sales
        |--------------------------------------------------------------------------
        */

        $totalSales = OrderItem::sum('subtotal');


        /*
        |--------------------------------------------------------------------------
        | New vs Repeat Orders
        |--------------------------------------------------------------------------
        */

        $newOrders = (clone $periodOrders)
            ->where(
                'is_repeat_order',
                false
            )
            ->count();

        $repeatOrders = (clone $periodOrders)
            ->where(
                'is_repeat_order',
                true
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Designer Performance
        |--------------------------------------------------------------------------
        */

        $designerPerformance = User::role('designer')
            ->withCount([
                'assignedOrders as assigned_orders_count' => function ($query) use (
                    $startDate,
                    $endDate
                ) {

                    $query->whereBetween(
                        'created_at',
                        [
                            $startDate,
                            $endDate
                        ]
                    );

                },

                'assignedOrders as completed_orders_count' => function ($query) use (
                    $startDate,
                    $endDate
                ) {

                    $query
                        ->whereBetween(
                            'created_at',
                            [
                                $startDate,
                                $endDate
                            ]
                        )
                        ->where(
                            'status',
                            'Completed'
                        );

                },

                'assignedOrders as pending_approval_orders_count' => function ($query) use (
                    $startDate,
                    $endDate
                ) {

                    $query
                        ->whereBetween(
                            'created_at',
                            [
                                $startDate,
                                $endDate
                            ]
                        )
                        ->where(
                            'status',
                            'Pending Approval'
                        );

                },
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Top Customers
        |--------------------------------------------------------------------------
        */

        $topCustomers = Order::with('customer')
            ->whereBetween(
                'created_at',
                [
                    $startDate,
                    $endDate
                ]
            )
            ->selectRaw(
                'customer_id, COUNT(*) as orders_count'
            )
            ->groupBy('customer_id')
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        */

        $chartLabels = [];
        $salesChartData = [];
        $ordersChartData = [];


        if ($type === 'annual') {

            for ($m = 1; $m <= 12; $m++) {

                $chartDate = Carbon::create(
                    $year,
                    $m,
                    1
                );

                $chartLabels[] =
                    $chartDate->format('M');


                $monthStart =
                    $chartDate->copy()->startOfMonth();

                $monthEnd =
                    $chartDate->copy()->endOfMonth();


                $ordersCount = Order::whereBetween(
                    'created_at',
                    [
                        $monthStart,
                        $monthEnd
                    ]
                )->count();


                $sales = OrderItem::whereHas(
                    'order',
                    function ($query) use (
                        $monthStart,
                        $monthEnd
                    ) {

                        $query->whereBetween(
                            'created_at',
                            [
                                $monthStart,
                                $monthEnd
                            ]
                        );

                    }
                )->sum('subtotal');


                $ordersChartData[] = $ordersCount;
                $salesChartData[] = $sales;
            }

        } else {

            $daysInMonth =
                $startDate->daysInMonth;


            for ($day = 1; $day <= $daysInMonth; $day++) {

                $chartDate = Carbon::create(
                    $year,
                    $month,
                    $day
                );

                $chartLabels[] =
                    $chartDate->format('d');


                $dayStart =
                    $chartDate->copy()->startOfDay();

                $dayEnd =
                    $chartDate->copy()->endOfDay();


                $ordersCount = Order::whereBetween(
                    'created_at',
                    [
                        $dayStart,
                        $dayEnd
                    ]
                )->count();


                $sales = OrderItem::whereHas(
                    'order',
                    function ($query) use (
                        $dayStart,
                        $dayEnd
                    ) {

                        $query->whereBetween(
                            'created_at',
                            [
                                $dayStart,
                                $dayEnd
                            ]
                        );

                    }
                )->sum('subtotal');


                $ordersChartData[] = $ordersCount;
                $salesChartData[] = $sales;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Return Report
        |--------------------------------------------------------------------------
        */

        return view(
            'reports.index',
            compact(
                'type',
                'month',
                'year',
                'periodLabel',

                'totalOrders',
                'completedOrders',
                'pendingOrders',
                'inProgressOrders',
                'pendingApprovalOrders',
                'printingOrders',

                'overdueOrders',
                'completionRate',

                'periodSales',
                'totalSales',

                'newOrders',
                'repeatOrders',

                'designerPerformance',
                'topCustomers',

                'chartLabels',
                'salesChartData',
                'ordersChartData'
            )
        );
    }

    public function exportExcel()
{
    return Excel::download(
        new ReportExport,
        'victooms-report-' . now()->format('Y-m-d') . '.xlsx'
    );
}


    /*
    |--------------------------------------------------------------------------
    | Export PDF
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Same Filter
        |--------------------------------------------------------------------------
        */

        $type = $request->get(
            'type',
            'monthly'
        );

        $month = (int) $request->get(
            'month',
            now()->month
        );

        $year = (int) $request->get(
            'year',
            now()->year
        );


        /*
        |--------------------------------------------------------------------------
        | Report Period
        |--------------------------------------------------------------------------
        */

        if ($type === 'annual') {

            $startDate = Carbon::create(
                $year,
                1,
                1
            )->startOfDay();

            $endDate = Carbon::create(
                $year,
                12,
                31
            )->endOfDay();

            $periodLabel = $year;

        } else {

            $startDate = Carbon::create(
                $year,
                $month,
                1
            )->startOfMonth();

            $endDate = Carbon::create(
                $year,
                $month,
                1
            )->endOfMonth();

            $periodLabel =
                $startDate->format('F Y');
        }


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        $periodOrders = Order::whereBetween(
            'created_at',
            [
                $startDate,
                $endDate
            ]
        );


        $totalOrders =
            (clone $periodOrders)->count();


        $completedOrders =
            (clone $periodOrders)
            ->where(
                'status',
                'Completed'
            )
            ->count();


        $pendingOrders =
            (clone $periodOrders)
            ->where(
                'status',
                'Pending'
            )
            ->count();


        $inProgressOrders =
            (clone $periodOrders)
            ->where(
                'status',
                'In Progress'
            )
            ->count();


        $pendingApprovalOrders =
            (clone $periodOrders)
            ->where(
                'status',
                'Pending Approval'
            )
            ->count();


        $printingOrders =
            (clone $periodOrders)
            ->where(
                'status',
                'Printing'
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Overdue
        |--------------------------------------------------------------------------
        */

        $overdueOrders =
            (clone $periodOrders)
            ->whereDate(
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


        /*
        |--------------------------------------------------------------------------
        | Completion Rate
        |--------------------------------------------------------------------------
        */

        $completionRate =
            $totalOrders > 0
                ? round(
                    ($completedOrders /
                    $totalOrders) * 100,
                    1
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        */

        $periodSales =
            OrderItem::whereHas(
                'order',
                function ($query) use (
                    $startDate,
                    $endDate
                ) {

                    $query->whereBetween(
                        'created_at',
                        [
                            $startDate,
                            $endDate
                        ]
                    );

                }
            )->sum('subtotal');


        /*
        |--------------------------------------------------------------------------
        | New / Repeat
        |--------------------------------------------------------------------------
        */

        $newOrders =
            (clone $periodOrders)
            ->where(
                'is_repeat_order',
                false
            )
            ->count();


        $repeatOrders =
            (clone $periodOrders)
            ->where(
                'is_repeat_order',
                true
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Designer Performance
        |--------------------------------------------------------------------------
        */

        $designerPerformance =
            User::role('designer')
            ->withCount([

                'assignedOrders as assigned_orders_count'
                    => function ($query) use (
                        $startDate,
                        $endDate
                    ) {

                        $query->whereBetween(
                            'created_at',
                            [
                                $startDate,
                                $endDate
                            ]
                        );

                    },


                'assignedOrders as completed_orders_count'
                    => function ($query) use (
                        $startDate,
                        $endDate
                    ) {

                        $query
                            ->whereBetween(
                                'created_at',
                                [
                                    $startDate,
                                    $endDate
                                ]
                            )
                            ->where(
                                'status',
                                'Completed'
                            );

                    },


                'assignedOrders as pending_approval_orders_count'
                    => function ($query) use (
                        $startDate,
                        $endDate
                    ) {

                        $query
                            ->whereBetween(
                                'created_at',
                                [
                                    $startDate,
                                    $endDate
                                ]
                            )
                            ->where(
                                'status',
                                'Pending Approval'
                            );

                    },

            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Top Customers
        |--------------------------------------------------------------------------
        */

        $topCustomers =
            Order::with('customer')
            ->whereBetween(
                'created_at',
                [
                    $startDate,
                    $endDate
                ]
            )
            ->selectRaw(
                'customer_id, COUNT(*) as orders_count'
            )
            ->groupBy('customer_id')
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'reports.pdf',
            compact(
                'periodLabel',

                'totalOrders',
                'completedOrders',
                'pendingOrders',
                'inProgressOrders',
                'pendingApprovalOrders',
                'printingOrders',

                'overdueOrders',
                'completionRate',

                'periodSales',

                'newOrders',
                'repeatOrders',

                'designerPerformance',
                'topCustomers'
            )
        );


        return $pdf->download(
            'Victo-OMS-Report-' .
            str_replace(
                ' ',
                '-',
                $periodLabel
            ) .
            '.pdf'
        );
    }
}