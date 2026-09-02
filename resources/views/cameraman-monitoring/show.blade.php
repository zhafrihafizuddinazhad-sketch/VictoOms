@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h1 class="m-0">

                    <i class="fas fa-camera mr-2"></i>

                    Cameraman Details

                </h1>

                <p class="text-muted mb-0">

                    View cameraman information and current workload.

                </p>

            </div>


            @php

                $cameramanIndexRoute = auth()->user()->hasRole('admin')
                    ? 'admin.cameramen.index'
                    : 'owner.cameramen.index';

            @endphp


            <a
                href="{{ route($cameramanIndexRoute) }}"
                class="btn btn-secondary"
            >

                <i class="fas fa-arrow-left mr-1"></i>

                Back

            </a>

        </div>

    </div>

</div>


<section class="content">

    <div class="container-fluid">


        {{-- ================================================= --}}
        {{-- CAMERAMAN INFORMATION --}}
        {{-- ================================================= --}}

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-user mr-2"></i>

                    Cameraman Information

                </h3>

            </div>


            <div class="card-body">

                <div class="row">

                    <div class="col-md-6">

                        <strong>

                            Name

                        </strong>

                        <p class="text-muted">

                            {{ $cameraman->name }}

                        </p>

                    </div>


                    <div class="col-md-6">

                        <strong>

                            Email

                        </strong>

                        <p class="text-muted">

                            {{ $cameraman->email }}

                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- WORKLOAD SUMMARY --}}
        {{-- ================================================= --}}

        <div class="row">

            <div class="col-md-4">

                <div class="small-box bg-info">

                    <div class="inner">

                        <h3>

                            {{ $activeTasks }}

                        </h3>

                        <p>

                            Active Tasks

                        </p>

                    </div>


                    <div class="icon">

                        <i class="fas fa-camera"></i>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="small-box bg-success">

                    <div class="inner">

                        <h3>

                            {{ $completedTasks }}

                        </h3>

                        <p>

                            Completed Tasks

                        </p>

                    </div>


                    <div class="icon">

                        <i class="fas fa-check-circle"></i>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="small-box bg-warning">

                    <div class="inner">

                        <h3>

                            {{ $orders->count() }}

                        </h3>

                        <p>

                            Total Assigned Orders

                        </p>

                    </div>


                    <div class="icon">

                        <i class="fas fa-clipboard-list"></i>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- ASSIGNED ORDERS --}}
        {{-- ================================================= --}}

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-list mr-2"></i>

                    Assigned Orders

                </h3>

            </div>


            <div class="card-body p-0">

                @if($orders->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-hover mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        #
                                    </th>

                                    <th>
                                        Order
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Due Date
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($orders as $order)

                                    <tr>

                                        <td>

                                            {{ $loop->iteration }}

                                        </td>


                                        <td>

                                            <a
                                                href="{{ route(
                                                    'orders.show',
                                                    $order
                                                ) }}"
                                            >

                                                {{ $order->order_no }}

                                            </a>

                                        </td>


                                        <td>

                                            {{ $order->customer->customer_name ?? '-' }}

                                        </td>


                                        <td>

                                            {{ $order->due_date
                                                ? \Carbon\Carbon::parse($order->due_date)->format('d M Y')
                                                : '-'
                                            }}

                                        </td>


                                        <td>

                                            @php

                                                $badge = match($order->status) {

                                                    'Completed' => 'success',

                                                    'Photo Completed' => 'success',

                                                    'Photo Session' => 'primary',

                                                    'Cancelled' => 'danger',

                                                    default => 'warning',

                                                };

                                            @endphp


                                            <span class="badge badge-{{ $badge }}">

                                                {{ $order->status }}

                                            </span>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="text-center p-4">

                        <i class="fas fa-camera fa-2x text-muted mb-3"></i>

                        <p class="text-muted mb-0">

                            No orders assigned to this cameraman.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</section>

@endsection