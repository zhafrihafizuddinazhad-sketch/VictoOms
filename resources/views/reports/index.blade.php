@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}


    <div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="mb-1">Reports</h1>
        <p class="text-muted mb-0">
            Overview of order performance and business activity.
        </p>
    </div>

    <div class="d-flex" style="gap: 10px;">

        <a
            href="{{ route('owner.reports.export.pdf') }}"
            class="btn btn-danger"
        >
            <i class="fas fa-file-pdf mr-1"></i>
            Export PDF
        </a>

        <a
            href="{{ route('owner.reports.export.excel') }}"
            class="btn btn-success"
        >
            <i class="fas fa-file-excel mr-1"></i>
            Export Excel
        </a>

    </div>

</div>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="mb-1">
                <i class="fas fa-chart-bar mr-2"></i>
                Reports
            </h1>

            <p class="text-muted mb-0">
                Order and business performance overview
            </p>
        </div>

        <div>
            <span class="badge badge-primary p-2">
                <i class="fas fa-calendar-alt mr-1"></i>
                {{ now()->format('F Y') }}
            </span>
        </div>

    </div>

    <div class="card card-outline card-primary mb-4">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-1"></i>
            Report Filter
        </h3>
    </div>

    <div class="card-body">

        <form method="GET" action="{{ route('owner.reports') }}">

            <div class="row">

                {{-- Report Type --}}
                <div class="col-md-4">
                    <div class="form-group">

                        <label>Report Type</label>

                        <select
                            name="type"
                            id="reportType"
                            class="form-control">

                            <option value="monthly"
                                {{ ($type ?? 'monthly') === 'monthly' ? 'selected' : '' }}>
                                Monthly
                            </option>

                            <option value="annual"
                                {{ ($type ?? 'monthly') === 'annual' ? 'selected' : '' }}>
                                Annual
                            </option>

                        </select>

                    </div>
                </div>


                {{-- Month --}}
<div
    class="col-md-4"
    id="monthField"
    style="{{ ($type ?? 'monthly') === 'annual' ? 'display: none;' : '' }}"
>

    <div class="form-group">

        <label>Month</label>

        <select
            name="month"
            class="form-control"
        >

            @for($m = 1; $m <= 12; $m++)

                <option
                    value="{{ $m }}"
                    {{ ($month ?? now()->month) == $m ? 'selected' : '' }}
                >
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>

            @endfor

        </select>

    </div>

</div>

                {{-- Year --}}
                <div class="col-md-4">

                    <div class="form-group">

                        <label>Year</label>

                        <select
                            name="year"
                            class="form-control">

                            @for($y = now()->year; $y >= now()->year - 5; $y--)

                                <option
                                    value="{{ $y }}"
                                    {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>

                                    {{ $y }}

                                </option>

                            @endfor

                        </select>

                    </div>

                </div>

            </div>


            <div class="d-flex align-items-center">

    <button
        type="submit"
        class="btn btn-primary mr-2">

        <i class="fas fa-chart-bar mr-1"></i>

        Generate Report

    </button>


</div>

        </form>

    </div>

</div>

    {{-- ========================================================= --}}
    {{-- ORDER SUMMARY --}}
    {{-- ========================================================= --}}

    <div class="row">

        {{-- Total Orders --}}
        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>{{ $totalOrders }}</h3>

                    <p>Total Orders</p>

                </div>

                <div class="icon">

                    <i class="fas fa-shopping-cart"></i>

                </div>

            </div>

        </div>


        {{-- Completed --}}
        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>{{ $completedOrders }}</h3>

                    <p>Completed Orders</p>

                </div>

                <div class="icon">

                    <i class="fas fa-check-circle"></i>

                </div>

            </div>

        </div>


        {{-- In Progress --}}
        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>{{ $inProgressOrders }}</h3>

                    <p>In Progress</p>

                </div>

                <div class="icon">

                    <i class="fas fa-spinner"></i>

                </div>

            </div>

        </div>


        {{-- Overdue --}}
        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-danger">

                <div class="inner">

                    <h3>{{ $overdueOrders }}</h3>

                    <p>Overdue Orders</p>

                </div>

                <div class="icon">

                    <i class="fas fa-exclamation-triangle"></i>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SECONDARY ORDER STATUS --}}
    {{-- ========================================================= --}}

    <div class="row">

        {{-- Pending --}}
        <div class="col-lg-3 col-md-6">

            <div class="info-box">

                <span class="info-box-icon bg-warning">

                    <i class="fas fa-clock"></i>

                </span>

                <div class="info-box-content">

                    <span class="info-box-text">
                        Pending
                    </span>

                    <span class="info-box-number">
                        {{ $pendingOrders }}
                    </span>

                </div>

            </div>

        </div>


        {{-- Pending Approval --}}
        <div class="col-lg-3 col-md-6">

            <div class="info-box">

                <span class="info-box-icon bg-warning">

                    <i class="fas fa-search"></i>

                </span>

                <div class="info-box-content">

                    <span class="info-box-text">
                        Pending Approval
                    </span>

                    <span class="info-box-number">
                        {{ $pendingApprovalOrders }}
                    </span>

                </div>

            </div>

        </div>


        {{-- Printing --}}
        <div class="col-lg-3 col-md-6">

            <div class="info-box">

                <span class="info-box-icon bg-secondary">

                    <i class="fas fa-print"></i>

                </span>

                <div class="info-box-content">

                    <span class="info-box-text">
                        Printing
                    </span>

                    <span class="info-box-number">
                        {{ $printingOrders }}
                    </span>

                </div>

            </div>

        </div>


        {{-- Completion Rate --}}
        <div class="col-lg-3 col-md-6">

            <div class="info-box">

                <span class="info-box-icon bg-success">

                    <i class="fas fa-percentage"></i>

                </span>

                <div class="info-box-content">

                    <span class="info-box-text">
                        Completion Rate
                    </span>

                    <span class="info-box-number">
                        {{ $completionRate }}%
                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SALES --}}
    {{-- ========================================================= --}}

    <div class="row">

        {{-- Monthly Orders --}}
        <div class="col-lg-4">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-calendar mr-1"></i>

                        This Month

                    </h3>

                </div>

                <div class="card-body">

                    <div class="row text-center">

                        <div class="col-6">

                            <h3 class="text-primary">

                                {{ $totalOrders }}

                            </h3>

                            <p class="text-muted mb-0">

                                Orders

                            </p>

                        </div>


                        <div class="col-6">

                            <h3 class="text-success">

                                RM {{ number_format($periodSales, 2) }}

                            </h3>

                            <p class="text-muted mb-0">

                                Sales

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Total Sales --}}
        <div class="col-lg-4">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-money-bill-wave mr-1"></i>

                        Overall Sales

                    </h3>

                </div>

                <div class="card-body text-center">

                    <h2 class="text-success">

                        RM {{ number_format($totalSales, 2) }}

                    </h2>

                    <p class="text-muted mb-0">

                        Total sales recorded

                    </p>

                </div>

            </div>

        </div>


        {{-- Average Order Value --}}
        <div class="col-lg-4">

            @php

                $averageOrderValue = $totalOrders > 0
                    ? $totalSales / $totalOrders
                    : 0;

            @endphp

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-calculator mr-1"></i>

                        Average Order Value

                    </h3>

                </div>

                <div class="card-body text-center">

                    <h2 class="text-info">

                        RM {{ number_format($averageOrderValue, 2) }}

                    </h2>

                    <p class="text-muted mb-0">

                        Average value per order

                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- NEW VS REPEAT --}}
    {{-- ========================================================= --}}

    <div class="row">

        <div class="col-lg-6">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-redo mr-1"></i>

                        New vs Repeat Orders

                    </h3>

                </div>

                <div class="card-body">

                    <div class="row text-center">

                        <div class="col-6">

                            <h2 class="text-primary">

                                {{ $newOrders }}

                            </h2>

                            <p class="text-muted">

                                New Orders

                            </p>

                        </div>


                        <div class="col-6">

                            <h2 class="text-info">

                                {{ $repeatOrders }}

                            </h2>

                            <p class="text-muted">

                                Repeat Orders

                            </p>

                        </div>

                    </div>


                    @php

                        $totalOrderTypes =
                            $newOrders + $repeatOrders;

                        $repeatPercentage =
                            $totalOrderTypes > 0
                                ? round(
                                    ($repeatOrders /
                                    $totalOrderTypes) * 100,
                                    1
                                )
                                : 0;

                    @endphp


                    <div class="progress">

                        <div
                            class="progress-bar bg-info"
                            role="progressbar"
                            style="width: {{ $repeatPercentage }}%"
                        >

                            {{ $repeatPercentage }}%

                        </div>

                    </div>

                    <small class="text-muted">

                        Repeat order percentage

                    </small>

                </div>

            </div>

        </div>
{{-- ========================================================= --}}
{{-- REPORT TRENDS --}}
{{-- ========================================================= --}}

<div class="row">

    {{-- SALES TREND --}}

    <div class="col-md-6">

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">

                    <i class="fas fa-chart-line text-success mr-1"></i>

                    Sales Trend

                </h5>

                <small class="text-muted">

                    {{ $periodLabel }}

                </small>

            </div>

            <div class="card-body">

                <div style="height: 300px;">

                    <canvas id="salesTrendChart"></canvas>

                </div>

            </div>

        </div>

    </div>


    {{-- ORDER TREND --}}

    <div class="col-md-6">

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">

                    <i class="fas fa-chart-bar text-primary mr-1"></i>

                    Order Trend

                </h5>

                <small class="text-muted">

                    {{ $periodLabel }}

                </small>

            </div>

            <div class="card-body">

                <div style="height: 300px;">

                    <canvas id="orderTrendChart"></canvas>

                </div>

            </div>

        </div>

    </div>

</div>

        {{-- Order Health --}}
        <div class="col-lg-6">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-heartbeat mr-1"></i>

                        Order Health

                    </h3>

                </div>

                <div class="card-body">

                    <div class="progress-group">

                        Completed Orders

                        <span class="float-right">

                            <b>{{ $completedOrders }}</b>
                            / {{ $totalOrders }}

                        </span>

                        <div class="progress progress-sm">

                            <div
                                class="progress-bar bg-success"
                                style="width:
                                    {{ $totalOrders > 0
                                        ? ($completedOrders /
                                            $totalOrders) * 100
                                        : 0
                                    }}%"
                            ></div>

                        </div>

                    </div>


                    <div class="progress-group">

                        Orders Pending Approval

                        <span class="float-right">

                            <b>{{ $pendingApprovalOrders }}</b>

                        </span>

                        <div class="progress progress-sm">

                            <div
                                class="progress-bar bg-warning"
                                style="width:
                                    {{ $totalOrders > 0
                                        ? ($pendingApprovalOrders /
                                            $totalOrders) * 100
                                        : 0
                                    }}%"
                            ></div>

                        </div>

                    </div>


                    <div class="progress-group">

                        Overdue Orders

                        <span class="float-right">

                            <b>{{ $overdueOrders }}</b>

                        </span>

                        <div class="progress progress-sm">

                            <div
                                class="progress-bar bg-danger"
                                style="width:
                                    {{ $totalOrders > 0
                                        ? ($overdueOrders /
                                            $totalOrders) * 100
                                        : 0
                                    }}%"
                            ></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- DESIGNER PERFORMANCE --}}
    {{-- ========================================================= --}}

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-users mr-1"></i>

                Designer Performance

            </h3>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover mb-0">

                    <thead>

                        <tr>

                            <th width="60">
                                #
                            </th>

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

                        @forelse($designerPerformance as $index => $designer)

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
                                    {{ $index + 1 }}
                                </td>

                                <td>

                                    <strong>
                                        {{ $designer->name }}
                                    </strong>

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-primary">

                                        {{ $assigned }}

                                    </span>

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-success">

                                        {{ $completed }}

                                    </span>

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-warning">

                                        {{ $designer->pending_approval_orders_count }}

                                    </span>

                                </td>

                                <td class="text-center">

                                    <strong>

                                        {{ $designerRate }}%

                                    </strong>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >

                                    No designer data available.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- TOP CUSTOMERS --}}
    {{-- ========================================================= --}}

    <div class="card mt-4">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-star mr-1"></i>

                Top Customers

            </h3>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover mb-0">

                    <thead>

                        <tr>

                            <th width="60">
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

                                    <strong>

                                        {{ $customer->customer->customer_name ?? 'Unknown Customer' }}

                                    </strong>

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-info">

                                        {{ $customer->orders_count }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="3"
                                    class="text-center text-muted py-4"
                                >

                                    No customer data available.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- REPORT NOTE --}}
    {{-- ========================================================= --}}

    <div class="alert alert-info mt-4">

        <i class="fas fa-info-circle mr-1"></i>

        <strong>Report Information:</strong>

        This report provides an overview of order performance,
        sales, repeat orders, designer performance and customer
        activity.

    </div>

</div>

{{-- ========================================================= --}}
{{-- CHART.JS --}}
{{-- ========================================================= --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function ()
    {

        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        */

        const labels =
            @json($chartLabels);

        const salesData =
            @json($salesChartData);

        const ordersData =
            @json($ordersChartData);


        /*
        |--------------------------------------------------------------------------
        | Sales Trend Chart
        |--------------------------------------------------------------------------
        */

        const salesCanvas =
            document.getElementById(
                'salesTrendChart'
            );


        if (salesCanvas) {

            new Chart(
                salesCanvas,
                {

                    type: 'line',

                    data: {

                        labels: labels,

                        datasets: [

                            {

                                label: 'Sales (RM)',

                                data: salesData,

                                borderWidth: 2,

                                tension: 0.3,

                                fill: false

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        plugins: {

                            legend: {

                                display: true

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback: function(value)
                                    {

                                        return 'RM ' +
                                            Number(value)
                                                .toLocaleString();

                                    }

                                }

                            }

                        }

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Order Trend Chart
        |--------------------------------------------------------------------------
        */

        const orderCanvas =
            document.getElementById(
                'orderTrendChart'
            );


        if (orderCanvas) {

            new Chart(
                orderCanvas,
                {

                    type: 'bar',

                    data: {

                        labels: labels,

                        datasets: [

                            {

                                label: 'Orders',

                                data: ordersData,

                                borderWidth: 1

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        plugins: {

                            legend: {

                                display: true

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    precision: 0

                                }

                            }

                        }

                    }

                }
            );

        }

    }
);

</script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const reportType = document.getElementById('reportType');
    const monthField = document.getElementById('monthField');

    function toggleMonthField() {

        if (reportType.value === 'annual') {

            monthField.style.display = 'none';

        } else {

            monthField.style.display = 'block';

        }

    }

    reportType.addEventListener('change', toggleMonthField);

    toggleMonthField();

});

</script>
@endsection