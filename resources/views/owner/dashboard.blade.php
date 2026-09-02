@extends('layouts.admin')

@section('content')

<div class="content-header">
    <h1 class="mb-4">Dashboard</h1>
</div>

{{-- KPI --}}
<div class="row">

    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalCustomers }}</h3>
                <p>Customers</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalOrders }}</h3>
                <p>Total Orders</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>RM {{ number_format($totalRevenue,2) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>

</div>

<div class="alert alert-info mb-4">

    <i class="fas fa-info-circle"></i>

    <strong>Today's Summary</strong>

    <hr>

    There are

    <strong>{{ $readyHQ }}</strong>

    order(s) waiting at HQ.

    <br>

    <strong>{{ $photoSession }}</strong>

    order(s) currently in Photo Session.

    <br>

    <strong>{{ $photoCompleted }}</strong>

    order(s) waiting for Delivery / Pickup.

</div>

{{-- ================================================= --}}
{{-- ATTENTION REQUIRED --}}
{{-- ================================================= --}}

<div class="card mb-4">

    <div class="card-header bg-danger text-white">

        <h3 class="card-title">

            <i class="fas fa-exclamation-triangle mr-1"></i>

            Attention Required

        </h3>

    </div>


    <div class="card-body p-0">

        @forelse($attentionOrders as $order)

            @php

                if ($order->status === 'Pending Approval') {

                    $attentionType = 'Pending Review';

                    $attentionClass = 'danger';

                    $attentionIcon = 'fa-search';

                } elseif (
                    $order->due_date &&
                    \Carbon\Carbon::parse($order->due_date)->isPast()
                    &&
                    $order->status !== 'Completed'
                ) {

                    $attentionType = 'Overdue';

                    $attentionClass = 'danger';

                    $attentionIcon = 'fa-clock';

                } else {

                    $attentionType = 'Due Soon';

                    $attentionClass = 'warning';

                    $attentionIcon = 'fa-calendar-alt';

                }

            @endphp


            <div class="p-3 border-bottom">


                <div class="row align-items-center">


                    {{-- ORDER INFO --}}

                    <div class="col-md-7">

                        <div class="d-flex align-items-center">

                            <i class="fas {{ $attentionIcon }}
                               text-{{ $attentionClass }}
                               mr-2">
                            </i>


                            <div>

                                <strong>

                                    {{ $order->order_no }}

                                </strong>


                                <span class="badge
                                    bg-{{ $attentionClass }}
                                    ml-2">

                                    {{ $attentionType }}

                                </span>

                            </div>

                        </div>


                        <div class="text-muted mt-1">

                            {{ $order->customer->customer_name }}

                        </div>


                        <small class="text-muted">

                            Due:

                            {{ \Carbon\Carbon::parse($order->due_date)->format('d M Y') }}

                        </small>

                    </div>


                    {{-- STATUS --}}

                    <div class="col-md-2 mt-2 mt-md-0">

                        <span class="badge
                            {{ $order->getStatusBadgeClass() }}">

                            {{ $order->status }}

                        </span>

                    </div>


                    {{-- ACTION --}}

                    <div class="col-md-3 text-md-right mt-2 mt-md-0">

                        <a
                            href="{{ route('orders.show', $order) }}"
                            class="btn btn-sm btn-outline-primary">

                            <i class="fas fa-eye mr-1"></i>

                            Review Order

                        </a>

                    </div>

                </div>

            </div>


        @empty

            <div class="text-center py-4">

                <i class="fas fa-check-circle
                          text-success
                          fa-2x
                          mb-2">
                </i>

                <p class="mb-0 text-muted">

                    No urgent orders require your attention.

                </p>

            </div>

        @endforelse

    </div>

</div>

{{-- Workflow Status + Latest Orders --}}
<div class="row">

    <div class="col-md-4">

        {{-- Production --}}
        <div class="card mb-3">

            <div class="card-header bg-primary text-white">

                <strong>
                    <i class="fas fa-industry"></i>
                    Production
                </strong>

            </div>

            <div class="card-body p-0">

                <table class="table table-striped mb-0">

                   <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Pending']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>
                Pending
            </span>

            <span class="badge bg-warning text-dark">
                {{ $pending }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Assigned']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>
                Assigned
            </span>

            <span class="badge bg-info">
                {{ $assigned }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'In Progress']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>
                In Progress
            </span>

            <span class="badge bg-primary">
                {{ $inProgress }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Pending Approval']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>
                Pending Approval
            </span>

            <span class="badge bg-secondary">
                {{ $pendingApproval }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Printing']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>
                Printing
            </span>

            <span class="badge bg-dark">
                {{ $printing }}
            </span>

        </a>

    </td>
</tr>

                </table>

            </div>

        </div>

        {{-- HQ & Cameraman --}}
        <div class="card mb-3">

            <div class="card-header bg-info text-white">

                <strong>
                    <i class="fas fa-camera"></i>
                    HQ & Photo Session
                </strong>

            </div>

            <div class="card-body p-0">

                <table class="table table-striped mb-0">

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Ready at HQ']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>Ready at HQ</span>

            <span class="badge bg-dark">
                {{ $readyHQ }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Photo Session']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>Photo Session</span>

            <span class="badge bg-warning text-dark">
                {{ $photoSession }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Photo Completed']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>Photo Completed</span>

            <span class="badge bg-success">
                {{ $photoCompleted }}
            </span>

        </a>

    </td>
</tr>

                </table>

            </div>

        </div>

        {{-- Delivery --}}
        <div class="card">

            <div class="card-header bg-success text-white">

                <strong>
                    <i class="fas fa-truck"></i>
                    Delivery
                </strong>

            </div>

            <div class="card-body p-0">

                <table class="table table-striped mb-0">

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Out for Delivery']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>Out for Delivery</span>

            <span class="badge bg-primary">
                {{ $outForDelivery }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Waiting for Pickup']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>Waiting for Pickup</span>

            <span class="badge bg-warning text-dark">
                {{ $waitingPickup }}
            </span>

        </a>

    </td>
</tr>

                    <tr>
    <td colspan="2" class="p-0">

        <a
            href="{{ route('orders.index', ['status' => 'Completed']) }}"
            class="dashboard-status-link d-flex justify-content-between align-items-center p-2 text-decoration-none">

            <span>Completed</span>

            <span class="badge bg-success">
                {{ $completed }}
            </span>

        </a>

    </td>
</tr>

                </table>

            </div>

        </div>

    </div>

    {{-- Latest Orders --}}
    <div class="col-md-8">

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-shopping-bag"></i>

                    Latest Orders

                </h3>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover mb-0">

                    <thead>

                        <tr>

                            <th>Order No</th>

                            <th>Customer</th>

                            <th>Status</th>

                            <th>Due Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($latestOrders as $order)

                        <tr>

                            <td>{{ $order->order_no }}</td>

                            <td>{{ $order->customer->customer_name }}</td>

                            <td>

                                <span class="badge {{ $order->getStatusBadgeClass() }}">
                                    {{ $order->status }}
                                </span>

                            </td>

                            <td>{{ \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') }}</td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="text-center">

                                No orders found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- Quick Actions --}}
<div class="card mb-4">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-bolt"></i>

            Quick Actions

        </h3>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 mb-2">

                <a href="{{ route('orders.create') }}"
                   class="btn btn-primary w-100">

                    <i class="fas fa-plus-circle"></i>

                    <br>

                    Create Order

                </a>

            </div>

            <div class="col-md-3 mb-2">

                <a href="{{ route('customers.index') }}"
                   class="btn btn-info w-100">

                    <i class="fas fa-users"></i>

                    <br>

                    Customers

                </a>

            </div>

            <div class="col-md-3 mb-2">

                <a href="{{ route('designer.monitoring') }}"
                   class="btn btn-warning w-100">

                    <i class="fas fa-user-pen"></i>

                    <br>

                    Designer

                </a>

            </div>

            <div class="col-md-3 mb-2">

                <a href="{{ route('orders.index') }}"
                   class="btn btn-success w-100">

                    <i class="fas fa-box"></i>

                    <br>

                    Orders

                </a>

            </div>

        </div>

    </div>

</div>
{{-- Recent Activities --}}
<div class="card">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-history"></i>

            Recent Activities

        </h3>

    </div>

    <div class="card-body">

        @forelse($activities as $activity)

            <div class="d-flex justify-content-between">

                <div>

                    <strong>

                        {{ $activity->order->order_no ?? '-' }}

                    </strong>

                    <br>

                    <small class="text-muted">

                        {{ $activity->order->customer->customer_name ?? '-' }}

                    </small>

                    <br>

                    @php
    $badge = match($activity->action) {

    'Task Started' => 'info',

    'Submitted for Approval' => 'warning',

    'Design Approved' => 'success',

    'Revision Requested' => 'danger',

    'Ready at HQ' => 'dark',

    'Photo Session Started' => 'primary',

    'Photo Session Completed' => 'success',

    'Out for Delivery' => 'primary',

    'Waiting for Pickup' => 'warning',

    'Delivered' => 'success',

    'Picked Up' => 'success',

    default => 'secondary',

};
@endphp
                    
                    <span class="badge bg-{{ $badge }}">

                    

                        {{ $activity->action }}

                    </span>

                </div>

                <div class="text-end text-muted">

                    {{ $activity->created_at->diffForHumans() }}

                </div>

            </div>

            @if(!$loop->last)

                <hr>

            @endif

        @empty

            <p class="text-center text-muted">

                No recent activity.

            </p>

        @endforelse

    </div>

</div>

<style>

.dashboard-status-link {

    color: inherit;

    transition: all 0.2s ease;

}


.dashboard-status-link:hover {

    background-color: #f4f6f9;

    color: inherit;

}


.dashboard-status-link:hover span:first-child {

    font-weight: 600;

}


</style>
@endsection