<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        Victo OMS Report - {{ $periodLabel }}
    </title>

    <style>

        @page {
            margin: 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #eeeeee;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #cccccc;
            padding: 8px;
        }

        .summary td {
            width: 25%;
            text-align: center;
        }

        .label {
            font-size: 9px;
            color: #666666;
        }

        .value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 9px;
            color: #777777;
        }

    </style>

</head>

<body>

    {{-- ================================================= --}}
    {{-- HEADER --}}
    {{-- ================================================= --}}

    <h1>
        Victo OMS
    </h1>

    <div class="subtitle">

        Order Management Report

        <br>

        <strong>
            {{ $periodLabel }}
        </strong>

    </div>


    {{-- ================================================= --}}
    {{-- ORDER SUMMARY --}}
    {{-- ================================================= --}}

    <h2>
        Order Summary
    </h2>

    <table class="summary">

        <tr>

            <td>

                <div class="label">
                    TOTAL ORDERS
                </div>

                <div class="value">
                    {{ number_format($totalOrders) }}
                </div>

            </td>


            <td>

                <div class="label">
                    COMPLETED
                </div>

                <div class="value">
                    {{ number_format($completedOrders) }}
                </div>

            </td>


            <td>

                <div class="label">
                    IN PROGRESS
                </div>

                <div class="value">
                    {{ number_format($inProgressOrders) }}
                </div>

            </td>


            <td>

                <div class="label">
                    OVERDUE
                </div>

                <div class="value">
                    {{ number_format($overdueOrders) }}
                </div>

            </td>

        </tr>

    </table>


    {{-- ================================================= --}}
    {{-- ORDER STATUS --}}
    {{-- ================================================= --}}

    <h2>
        Order Status Breakdown
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    Status
                </th>

                <th class="text-center">
                    Orders
                </th>

            </tr>

        </thead>

        <tbody>

            <tr>
                <td>Pending</td>
                <td class="text-center">
                    {{ $pendingOrders }}
                </td>
            </tr>

            <tr>
                <td>In Progress</td>
                <td class="text-center">
                    {{ $inProgressOrders }}
                </td>
            </tr>

            <tr>
                <td>Pending Approval</td>
                <td class="text-center">
                    {{ $pendingApprovalOrders }}
                </td>
            </tr>

            <tr>
                <td>Printing</td>
                <td class="text-center">
                    {{ $printingOrders }}
                </td>
            </tr>

            <tr>
                <td>Completed</td>
                <td class="text-center">
                    {{ $completedOrders }}
                </td>
            </tr>

        </tbody>

    </table>


    {{-- ================================================= --}}
    {{-- SALES --}}
    {{-- ================================================= --}}

    <h2>
        Sales Performance
    </h2>

    <table>

        <tr>

            <td>
                Period Sales
            </td>

            <td class="text-right">

                RM {{ number_format($periodSales, 2) }}

            </td>

        </tr>

        <tr>

            <td>
                Average Order Value
            </td>

            <td class="text-right">

                RM
                {{ number_format(
                    $totalOrders > 0
                        ? $periodSales / $totalOrders
                        : 0,
                    2
                ) }}

            </td>

        </tr>

        <tr>

            <td>
                Completion Rate
            </td>

            <td class="text-right">

                {{ $completionRate }}%

            </td>

        </tr>

    </table>


    {{-- ================================================= --}}
    {{-- NEW VS REPEAT --}}
    {{-- ================================================= --}}

    <h2>
        New vs Repeat Orders
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    Order Type
                </th>

                <th class="text-center">
                    Orders
                </th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>
                    New Orders
                </td>

                <td class="text-center">
                    {{ $newOrders }}
                </td>

            </tr>

            <tr>

                <td>
                    Repeat Orders
                </td>

                <td class="text-center">
                    {{ $repeatOrders }}
                </td>

            </tr>

        </tbody>

    </table>


    {{-- ================================================= --}}
    {{-- DESIGNER PERFORMANCE --}}
    {{-- ================================================= --}}

    <h2>
        Designer Performance
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    Designer
                </th>

                <th class="text-center">
                    Assigned
                </th>

                <th class="text-center">
                    Completed
                </th>

                <th class="text-center">
                    Pending Approval
                </th>

                <th class="text-center">
                    Completion Rate
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($designerPerformance as $designer)

                @php

                    $assigned =
                        $designer->assigned_orders_count;

                    $completed =
                        $designer->completed_orders_count;

                    $designerRate =
                        $assigned > 0
                            ? round(
                                ($completed /
                                $assigned) * 100,
                                1
                            )
                            : 0;

                @endphp

                <tr>

                    <td>
                        {{ $designer->name }}
                    </td>

                    <td class="text-center">
                        {{ $assigned }}
                    </td>

                    <td class="text-center">
                        {{ $completed }}
                    </td>

                    <td class="text-center">
                        {{ $designer->pending_approval_orders_count }}
                    </td>

                    <td class="text-center">
                        {{ $designerRate }}%
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="5"
                        class="text-center">

                        No designer data available.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- ================================================= --}}
    {{-- TOP CUSTOMERS --}}
    {{-- ================================================= --}}

    <h2>
        Top Customers
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    #
                </th>

                <th>
                    Customer
                </th>

                <th class="text-center">
                    Total Orders
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($topCustomers as $index => $customer)

                <tr>

                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>

                        {{ $customer->customer->customer_name
                            ?? 'Unknown Customer' }}

                    </td>

                    <td class="text-center">

                        {{ $customer->orders_count }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="3"
                        class="text-center">

                        No customer data available.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- ================================================= --}}
    {{-- FOOTER --}}
    {{-- ================================================= --}}

    <div class="footer">

        Generated by Victo OMS

        <br>

        {{ now()->format('d M Y, h:i A') }}

    </div>

</body>

</html>