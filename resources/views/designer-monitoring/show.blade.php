@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>
        <h2 class="mb-0">{{ $designer->name }}</h2>
        <small class="text-muted">Designer Profile</small>
    </div>

    <a href="{{ route('designer.monitoring') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>

</div>

<div class="row mb-4">

    <div class="col-md-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $totalOrders }}</h3>
                <p>Total Orders</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $activeOrders }}</h3>
                <p>Active Orders</p>
            </div>
            <div class="icon">
                <i class="fas fa-palette"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pendingApproval }}</h3>
                <p>Pending Approval</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $completedOrders }}</h3>
                <p>Completed</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

</div>

<div class="card">

    <div class="col-md-8">
        <div class="card">

            <div class="card-header">
                <strong>Current Assigned Orders</strong>
            </div>

            <div class="card-body">

                @forelse($orders as $order)

                    <div class="card shadow-sm mb-3">

                        <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h5 class="mb-1">
                                    {{ $order->order_no }}
                                </h5>

                                <p class="mb-1">
                                    <strong>Customer:</strong>
                                    {{ $order->customer->customer_name }}
                                </p>

                                @php

                                $due = \Carbon\Carbon::parse($order->due_date);

                                @endphp

                                <p class="mb-1">

                                    <strong>Due Date :</strong>

                                        @if($due->isPast() && $order->status != 'Completed')

                                        <span class="text-danger fw-bold">

                                            {{ $due->format('d/m/Y') }}

                                                (Overdue)

                                        </span>

                                        @elseif($due->diffInDays(now()) <= 3 && $order->status != 'Completed')

                                        <span class="text-warning fw-bold">

                                            {{ $due->format('d/m/Y') }}

                                                (Due Soon)

                                        </span>

                                        @else

                                            {{ $due->format('d/m/Y') }}

                                        @endif

                                </p>
                            </div>
                            </div>

                            <div class="text-end">

                                @php

                                $badge = match($order->status){

                                'Pending' => 'secondary',

                                'Assigned' => 'primary',

                                'In Progress' => 'warning',

                                'Pending Approval' => 'info',

                                'Printing' => 'dark',

                                'Ready at HQ' => 'success',

                                'Out for Delivery' => 'primary',

                                'Waiting for Pickup' => 'warning',

                                'Completed' => 'success',

                                default => 'secondary'

                                };

                            @endphp

                        <span class="badge bg-{{ $badge }}">
                            {{ $order->status }}
                        </span>

                                <br><br>

                                <a href="{{ route('orders.show', $order) }}"
                                   class="btn btn-primary btn-sm">

                                    <i class="fas fa-eye"></i>
                                    Open Order

                                </a>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="text-center text-muted">

                        No assigned orders.

                    </div>

                @endforelse

            </div>

        </div>
    </div>

</div>

@endsection